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
 * @property int $id
 * @property int $created_by
 * @property int $project_id
 * @property string $title
 * @property string $body
 * @property int $assigned_to
 * @property int $time_quote
 * @property int $closed_by
 * @property int $closed_at
 * @property int status
 * @property int $updated_at
 * @property int $updated_by
 * @property Project $project
 * @property User $user
 * @property User $updatedBy
 * @property Eloquent\Collection $attachments
 * @property Eloquent\Collection $comments
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
        $this->updated_by = $userId;
        $this->touch();

        return $this->save();
    }

    /**
     * Reassign the issue to a new user.
     *
     * @param int|User $assignTo
     * @param User     $user
     *
     * @return Eloquent\Model
     */
    public function reassign($assignTo, User $user)
    {
        $assignToId        = !$assignTo instanceof User ? $assignTo : $assignTo->id;
        $this->assigned_to = $assignToId;

        // Add event on successful save
        static::saved(function (Project\Issue $issue) use ($user) {
            $this->queueAssign($issue, $user);
        });

        $this->save();

        return $this->activities()->save(new User\Activity([
            'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
            'parent_id' => $this->project->id,
            'user_id'   => $user->id,
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
        $fill = array_only($input, [
            'title', 'body', 'assigned_to',
        ]);
        $fill['updated_by'] = $this->updatedBy->id;

        if (isset($input['time_quote']['lock'])) {
            $fill['lock_quote'] = $input['time_quote']['lock'];
        }

        // Only save quote if not locked or locked & user allowed to modify it
        if (array_key_exists('time_quote', $input) &&
            (!$this->isQuoteLocked() || $this->user->permission(Model\Permission::PERM_ISSUE_LOCK_QUOTE))
        ) {
            $fill['time_quote'] = $input['time_quote'];
        }

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

        // Add event on successful save
        static::saved(function (Project\Issue $issue) {
            $this->queueUpdate($issue, $issue->updatedBy);
        });

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

        // Add issue to messages queue
        $this->queueAdd($this, $this->user);

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

        // Add default tag to newly created issue
        $defaultTag = app('tinyissue.settings')->getFirstStatusTagId();
        if ($defaultTag > 0 && empty($input['tag_status'])) {
            $input['tag_status'] = $defaultTag;
        }

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

    /**
     * Delete an issue.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function delete()
    {
        $id          = $this->id;
        $projectId   = $this->project_id;
        $comments    = $this->comments;
        $attachments = $this->attachments;

        $status = parent::delete();

        if ($status) {
            $attachments->each(function (Attachment $attachment) use ($projectId) {
                $path = config('filesystems.disks.local.root')
                    . '/' . config('tinyissue.uploads_dir')
                    . '/' . $projectId
                    . '/' . $attachment->upload_token;
                $attachment->deleteFile($path, $attachment->filename);
                $attachment->delete();
            });
            $comments->each(function (Project\Issue\Comment $comment) {
                $comment->deleteComment(auth()->user());
            });
            User\Activity::where('parent_id', '=', $projectId)->where('item_id', '=', $id)->delete();
            \DB::table('projects_issues_tags')->where('issue_id', '=', $id)->delete();
        }

        return $status;
    }
}
