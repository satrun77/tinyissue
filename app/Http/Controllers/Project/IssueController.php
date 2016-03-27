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
use Tinyissue\Model\User\Activity as UserActivity;

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
     * @return \Illuminate\View\View
     */
    public function getIndex(Project $project, Issue $issue, CommentForm $form)
    {
        $issue->attachments->each(function (Attachment $attachment) use ($issue) {
            $attachment->setRelation('issue', $issue);
        });
        $activities = $issue->activities()->with('activity', 'user', 'comment', 'assignTo',
            'comment.attachments')->get();
        $activities->each(function (UserActivity $activity) use ($issue) {
            $activity->setRelation('issue', $issue);
        });

        // Projects should be limited to issue-modify
        $projects = null;
        if (!$this->auth->guest() && $this->auth->user()->permission('issue-modify')) {
            $projects = $this->auth->user()->projects()->get();
        }

        return view('project.issue.index', [
            'issue'       => $issue,
            'project'     => $project,
            'commentForm' => $form,
            'activities'  => $activities,
            'sidebar'     => 'project',
            'projects'    => $projects,
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
        $response = ['status' => false];
        if ($issue->reassign((int) $request->input('user_id'), $this->auth->user()->id)) {
            $response['status'] = true;
        }

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
            $comment->fill(['comment' => $request->input('body')])->save();
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
    public function getAddComment(Project $project, Issue $issue, Comment $comment, FormRequest\Comment $request)
    {
        $comment->setRelation('project', $project);
        $comment->setRelation('issue', $issue);
        $comment->setRelation('user', $this->auth->user());
        $comment->createComment($request->all());

        return redirect($issue->to() . '#comment' . $comment->id)
            ->with('notice', trans('tinyissue.your_comment_added'));
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
        $comment->deleteComment();

        return response()->json(['status' => true]);
    }

    /**
     * New issue form.
     *
     * @param Project   $project
     * @param IssueForm $form
     *
     * @return \Illuminate\View\View
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
        $issue->setRelation('project', $project);
        $issue->setRelation('user', $this->auth->user());
        $issue->createIssue($request->all());

        return redirect($issue->to())
            ->with('notice', trans('tinyissue.issue_has_been_created'));
    }

    /**
     * Edit an existing issue form.
     *
     * @param Project   $project
     * @param Issue     $issue
     * @param IssueForm $form
     *
     * @return \Illuminate\View\View
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
        $issue->setRelation('project', $project);
        $issue->setRelation('updatedBy', $this->auth->user());
        $issue->updateIssue($request->all());

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
        $issue->changeStatus($status, $this->auth->user()->id);

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
            if (!$this->auth->user()->permission('project-all')) {
                abort(404);
            }

            $attachment->upload($request->all(), $project, $this->auth->user());

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
     * Ajax: to remove an attachment file.
     *
     * @param Project    $project
     * @param Attachment $attachment
     * @param Request    $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postRemoveAttachment(Project $project, Attachment $attachment, Request $request)
    {
        $attachment->remove($request->all(), $project, $this->auth->user());

        return response()->json(['status' => true]);
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

        $path    = config('tinyissue.uploads_dir') . '/' . $issue->project_id . '/' . $attachment->upload_token . '/' . $attachment->filename;
        $storage = \Storage::disk('local');
        $length  = $storage->size($path);
        $time    = $storage->lastModified($path);
        $type    = $storage->getDriver()->getMimetype($path);

        $response = new Response();
        $response->setEtag(md5($time . $path));
        $response->setExpires(new \DateTime('@' . ($time + 60)));
        $response->setLastModified(new \DateTime('@' . $time));
        $response->setPublic();
        $response->setStatusCode(200);

        $response->header('Content-Type', $type);
        $response->header('Content-Length', $length);
        $response->header('Content-Disposition', 'inline; filename="' . $attachment->filename . '"');
        $response->header('Cache-Control', 'must-revalidate');

        if ($response->isNotModified($request)) {
            // Return empty response if not modified
            return $response;
        }

        // Return file if first request / modified
        $response->setContent($storage->get($path));

        return $response;
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
        $issue->changeProject($request->input('project_id'));

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

        $issue->changeKanbanTag($newTag, $oldTag, $this->auth->user());

        return response()->json(['status' => true, 'issue' => $issue->id]);
    }
}
