<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Issue\Comment;

use Illuminate\Database\Eloquent;
use Tinyissue\Model;
use Tinyissue\Model\Activity;
use Tinyissue\Model\User;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Project\Issue\Attachment;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the Project\Issue\Comment model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait CrudTrait
{
    /**
     * Create new comment.
     *
     * @param array $input
     *
     * @return $this
     */
    public function createComment(array $input)
    {
        $fill = [
            'created_by' => $this->user->id,
            'project_id' => $this->project->id,
            'issue_id'   => $this->issue->id,
            'comment'    => $input['comment'],
        ];

        $this->fill($fill);

        // Add event on successful save
        static::saved(function (Issue\Comment $comment) {
            $this->queueAdd($comment, $comment->user);
        });

        $this->save();

        /* Add to user's activity log */
        $this->activity()->save(new User\Activity([
            'type_id'   => Activity::TYPE_COMMENT,
            'parent_id' => $this->project->id,
            'item_id'   => $this->issue->id,
            'user_id'   => $this->user->id,
        ]));

        /* Add attachments to issue */
        Attachment::where('upload_token', '=', $input['upload_token'])
            ->where('uploaded_by', '=', $this->user->id)
            ->update(['issue_id' => $this->issue->id, 'comment_id' => $this->id]);

        /* Update the project */
        $this->issue->changeUpdatedBy($this->user->id);

        return $this;
    }

    /**
     * Update comment body.
     *
     * @param string $body
     * @param User   $user
     *
     * @return Eloquent\Model
     */
    public function updateBody($body, User $user)
    {
        $this->fill([
            'comment' => $body,
        ]);

        // Add event on successful save
        static::saved(function (Issue\Comment $comment) use ($user) {
            $this->queueUpdate($comment, $user);
        });

        return $this->save();
    }

    /**
     * Delete a comment and its attachments.
     *
     * @param User $user
     *
     * @return Eloquent\Model
     *
     * @throws \Exception
     */
    public function deleteComment(User $user)
    {
        $this->activity()->delete();

        foreach ($this->attachments as $attachment) {
            $path = config('filesystems.disks.local.root')
                . '/' . config('tinyissue.uploads_dir')
                . '/' . $this->project_id
                . '/' . $attachment->upload_token;
            $attachment->deleteFile($path, $attachment->filename);
            $attachment->delete();
        }

        // Add event on successful delete
        static::deleted(function (Issue\Comment $comment) use ($user) {
            $this->queueDelete($comment, $user);
        });

        return $this->delete();
    }

    abstract public function fill(array $attributes);
    abstract public function queueAdd(Issue\Comment $issue, $changeBy);
    abstract public function queueUpdate(Issue\Comment $issue, $changeBy);
    abstract public function queueDelete(Issue\Comment $issue, $changeBy);
    abstract public function save(array $options = []);
    abstract public function activity();
}
