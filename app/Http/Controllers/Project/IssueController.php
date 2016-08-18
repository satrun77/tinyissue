<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Controllers\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tinyissue\Form\Comment as CommentForm;
use Tinyissue\Form\Issue as IssueForm;
use Tinyissue\Http\Controllers\Controller;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Project\Issue\Attachment;
use Tinyissue\Model\Project\Issue\Comment;
use Tinyissue\Model\Tag;

/**
 * IssueController is the controller class for managing request related to projects issues.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class IssueController extends Controller
{
    /**
     * Project issue index page (List project issues).
     *
     * @param Project     $project
     * @param Issue       $issue
     * @param CommentForm $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(Project $project, Issue $issue, CommentForm $form)
    {
        $canEdit           = $this->allows('update', $issue);
        $usersCanFixIssues = $canEdit && $issue->status == Issue::STATUS_OPEN ? $project->getUsersCanFixIssue() : [];

        // Projects should be limited to issue-modify
        $projects = null;
        if ($canEdit) {
            $projects = $this->getLoggedUser()->getProjects();
        }

        return view('project.issue.index', [
            'issue'               => $issue,
            'usersCanFixIssues'   => $usersCanFixIssues,
            'project'             => $project,
            'closed_issues_count' => $project->countClosedIssues($this->getLoggedUser()),
            'open_issues_count'   => $project->countOpenIssues($this->getLoggedUser()),
            'commentForm'         => $form,
            'sidebar'             => 'project',
            'projects'            => $projects,
        ]);
    }

    /**
     * Ajax: Assign new user to an issue.
     *
     * @param Issue   $issue
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAssign(Issue $issue, Request $request)
    {
        $response = [
            'status' => $issue->updater($this->getLoggedUser())->reassign((int) $request->input('user_id'), $this->getLoggedUser()),
        ];

        return response()->json($response);
    }

    /**
     * Ajax: save comment.
     *
     * @param Comment $comment
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postEditComment(Comment $comment, Request $request)
    {
        $body = '';
        if ($request->has('body')) {
            $comment
                ->updater($this->getLoggedUser())
                ->updateBody((string) $request->input('body'));
            $body = \Html::format($comment->comment);
        }

        return response()->json(['text' => $body]);
    }

    /**
     * To add new comment to an issue.
     *
     * @param Project             $project
     * @param Issue               $issue
     * @param Comment             $comment
     * @param FormRequest\Comment $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAddComment(Project $project, Issue $issue, Comment $comment, FormRequest\Comment $request)
    {
        $comment->setRelations([
            'project' => $project,
            'issue'   => $issue,
            'user'    => $this->getLoggedUser(),
        ]);
        $comment->updater($this->getLoggedUser())->create($request->all());

        return redirect($issue->to() . '#comment' . $comment->id)->with('notice', trans('tinyissue.your_comment_added'));
    }

    /**
     * Ajax: to delete a comment.
     *
     * @param Comment $comment
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDeleteComment(Comment $comment)
    {
        $comment->updater($this->getLoggedUser())->delete();

        return response()->json(['status' => true]);
    }

    /**
     * New issue form.
     *
     * @param Project   $project
     * @param IssueForm $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getNew(Project $project, IssueForm $form)
    {
        return view('project.issue.new', [
            'project' => $project,
            'form'    => $form,
            'sidebar' => 'project',
        ]);
    }

    /**
     * To create a new issue.
     *
     * @param Project           $project
     * @param Issue             $issue
     * @param FormRequest\Issue $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNew(Project $project, Issue $issue, FormRequest\Issue $request)
    {
        $issue->setRelations([
            'project' => $project,
            'user'    => $this->getLoggedUser(),
        ]);
        $issue->updater($this->getLoggedUser())->create($request->all());

        return redirect($issue->to())->with('notice', trans('tinyissue.issue_has_been_created'));
    }

    /**
     * Edit an existing issue form.
     *
     * @param Project   $project
     * @param Issue     $issue
     * @param IssueForm $form
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getEdit(Project $project, Issue $issue, IssueForm $form)
    {
        // Cannot edit closed issue
        if ($issue->status == Issue::STATUS_CLOSED) {
            return redirect($issue->to())
                ->with('notice', trans('tinyissue.cant_edit_closed_issue'));
        }

        return view('project.issue.edit', [
            'issue'   => $issue,
            'project' => $project,
            'form'    => $form,
            'sidebar' => 'project',
        ]);
    }

    /**
     * To update an existing issue details.
     *
     * @param Project           $project
     * @param Issue             $issue
     * @param FormRequest\Issue $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(Project $project, Issue $issue, FormRequest\Issue $request)
    {
        // Delete the issue
        if ($request->has('delete-issue')) {
            $issue->updater($this->getLoggedUser())->delete();

            return redirect($project->to())->with('notice', trans('tinyissue.issue_has_been_deleted'));
        }

        $issue->setRelations([
            'project'   => $project,
            'updatedBy' => $this->getLoggedUser(),
        ]);
        $issue->updater($this->getLoggedUser())->update($request->all());

        return redirect($issue->to())
            ->with('notice', trans('tinyissue.issue_has_been_updated'));
    }

    /**
     * To close or reopen an issue.
     *
     * @param Project $project
     * @param Issue   $issue
     * @param int     $status
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getClose(Project $project, Issue $issue, $status = 0)
    {
        if ($status == 0) {
            $message = trans('tinyissue.issue_has_been_closed');
        } else {
            $message = trans('tinyissue.issue_has_been_reopened');
        }

        $issue->setRelation('project', $project);
        $issue->updater($this->getLoggedUser())->changeStatus($status, $this->getLoggedUser());

        return redirect($issue->to())
            ->with('notice', $message);
    }

    /**
     * To upload an attachment file.
     *
     * @param Project    $project
     * @param Attachment $attachment
     * @param Request    $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postUploadAttachment(Project $project, Attachment $attachment, Request $request)
    {
        try {
            $attachment->updater($this->getLoggedUser())->upload($request->all(), $project, $this->getLoggedUser());

            $response = [
                'upload' => [
                    [
                        'name'   => $attachment->filename,
                        'size'   => $attachment->filesize,
                        'fileId' => $attachment->id,
                    ],
                ],
            ];
        } catch (\Exception $exception) {
            $file = $request->file('upload');

            $response = [
                'status' => false,
                'name'   => $file->getClientOriginalName(),
                'error'  => $exception->getMessage(),
                'trace'  => $exception->getTraceAsString(),
            ];
        }

        return response()->json($response);
    }

    /**
     * Delete attachment.
     *
     * @param Project    $project
     * @param Issue      $issue
     * @param Attachment $attachment
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getDeleteAttachment(Project $project, Issue $issue, Attachment $attachment)
    {
        $issue->setRelation('project', $project);
        $attachment->setRelation('issue', $issue);
        $attachment->updater($this->getLoggedUser())->delete();

        return redirect($issue->to())->with('notice', trans('tinyissue.attachment_has_been_deleted'));
    }

    /**
     * Display an attachment file such as image.
     *
     * @param Project    $project
     * @param Issue      $issue
     * @param Attachment $attachment
     * @param Request    $request
     *
     * @return Response
     */
    public function getDisplayAttachment(Project $project, Issue $issue, Attachment $attachment, Request $request)
    {
        $issue->setRelation('project', $project);
        $attachment->setRelation('issue', $issue);

        return $attachment->getDisplayResponse($request);
    }

    /**
     * Download an attachment file.
     *
     * @param Project    $project
     * @param Issue      $issue
     * @param Attachment $attachment
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getDownloadAttachment(Project $project, Issue $issue, Attachment $attachment)
    {
        $issue->setRelation('project', $project);
        $attachment->setRelation('issue', $issue);

        $path = config('filesystems.disks.local.root') . '/' . config('tinyissue.uploads_dir') . '/' . $issue->project_id . '/' . $attachment->upload_token . '/' . $attachment->filename;

        return response()->download($path, $attachment->filename);
    }

    /**
     * Ajax: move an issue to another project.
     *
     * @param Issue   $issue
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postChangeProject(Issue $issue, Request $request)
    {
        $issue->updater()->changeProject((int) $request->input('project_id'));

        return response()->json(['status' => true, 'url' => $issue->to()]);
    }

    /**
     * Ajax: change status of an issue.
     *
     * @param Issue   $issue
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postChangeKanbanTag(Issue $issue, Request $request)
    {
        $newTag = Tag::find((int) $request->input('newtag'));
        $oldTag = Tag::find((int) $request->input('oldtag'));

        $issue->updater($this->getLoggedUser())->changeKanbanTag($newTag, $oldTag);

        return response()->json(['status' => true, 'issue' => $issue->id]);
    }

    /**
     * Ajax: returns comments for an issue.
     *
     * @param Project $project
     * @param Issue   $issue
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\Vie
     */
    public function getIssueComments(Project $project, Issue $issue)
    {
        $issue->setRelation('project', $project);
        $activities = $issue->getCommentActivities();

        return view('project.issue.partials.activities', [
            'no_data'     => trans('tinyissue.no_comments'),
            'activities'  => $activities,
            'project'     => $project,
            'issue'       => $issue,
        ]);
    }

    /**
     * Ajax: returns activities for an issue excluding comments.
     *
     * @param Project $project
     * @param Issue   $issue
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\Vie
     */
    public function getIssueActivity(Project $project, Issue $issue)
    {
        $issue->setRelation('project', $project);
        $activities = $issue->getGeneralActivities();

        return view('project.issue.partials.activities', [
            'no_data'     => trans('tinyissue.no_activities'),
            'activities'  => $activities,
            'project'     => $project,
            'issue'       => $issue,
        ]);
    }
}
