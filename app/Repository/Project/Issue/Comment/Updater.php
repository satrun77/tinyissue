<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project\Issue\Comment;

use Tinyissue\Model\Activity;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Project\Issue\Attachment;
use Tinyissue\Model\Project\Issue\Comment;
use Tinyissue\Model\User;
use Tinyissue\Repository\RepositoryUpdater;

class Updater extends RepositoryUpdater
{
    /**
     * @var Comment
     */
    protected $model;

    public function __construct(Comment $model)
    {
        $this->model = $model;
    }

    /**
     * Create new comment.
     *
     * @param array $input
     *
     * @return Comment
     */
    public function create(array $input)
    {
        $fill = [
            'created_by' => $this->model->user->id,
            'project_id' => $this->model->project->id,
            'issue_id'   => $this->model->issue->id,
            'comment'    => $input['comment'],
        ];

        $this->model->fill($fill);

        // Add event on successful save
        Comment::saved(function (Comment $comment) {
            $this->queueAdd($comment, $this->user);
        });

        $this->save();

        /* Add to user's activity log */
        $this->saveToActivity([
            'type_id'   => Activity::TYPE_COMMENT,
            'parent_id' => $this->model->project->id,
            'item_id'   => $this->model->issue->id,
            'user_id'   => $this->model->user->id,
        ]);

        /* Add attachments to issue */
        Attachment::instance()->updater()->updateCommentToken(
            array_get($input, 'upload_token'),
            $this->model->user->id,
            $this->model->issue->id,
            $this->model->id
        );

        /* Update the project */
        $this->model->issue->updater($this->model->user)->changeUpdatedBy();

        return $this->model;
    }

    /**
     * Update comment body.
     *
     * @param string $body
     *
     * @return Comment
     */
    public function updateBody($body)
    {
        $this->model->fill([
            'comment' => $body,
        ]);

        // Add event on successful save
        Comment::saved(function (Comment $comment) {
            $this->queueUpdate($comment, $this->user);
        });

        return $this->save();
    }

    public function delete()
    {
        return $this->transaction('deleteComment');
    }

    /**
     * Delete a comment and its attachments.
     *
     * @return Comment
     *
     * @throws \Exception
     */
    protected function deleteComment()
    {
        $this->model->activity()->delete();

        foreach ($this->model->attachments as $attachment) {
            $attachment->updater()->delete();
        }

        // Add event on successful delete
        Comment::deleted(function (Comment $comment) {
            $this->queueDelete($comment, $this->user);
        });

        return $this->model->delete();
    }

    /**
     * Insert add comment to message queue.
     *
     * @param Comment  $comment
     * @param User $changeBy
     *
     * @return void
     */
    public function queueAdd(Comment $comment, User $changeBy)
    {
        // Skip message if issue closed
        if (!$comment->issue->isOpen()) {
            return;
        }

        return (new Queue())->updater($changeBy)->queue(Queue::ADD_COMMENT, $comment, $changeBy);
    }

    /**
     * Insert update comment to message queue.
     *
     * @param Comment  $comment
     * @param User $changeBy
     *
     * @return void
     */
    public function queueUpdate(Comment $comment, User $changeBy)
    {
        // Skip message if issue closed or nothing changed in comment
        if (!$comment->issue->isOpen() || !$comment->isDirty()) {
            return;
        }

        return (new Queue())->updater($changeBy)->queue(Queue::UPDATE_COMMENT, $comment, $changeBy);
    }

    /**
     * Insert delete comment to message queue.
     *
     * @param Comment  $comment
     * @param User $changeBy
     *
     * @return void
     */
    public function queueDelete(Comment $comment, User $changeBy)
    {
        // Skip message if issue closed
        if ($comment->issue instanceof Issue && !$comment->issue->isOpen()) {
            return;
        }

        return (new Queue())->updater($changeBy)->queueDelete(Queue::DELETE_COMMENT, $comment, $changeBy);
    }
}
