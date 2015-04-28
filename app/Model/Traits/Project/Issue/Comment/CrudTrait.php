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
use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model;
use Tinyissue\Model\Activity;
use Tinyissue\Model\User;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Project\Issue\Attachment;
use Illuminate\Database\Eloquent\Collection;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the Project\Issue\Comment model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int                $id
 * @property int                $issue_id
 * @property int                $project_id
 * @property string             $comment
 * @property int                $created_by
 * @property User               $user
 * @property Project            $project
 * @property Issue              $issue
 * @property Collection         $attachments
 *
 * @method   Eloquent\Model     save()
 * @method   Eloquent\Model     fill(array $attributes)
 * @method   Relations\HasOne   activity()
 * @method   Eloquent\Model     delete()
 */
trait CrudTrait
{
    /**
     * Create new comment
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
     * Delete a comment and its attachments
     *
     * @return Eloquent\Model
     *
     * @throws \Exception
     */
    public function deleteComment()
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

        return $this->delete();
    }
}
