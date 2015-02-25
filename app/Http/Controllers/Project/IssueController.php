<?php

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

class IssueController extends Controller
{
    /**
     * View a issue.
     *
     * @return View
     */
    public function getIndex(Project $project, Issue $issue, CommentForm $form, Request $request)
    {
        $issue->attachments->each(function ($attachment) use ($issue) {
            $attachment->setRelation('issue', $issue);
        });
        $activities = $issue->activities()->with('activity', 'user', 'comment', 'assignTo',
            'comment.attachments')->get();
        $activities->each(function ($activity) use ($issue) {
            $activity->setRelation('issue', $issue);
        });

        return view('project.issue.index', [
            'issue' => $issue,
            'project' => $project,
            'commentForm' => $form,
            'activities' => $activities,
            'sidebar' => 'project',
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    public function postAssign(Issue $issue, Request $request)
    {
        $response = ['status' => false];
        if ($issue->reassign((int) $request->input('assign'))) {
            $response['status'] = true;
        }

        return response()->json($response);
    }

    /**
     * Update / Edit a comment.
     *
     * @return string
     */
    public function postEditComment(Comment $comment, Request $request)
    {
        $body = '';
        if ($request->has('body')) {
            $comment->fill(array('comment' => $request->input('body')))
                ->save();
            $body = \Html::format($comment->comment);
        }

        return response()->json(['text' => $body]);
    }

    /**
     * Post a comment to a issue.
     *
     * @return Redirect
     */
    public function getAddComment(Project $project, Issue $issue, Comment $comment, FormRequest\Comment $request)
    {
        $comment->setRelation('project', $project);
        $comment->setRelation('issue', $issue);
        $comment->createComment($request->all(), $this->auth->user()->id);

        return redirect($issue->to().'#comment'.$comment->id)
            ->with('notice', trans('tinyissue.your_comment_added'));
    }

    public function getDeleteComment(Comment $comment)
    {
        $comment->deleteComment();

        return response()->json(['status' => true]);
    }

    /**
     * Create a new issue.
     *
     * @return View
     */
    public function getNew(Project $project, IssueForm $form)
    {
        return view('project.issue.new', array(
            'project' => $project,
            'form' => $form,
            'sidebar' => 'project'
        ));
    }

    public function postNew(Project $project, Issue $issue, FormRequest\Issue $request)
    {
        $issue->setRelation('project', $project);
        $issue->createIssue($request->all(), $this->auth->user()->id);

        return redirect($issue->to())
            ->with('notice', trans('tinyissue.issue_has_been_created'));
    }

    /**
     * Edit a issue.
     *
     * @return View
     */
    public function getEdit(Project $project, Issue $issue, IssueForm $form)
    {
        return view('project.issue.edit', [
            'issue' => $issue,
            'project' => $project,
            'form' => $form,
            'sidebar' => 'project'
        ]);
    }

    public function postEdit(Project $project, Issue $issue, FormRequest\Issue $request)
    {
        $issue->setRelation('project', $project);
        $issue->updateIssue($request->all(), $this->auth->user()->id);

        return redirect($issue->to())
            ->with('notice', trans('tinyissue.issue_has_been_updated'));
    }

    /**
     * Change the status of a issue.
     *
     * @return Redirect
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

    public function postUploadAttachment(Project $project, Attachment $attachment, Request $request)
    {
        $userId = \Crypt::decrypt(str_replace(' ', '+', $request->input('session')));
        if ((int) $userId !== (int) $this->auth->user()->id) {
            return abort(404);
        }

        if (!$this->auth->user()->permission('project-all')) {
            return abort(404);
        }

        $attachment->upload($request->all(), $project, $userId);

        return response()->json(['status' => true]);
    }

    public function postRemoveAttachment(Project $project, Attachment $attachment, Request $request)
    {
        $userId = \Crypt::decrypt(str_replace(' ', '+', $request->input('session')));
        if ((int) $userId !== (int) $this->auth->user()->id) {
            return abort(404);
        }

        $attachment->remove($request->all(), $project, $userId);

        return response()->json(['status' => true]);
    }

    public function getDisplayAttachment(Project $project, Issue $issue, Attachment $attachment, Request $request)
    {
        $issue->setRelation('project', $project);
        $attachment->setRelation('issue', $issue);

        $path = 'uploads/'.$issue->project_id.'/'.$attachment->upload_token.'/'.$attachment->filename;
        $storage = \Storage::disk('local');
        $length = $storage->size($path);
        $time = $storage->lastModified($path);
        $type = $storage->getDriver()->getMimetype($path);

        $response = new Response();
        $response->setEtag(md5($time.$path));
        $response->setExpires(new \DateTime('@'.($time + 60)));
        $response->setLastModified(new \DateTime('@'.$time));
        $response->setPublic();
        $response->setStatusCode(200);

        $response->header('Content-Type', $type);
        $response->header('Content-Length', $length);
        $response->header('Content-Disposition', 'inline; filename="'.$attachment->filename.'"');
        $response->header('Cache-Control', 'must-revalidate');

        if ($response->isNotModified($request)) {
            // Return empty response if not modified
            return $response;
        }

        // Return file if first request / modified
        $response->setContent($storage->get($path));

        return $response;
    }

    public function getDownloadAttachment(Project $project, Issue $issue, Attachment $attachment)
    {
        $issue->setRelation('project', $project);
        $attachment->setRelation('issue', $issue);

        $path = config('filesystems.disks.local.root').'/uploads/'.$this->issue->project_id.'/'.$this->upload_token.'/'.$attachment->filename;

        return response()->download($path, $attachment->filename);
    }

    public function postChangeProject(Issue $issue, Request $request)
    {
        $issue->changeProject($request->input('project_id'));

        return response()->json(['status' => true, 'url' => $issue->to()]);
    }
}
