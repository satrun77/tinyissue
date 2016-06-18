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
use Illuminate\Support\Collection;
use Tinyissue\Model;
use Tinyissue\Model\Activity;
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;

/**
 * CrudTagTrait is trait class containing the methods for adding/editing/deleting the tags of Project\Issue model.
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
 * @property Model\Project $project
 * @property Model\User $user
 * @property Model\User $updatedBy
 */
trait CrudTagTrait
{
    /**
     * Change the status of an issue.
     *
     * @param int  $status
     * @param User $userId
     *
     * @return Eloquent\Model
     */
    public function changeStatus($status, User $user)
    {
        if ($status == 0) {
            $this->closed_by = $user->id;
            $this->closed_at = (new \DateTime())->format('Y-m-d H:i:s');
            $activityType    = Activity::TYPE_CLOSE_ISSUE;
        } else {
            $this->closed_by = 0;
            $this->closed_at = null;
            $activityType    = Activity::TYPE_REOPEN_ISSUE;
        }

        /* Add to activity log */
        $this->activities()->save(new User\Activity([
            'type_id'   => $activityType,
            'parent_id' => $this->project->id,
            'user_id'   => $userId,
        ]));

        $this->status = $status;

        // Add event on successful save
        static::saved(function (Project\Issue $issue) use ($user) {
            $this->queueUpdate($issue, $user);
        });

        return $this->save();
    }

    /**
     * Sync the issue tags.
     *
     * @param array      $input
     * @param Collection $currentTags
     *
     * @return bool
     */
    public function syncTags(array $input, Collection $currentTags = null)
    {
        $tagIds = array_only($input, [
            'tag_type', 'tag_status', 'tag_resolution',
        ]);
        $tags = (new Tag())->whereIn('id', $tagIds)->get();

        $removedTags = [];
        if (null === $currentTags) {
            // Add the following tags except for open status
            $addedTags = $tags
                ->map(function (Tag $tag) {
                    return $tag->toShortArray();
                })
                ->toArray();
        } else {
            // Tags remove from the issue
            $removedTags = $currentTags
                ->diff($tags)
                ->map(function (Tag $tag) {
                    return $tag->toShortArray();
                })
                ->toArray();

            // Check if we are adding new tags
            $addedTags = $tags
                ->filter(function (Tag $tag) use ($currentTags) {
                    return $currentTags->where('id', $tag->id)->count() === 0;
                })
                ->map(function (Tag $tag) {
                    return $tag->toShortArray();
                })
                ->toArray();

            // No new tags to add or remove
            if (empty($removedTags) && empty($addedTags)) {
                return true;
            }
        }

        // Save relation
        $this->tags()->sync($tags->lists('id')->all());

        // Activity is added when new issue create with tags or updated with tags excluding the open status tag
        if (!empty($removedTags) || !empty($addedTags)) {
            // Add this change to messages queue
            $this->queueChangeTags($this, $addedTags, $removedTags, $this->user);

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
     * Add tag to the issue & close issue if added tag is Closed.
     *
     * @param Tag $newTag
     * @param Tag $oldTag
     *
     * @return $this
     */
    public function changeKanbanTag(Tag $newTag, Tag $oldTag)
    {
        //  skip if there is no change in status tags
        if ($oldTag->name === $newTag->name) {
            return $this;
        }

        // Open issue
        $data = ['added_tags' => [], 'removed_tags' => []];

        // Remove previous status tag
        $this->tags()->detach($oldTag);
        $data['removed_tags'][] = $oldTag->toShortArray();

        // Add new tag
        if (!$this->tags->contains($newTag)) {
            $this->tags()->attach($newTag);

            $data['added_tags'][] = $newTag->toShortArray();
        }

        if (!empty($data)) {
            // Add this change to messages queue
            $this->queueChangeTags($this, $data['added_tags'], $data['removed_tags'], $this->user);

            // Add to activity log for tags if changed
            $this->activities()->save(new User\Activity([
                'type_id'   => Activity::TYPE_ISSUE_TAG,
                'parent_id' => $this->project->id,
                'user_id'   => $this->user->id,
                'data'      => $data,
            ]));
        }

        return $this;
    }
}
