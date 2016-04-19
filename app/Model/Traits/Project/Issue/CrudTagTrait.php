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
use Tinyissue\Model\Traits\Tag\DataMappingTrait;
use Tinyissue\Model\Traits\Tag\FilterTrait;
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
    use DataMappingTrait,
        FilterTrait;

    /**
     * Change the status of an issue.
     *
     * @param int $status
     * @param int $userId
     *
     * @return Eloquent\Model
     */
    public function changeStatus($status, $userId)
    {
        if ($status == 0) {
            $this->closed_by = $userId;
            $this->closed_at = (new \DateTime())->format('Y-m-d H:i:s');

            $activityType = Activity::TYPE_CLOSE_ISSUE;
            $addTagName   = Tag::STATUS_CLOSED;

            /** @var \Illuminate\Support\Collection $ids */
            $ids = $this->getTagsExceptStatus()->getRelatedIds();
        } else {
            $activityType = Activity::TYPE_REOPEN_ISSUE;
            $removeTag    = Tag::STATUS_CLOSED;
            $addTagName   = Tag::STATUS_OPEN;

            /** @var \Illuminate\Support\Collection $ids */
            $ids = $this->getTagsExcept($removeTag)->getRelatedIds();
        }

        $ids->push((new Tag())->getTagByName($addTagName)->id);

        $this->tags()->sync($ids->unique()->all());

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
            // Open status tag
            $openTag = (new Tag())->getTagByName(Tag::STATUS_OPEN);

            // Add the following tags except for open status
            $addedTags = $tags
                ->filter([$this, 'tagsExceptStatusOpenCallback'])
                ->map([$this, 'toArrayCallback'])
                ->toArray();
        } else {
            // Open status tag
            $openTag = $currentTags->first([$this, 'onlyStatusOpenCallback']);

            // Remove status tag
            $currentTags = $currentTags->filter([$this, 'tagsExceptStatusOpenCallback']);

            // Make sure the tags does not includes the open status
            $tags = $tags->filter([$this, 'tagsExceptStatusOpenCallback']);

            // Tags remove from the issue
            $removedTags = $currentTags
                ->diff($tags)
                ->map([$this, 'toArrayCallback'])
                ->toArray();

            // Check if we are adding new tags
            $addedTags = $tags
                ->filter(function (Tag $tag) use ($currentTags) {
                    return $currentTags->where('id', $tag->id)->count() === 0;
                })
                ->map([$this, 'toArrayCallback'])
                ->toArray();

            // No new tags to add or remove
            if (empty($removedTags) && empty($addedTags)) {
                return true;
            }
        }

        // Make sure open status exists
        $tags->put($openTag->id, $openTag);

        // Save relation
        $this->tags()->sync($tags->lists('id')->all());

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
     * Add tag to the issue & close issue if added tag is Closed.
     *
     * @param Tag  $newTag
     * @param Tag  $oldTag
     * @param User $user
     *
     * @return $this
     */
    public function changeKanbanTag(Tag $newTag, Tag $oldTag, User $user)
    {
        if ($newTag->name === Tag::STATUS_CLOSED) {
            // Close issue
            $this->changeStatus(Project\Issue::STATUS_CLOSED, $user->id);
        } elseif ($oldTag->name !== $newTag->name) {
            // Open issue
            $data = ['added_tags' => [], 'removed_tags' => []];

            // Remove previous status tag
            if ($oldTag->name !== Tag::STATUS_OPEN) {
                $this->tags()->detach($oldTag);
                $data['removed_tags'][] = $oldTag->toArrayCallback($oldTag);
            }

            // Add new tag
            if (!$this->tags->contains($newTag)) {
                $this->tags()->attach($newTag);

                $data['added_tags'][] = $newTag->toArrayCallback($newTag);
            }

            // Make sure open tag exists
            if ($newTag->name !== Tag::STATUS_OPEN && !$this->tags->where('name', Tag::STATUS_OPEN)->first()) {
                $openTag = (new Tag())->getTagByName(Tag::STATUS_OPEN);
                $this->tags()->attach($openTag);

                // Set issue status open
                $this->status = Project\Issue::STATUS_OPEN;
                $this->save();

                // Add to activity log re-open issue
                $this->activities()->save(new User\Activity([
                    'type_id'   => Activity::TYPE_REOPEN_ISSUE,
                    'parent_id' => $this->project->id,
                    'user_id'   => $this->user->id,
                ]));
            }

            // Add to activity log for tags if changed
            if (!empty($data)) {
                $this->activities()->save(new User\Activity([
                    'type_id'   => Activity::TYPE_ISSUE_TAG,
                    'parent_id' => $this->project->id,
                    'user_id'   => $this->user->id,
                    'data'      => $data,
                ]));
            }
        }

        return $this;
    }
}
