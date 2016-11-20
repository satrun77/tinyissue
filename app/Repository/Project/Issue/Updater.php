<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project\Issue;

use Illuminate\Support\Collection;
use Tinyissue\Model\Activity;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;
use Tinyissue\Repository\RepositoryUpdater;

class Updater extends RepositoryUpdater
{
    /**
     * @var Project\Issue
     */
    protected $model;

    public function __construct(Project\Issue $model)
    {
        $this->model = $model;
    }

    /**
     * Set the issue is updated by a user.
     *
     * @return Project\Issue
     */
    public function changeUpdatedBy()
    {
        $this->model->updated_by = $this->user->id;
        $this->model->touch();

        return $this->save();
    }

    /**
     * Reassign the issue to a new user.
     *
     * @param int|User $assignTo
     * @param User     $user
     *
     * @return Project\Issue
     */
    public function reassign($assignTo, User $user)
    {
        $this->setUser($user);
        $assignToId = !$assignTo instanceof User ? $assignTo : $assignTo->id;
        $this->model->assigned_to = $assignToId;

        // Add event on successful save
        Project\Issue::saved(function (Project\Issue $issue) {
            $this->queueAssign($issue, $this->user);
        });

        $this->save();

        $this->saveToActivities([
            'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
            'parent_id' => $this->model->project->id,
            'user_id'   => $user->id,
            'action_id' => $this->model->assigned_to,
        ]);

        return $this->model;
    }

    protected function filterUpdateAttributes(array $input)
    {
        $fill = array_only($input, ['title', 'body', 'assigned_to']);
        $fill['updated_by'] = $this->model->updatedBy->id;
        $fill['lock_quote'] = (bool)isset($input['time_quote']['lock']);

        // Only save quote if not locked or locked & user allowed to modify it
        if (array_key_exists('time_quote', $input) &&
            (!$this->model->isQuoteLocked() || $this->getLoggedUser()->can('lockQuote', $this->model))
        ) {
            $fill['time_quote'] = $input['time_quote'];
        }

        return $fill;
    }

    /**
     * Update the given issue.
     *
     * @param array $input
     *
     * @return Project\Issue
     */
    public function update(array $input = [])
    {
        $this->model->fill($this->filterUpdateAttributes($input));

        /* Add to activity log for assignment if changed */
        if ($this->model->isDirty('assigned_to') && $this->model->assigned_to > 0) {
            $this->saveToActivities([
                'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
                'parent_id' => $this->model->project->id,
                'user_id'   => $this->model->updatedBy->id,
                'action_id' => $this->model->assigned_to,
            ]);
        }

        $this->syncTags($input, $this->model->tags()->with('parent')->get());

        // Add event on successful save
        Project\Issue::saved(function (Project\Issue $issue) {
            $this->queueUpdate($issue, $this->user);
        });

        return $this->save();
    }

    /**
     * Create a new issue.
     *
     * @param array $input
     *
     * @return Project\Issue
     */
    public function create(array $input)
    {
        if (array_key_exists('project_id', $input)) {
            $this->model->setRelation('project', Project::find((int)$input['project_id']));
        }

        $fill = [
            'created_by'  => $this->model->user->id,
            'project_id'  => $this->model->project->id,
            'title'       => $input['title'],
            'body'        => $input['body'],
            'assigned_to' => (int)$this->model->project->default_assignee,
        ];

        if ($this->model->user->isDeveloperOrMore()) {
            $fill['assigned_to'] = array_get($input, 'assigned_to', $fill['assigned_to']);
            $fill['time_quote'] = array_get($input, 'time_quote');
        }

        // Project internal issue number
        $this->model->issue_no = $this->model->forProject($this->model->project->id)->max('issue_no') + 1;

        $this->model->fill($fill)->save();

        // Add issue to messages queue
        $this->queueAdd($this->model, $this->user);

        /* Add to user's activity log */
        $this->saveToActivities([
            'type_id'   => Activity::TYPE_CREATE_ISSUE,
            'parent_id' => $this->model->project->id,
            'user_id'   => $this->model->user->id,
        ]);

        /* Add attachments to issue */
        Project\Issue\Attachment::instance()->updater()->updateIssueToken($input['upload_token'], $this->model->user->id, $this->model->id);

        // Add default tag to newly created issue
        $defaultTag = app('tinyissue.settings')->getFirstStatusTagId();
        if ($defaultTag > 0 && empty($input['tag_status'])) {
            $input['tag_status'] = $defaultTag;
        }

        $this->syncTags($input);

        return $this->model;
    }

    /**
     * Move the issue (comments & activities) to another project.
     *
     * @param int $projectId
     *
     * @return Project\Issue
     */
    public function changeProject($projectId)
    {
        $this->model->project_id = $projectId;
        $this->save();
        $comments = $this->model->comments()->get();
        foreach ($comments as $comment) {
            $comment->project_id = $projectId;
            $comment->save();
        }

        $activities = $this->model->activities()->get();
        foreach ($activities as $activity) {
            $activity->parent_id = $projectId;
            $activity->save();
        }

        return $this->model;
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
        return $this->transaction('deleteIssue');
    }

    protected function deleteIssue()
    {
        // Delete issue related data
        $this->deleteComments();
        $this->deleteAttachments();
        $this->deleteUserActivities();
        $this->deleteIssueTags();

        // Delete the issue
        return $this->model->delete();
    }

    /**
     * @return void
     */
    protected function deleteComments()
    {
        $this->model->comments->each(function (Project\Issue\Comment $comment) {
            $comment->updater($this->getLoggedUser())->delete();
        });
    }

    /**
     * @return void
     */
    protected function deleteAttachments()
    {
        $this->model->attachments->each(function (Project\Issue\Attachment $attachment) {
            $attachment->updater()->delete();
        });
    }

    /**
     * @return void
     */
    protected function deleteIssueTags()
    {
        \DB::table('projects_issues_tags')->where('issue_id', '=', $this->model->id)->delete();
    }

    /**
     * @return void
     */
    protected function deleteUserActivities()
    {
        User\Activity::where('parent_id', '=', $this->model->project_id)
            ->where('item_id', '=', $this->model->id)
            ->delete();
    }

    /**
     * Change the status of an issue.
     *
     * @param int  $status
     * @param User $user
     *
     * @return Project\Issue
     */
    public function changeStatus($status, User $user)
    {
        if ($status == 0) {
            $this->model->closed_by = $user->id;
            $this->model->closed_at = (new \DateTime())->format('Y-m-d H:i:s');
            $activityType = Activity::TYPE_CLOSE_ISSUE;
        } else {
            $this->model->closed_by = 0;
            $this->model->closed_at = null;
            $activityType = Activity::TYPE_REOPEN_ISSUE;
        }

        /* Add to activity log */
        $this->saveToActivities([
            'type_id'   => $activityType,
            'parent_id' => $this->model->project->id,
            'user_id'   => $user->id,
        ]);

        $this->model->status = $status;

        // Add event on successful save
        Project\Issue::saved(function (Project\Issue $issue) {
            $this->queueUpdate($issue, $this->user);
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
        $currentTags = is_null($currentTags) ? Collection::make([]) : $currentTags;

        // User can edit their own role and can only change issue type
        if ($this->model->updatedBy instanceof User && $this->model->updatedBy->isUser()) {
            $currentTagIds = $currentTags->pluck('id', 'parent.name')->toArray();
            $tagIds['tag_status'] = array_key_exists('status', $currentTagIds) ? $currentTagIds['status'] : 0;
            $tagIds['tag_resolution'] = array_key_exists('resolution', $currentTagIds) ? $currentTagIds['resolution'] : 0;
        }

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
        $this->model->tags()->sync($tags->pluck('id')->all());

        // Activity is added when new issue create with tags or updated with tags excluding the open status tag
        if (!empty($removedTags) || !empty($addedTags)) {
            // Add this change to messages queue
            $this->queueChangeTags($this->model, $addedTags, $removedTags, $this->user);

            // Add to activity log for tags if changed
            $this->saveToActivities([
                'type_id'   => Activity::TYPE_ISSUE_TAG,
                'parent_id' => $this->model->project->id,
                'user_id'   => $this->model->user->id,
                'data'      => ['added_tags' => $addedTags, 'removed_tags' => $removedTags],
            ]);
        }

        return true;
    }

    /**
     * Add tag to the issue & close issue if added tag is Closed.
     *
     * @param Tag $newTag
     * @param Tag $oldTag
     *
     * @return Project\Issue
     */
    public function changeKanbanTag(Tag $newTag, Tag $oldTag)
    {
        //  skip if there is no change in status tags
        if ($oldTag->name === $newTag->name) {
            return $this->model;
        }

        // Open issue
        $data = ['added_tags' => [], 'removed_tags' => []];

        // Remove previous status tag
        $this->model->tags()->detach($oldTag);
        $data['removed_tags'][] = $oldTag->toShortArray();

        // Add new tag
        if (!$this->model->tags->contains($newTag)) {
            $this->model->tags()->attach($newTag);

            $data['added_tags'][] = $newTag->toShortArray();
        }

        if (!empty($data)) {
            // Add this change to messages queue
            $this->queueChangeTags($this->model, $data['added_tags'], $data['removed_tags'], $this->user);

            // Add to activity log for tags if changed
            $this->saveToActivities([
                'type_id'   => Activity::TYPE_ISSUE_TAG,
                'parent_id' => $this->model->project->id,
                'user_id'   => $this->model->user->id,
                'data'      => $data,
            ]);
        }

        return $this->model;
    }

    /**
     * Insert update issue to message queue.
     *
     * @param Project\Issue $issue
     * @param User          $changeBy
     *
     * @return void
     */
    public function queueUpdate(Project\Issue $issue, User $changeBy)
    {
        // is Closed?
        $this->queueClosedIssue($issue, $changeBy);

        // is Reopened?
        $this->queueReopenedIssue($issue, $changeBy);

        // If the assignee has changed and it is not the logged in user who made the action
        $noMessageForMe = $this->queueAssign($issue, $changeBy);

        // If the update was just for assigning user, then skip update issue
        $this->queueUpdateIssue($issue, $changeBy, $noMessageForMe);
    }

    /**
     * @param Project\Issue $issue
     * @param User          $changeBy
     * @param bool|int      $noMessageForMe
     */
    protected function queueUpdateIssue(Project\Issue $issue, User $changeBy, $noMessageForMe = false)
    {
        // Number of changed attributes
        $countChanges = count($issue->getDirty());

        if (!($countChanges === 1 && $noMessageForMe !== false)) {
            return (new Queue())->updater($changeBy)->queue(Queue::UPDATE_ISSUE, $issue, $changeBy);
        }
    }

    /**
     * @param Project\Issue $issue
     * @param User          $changeBy
     */
    protected function queueClosedIssue(Project\Issue $issue, User $changeBy)
    {
        if (!$issue->isOpen()) {
            (new Queue())->updater($changeBy)->queue(Queue::CLOSE_ISSUE, $issue, $changeBy);
        }
    }

    /**
     * @param Project\Issue $issue
     * @param User          $changeBy
     */
    protected function queueReopenedIssue(Project\Issue $issue, User $changeBy)
    {
        if ((int)$issue->getOriginal('status') === Project\Issue::STATUS_CLOSED) {
            (new Queue())->updater($changeBy)->queue(Queue::REOPEN_ISSUE, $issue, $changeBy);
        }
    }

    /**
     * Insert add issue to message queue.
     *
     * @param Project\Issue $issue
     * @param User          $changeBy
     *
     * @return void
     */
    public function queueAdd(Project\Issue $issue, User $changeBy)
    {
        return (new Queue())->updater($changeBy)->queue(Queue::ADD_ISSUE, $issue, $changeBy);
    }

    /**
     * Insert assign issue to message queue.
     *
     * @param Project\Issue $issue
     * @param User          $changeBy
     *
     * @return bool|int
     */
    public function queueAssign(Project\Issue $issue, User $changeBy)
    {
        // Whether or not the assignee has changed
        $return = false;

        // If the assignee has changed and it is not the logged in user who made the action
        if ($issue->assigned_to > 0 && $changeBy->id !== $issue->assigned_to && $issue->assigned_to !== $issue->getOriginal('assigned_to', $issue->assigned_to)) {
            (new Queue())->updater($changeBy)->queue(Queue::ASSIGN_ISSUE, $issue, $changeBy);

            $return = $issue->assigned_to;
        }

        return $return;
    }

    /**
     * Insert issue tag changes to message queue.
     *
     * @param Project\Issue $issue
     * @param array         $addedTags
     * @param array         $removedTags
     * @param User          $changeBy
     *
     * @return mixed
     */
    public function queueChangeTags(Project\Issue $issue, array $addedTags, array $removedTags, User $changeBy)
    {
        return (new Queue())->updater($changeBy)->queueIssueTagChanges($issue, $addedTags, $removedTags, $changeBy);
    }
}
