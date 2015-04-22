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
use Tinyissue\Model;
use Tinyissue\Model\Activity;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;
use Tinyissue\Model\Project;
use Illuminate\Support\Collection;
use Tinyissue\Model\Project\Issue\Attachment;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the Project\Issue model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int              $id
 * @property int              $created_by
 * @property int              $project_id
 * @property string           $title
 * @property string           $body
 * @property int              $assigned_to
 * @property int              $time_quote
 * @property int              $closed_by
 * @property int              $closed_at
 * @property int              status
 * @property int              $updated_at
 * @property int              $updated_by
 * @property Model\Project    $project
 * @property Model\User       $user
 * @property Model\User       $updatedBy
 *
 * @method   Eloquent\Model   save()
 * @method   Eloquent\Model   fill(array $attributes)
 * @method   Project\Issue    tags()
 * @method   Project\Issue    activities()
 * @method   Project\Issue    comments()
 */
trait CrudTrait
{
    /**
     * Set the issue is updated by a user
     *
     * @param int $userId
     *
     * @return bool
     */
    public function changeUpdatedBy($userId)
    {
        $time = new \DateTime();
        $this->updated_at = $time->format('Y-m-d H:i:s');
        $this->updated_by = $userId;

        return $this->save();
    }

    /**
     * Reassign the issue to a new user
     *
     * @param int|Model\User $assignTo
     * @param int|Model\User $user
     *
     * @return $this
     */
    public function reassign($assignTo, $user)
    {
        $assignToId = !$assignTo instanceof User ? $assignTo : $assignTo->id;
        $userId = !$user instanceof User ? $user : $user->id;
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
     * Change the status of an issue
     *
     * @param int $status
     * @param int $userId
     *
     * @return bool
     */
    public function changeStatus($status, $userId)
    {
        if ($status == 0) {
            $time = new \DateTime();
            $this->closed_by = $userId;
            $this->closed_at = $time->format('Y-m-d H:i:s');

            $activityType = Activity::TYPE_CLOSE_ISSUE;
            $addTagName = Tag::STATUS_CLOSED;

            // Remove all tags of type status
            $statusGroup = Tag::where('name', '=', Tag::GROUP_STATUS)->first();
            $ids = $this->tags()->where('parent_id', '!=', $statusGroup->id)->getRelatedIds();
        } else {
            $activityType = Activity::TYPE_REOPEN_ISSUE;
            $removeTag = Tag::STATUS_CLOSED;
            $addTagName = Tag::STATUS_OPEN;
            $ids = $this->tags()->where('name', '!=', $removeTag)->getRelatedIds();
        }

        $addTag = $this->tags()->where('name', '=', $addTagName)->first();
        if (!$addTag) {
            $addTag = Tag::where('name', '=', $addTagName)->first();
        }

        $ids[] = $addTag->id;
        $this->tags()->sync(array_unique($ids));

        /* Add to activity log */
        $this->activities()->save(new User\Activity([
            'type_id'   => $activityType,
            'parent_id' => $this->project->id,
            'user_id'   => $userId,
        ]));

        $this->status = $status;

        return $this->save();
    }

    /**
     * Update the given issue.
     *
     * @param array $input
     *
     * @return bool
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

        $tags = $this->createTags(
            array_map('trim', explode(',', $input['tag'])),
            $this->user->permission('administration')
        );
        $this->syncTags($tags, $this->tags()->with('parent')->get());

        return $this->save();
    }

    /**
     * Create new tags from a string "group:tag_name" and fetch tag from a tag id.
     *
     * @param array $tags
     * @param bool  $isAdmin
     *
     * @return Collection
     */
    protected function createTags(array $tags, $isAdmin = false)
    {
        $newTags = new Collection($tags);

        // Transform the user input tags into tag objects
        $newTags->transform(function ($tagNameOrId) use ($isAdmin) {
            if (strpos($tagNameOrId, ':') !== false && $isAdmin) {
                return (new Tag())->createTagFromString($tagNameOrId);
            } else {
                return Tag::find($tagNameOrId);
            }
        });

        // Filter out invalid tags entered by the user
        $newTags = $newTags->filter(function ($tag) {
            return $tag instanceof Tag;
        });

        return $newTags;
    }

    /**
     * Sync the issue tags
     *
     * @param Collection $tags
     * @param Collection $currentTags
     *
     * @return bool
     */
    public function syncTags(Collection $tags, Collection $currentTags = null)
    {
        $removedTags = [];
        if (null === $currentTags) {
            $openTag = Tag::where('name', '=', Tag::STATUS_OPEN)->first();

            $addedTags = $tags->filter(function (Tag $tag) {
                return $tag->name !== Tag::STATUS_OPEN;
            })->map(function (Tag $tag) {
                return [
                    'id'      => $tag->id,
                    'name'    => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toArray();
        } else {
            $openTag = $currentTags->first(function ($index, Tag $tag) {
                return $tag->name === Tag::STATUS_OPEN;
            });

            $removedTags = $currentTags->diff($tags)->filter(function (Tag $tag) {
                return $tag->name !== Tag::STATUS_OPEN;
            })->map(function (Tag $tag) {
                return [
                    'id'      => $tag->id,
                    'name'    => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toArray();

            // Check if we are adding new tags
            $addedTags = $tags->filter(function (Tag $tag) use ($currentTags) {
                // Ignore open tag
                if ($tag->name === Tag::STATUS_OPEN) {
                    return false;
                }

                // Get new added tags that are not currently linked to the issue
                $currentTag = $currentTags->first(function ($index, Tag $currentTag) use ($tag) {
                    return $currentTag->id === $tag->id;
                }, false);

                return $currentTag === false;
            })->map(function (Tag $tag) {
                return [
                    'id'      => $tag->id,
                    'name'    => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toArray();

            // No new tags to add or remove
            if (empty($removedTags) && empty($addedTags)) {
                return true;
            }
        }

        // Make sure open status exists
        $tags->put($openTag->id, $openTag);

        // Save relation
        $this->tags()->sync($tags->map(function (Tag $tag) {
            return $tag->id;
        })->toArray());

        // Activity is added when new issue create with tags or updated with tags excluding the open status tag
        if (!empty($removedTags) || !empty($addedTags)) {
            // Add to activity log for tags if changed
            $this->activities()->save(new User\Activity([
                'type_id'   => Activity::TYPE_ISSUE_TAG,
                'parent_id' => $this->project->id,
                'user_id'   => $this->user->id,
                'data'      => ['added_tags' => $addedTags, 'removed_tags' => $removedTags],
            ]));
        }

        return true;
    }

    /**
     * Create a new issue.
     *
     * @param array $input
     *
     * @return Project\Issue
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
            $fill['time_quote'] = $input['time_quote'];
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

        // Create tags
        $tags = $this->createTags(
            array_map('trim', explode(',', $input['tag'])),
            $this->user->permission('administration')
        );
        $this->syncTags($tags);

        return $this;
    }

    /**
     * Move the issue (comments & activities) to another project
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
