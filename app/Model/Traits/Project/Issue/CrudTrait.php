<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Issue;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model;
use Tinyissue\Model\Activity;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue\Attachment;
use Tinyissue\Model\User;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the Project\Issue model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int                        $id
 * @property int                        $created_by
 * @property int                        $project_id
 * @property string                     $title
 * @property string                     $body
 * @property int                        $assigned_to
 * @property int                        $time_quote
 * @property int                        $closed_by
 * @property int                        $closed_at
 * @property int                        status
 * @property int                        $updated_at
 * @property int                        $updated_by
 * @property Project                    $project
 * @property User                       $user
 * @property User                       $updatedBy
 *
 * @method   Eloquent\Model             save()
 * @method   Eloquent\Model             fill(array $attributes)
 * @method   Relations\BelongsToMany    tags()
 * @method   Relations\HasMany          activities()
 * @method   Relations\HasMany          comments()
 */
trait CrudTrait
{
    /**
     * Set the issue is updated by a user.
     *
     * @param int $userId
     *
     * @return Eloquent\Model
     */
    public function changeUpdatedBy($userId)
    {
        $time             = new \DateTime();
        $this->updated_at = $time->format('Y-m-d H:i:s');
        $this->updated_by = $userId;

        return $this->save();
    }

    /**
     * Reassign the issue to a new user.
     *
     * @param int|User $assignTo
     * @param int|User $user
     *
     * @return Eloquent\Model
     */
    public function reassign($assignTo, $user)
    {
        $assignToId        = !$assignTo instanceof User ? $assignTo : $assignTo->id;
        $userId            = !$user instanceof User ? $user : $user->id;
        $this->assigned_to = $assignToId;
        $this->save();

        return $this->activities()->save(new User\Activity([
            'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
            'parent_id' => $this->project->id,
            'user_id'   => $userId,
            'action_id' => $this->assigned_to,
        ]));
    }

    /**
     * Update the given issue.
     *
     * @param array $input
     *
     * @return Eloquent\Model
     */
    public function updateIssue(array $input)
    {
        $fill = [
            'title'       => $input['title'],
            'body'        => $input['body'],
            'assigned_to' => $input['assigned_to'],
            'time_quote'  => $input['time_quote'],
            'updated_by'  => $this->updatedBy->id,
        ];

        /* Add to activity log for assignment if changed */
        if ($input['assigned_to'] != $this->assigned_to) {
            $this->activities()->save(new User\Activity([
                'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
                'parent_id' => $this->project->id,
                'user_id'   => $this->updatedBy->id,
                'action_id' => $this->assigned_to,
            ]));
        }

        $this->fill($fill);

        $this->syncTags($input, $this->tags()->with('parent')->get());

        return $this->save();
    }

    /**
     * Create a new issue.
     *
     * @param array $input
     *
     * @return CrudTrait
     */
    public function createIssue(array $input)
    {
        $fill = [
            'created_by' => $this->user->id,
            'project_id' => $this->project->id,
            'title'      => $input['title'],
            'body'       => $input['body'],
        ];

        if ($this->user->permission('issue-modify')) {
            $fill['assigned_to'] = $input['assigned_to'];
            $fill['time_quote']  = $input['time_quote'];
        }

        $this->fill($fill)->save();

        /* Add to user's activity log */
        $this->activities()->save(new User\Activity([
            'type_id'   => Activity::TYPE_CREATE_ISSUE,
            'parent_id' => $this->project->id,
            'user_id'   => $this->user->id,
        ]));

        /* Add attachments to issue */
        Attachment::where('upload_token', '=', $input['upload_token'])
            ->where('uploaded_by', '=', $this->user->id)
            ->update(['issue_id' => $this->id]);

        $this->syncTags($input);

        return $this;
    }

    /**
     * Move the issue (comments & activities) to another project.
     *
     * @param int $projectId
     *
     * @return $this
     */
    public function changeProject($projectId)
    {
        $this->project_id = $projectId;
        $this->save();
        $comments = $this->comments()->get();
        foreach ($comments as $comment) {
            $comment->project_id = $projectId;
            $comment->save();
        }

        $activities = $this->activities()->get();
        foreach ($activities as $activity) {
            $activity->parent_id = $projectId;
            $activity->save();
        }

        return $this;
    }
}
