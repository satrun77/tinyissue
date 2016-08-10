<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * QueryTrait is trait class containing the database queries methods for the Project model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait QueryTrait
{
    /**
     * Returns collection of active projects.
     *
     * @return Eloquent\Collection
     */
    public static function activeProjects()
    {
        return static::where('status', '=', Project::STATUS_OPEN)
            ->orderBy('name', 'ASC')
            ->get();
    }

    /**
     * Returns collection of public projects.
     *
     * @return Eloquent\Collection
     */
    public function publicProjects()
    {
        return $this->where('private', '=', Project::PRIVATE_NO)
            ->orderBy('name', 'ASC')
            ->get();
    }

    /**
     * Returns all users that are not assigned in the current project.
     *
     * @return array
     */
    public function usersNotIn()
    {
        if ($this->id > 0) {
            $userIds = $this->users()->lists('user_id')->all();
            $users   = User::where('deleted', '=', User::NOT_DELETED_USERS)->whereNotIn('id', $userIds)->get();
        } else {
            $users = User::where('deleted', '=', User::NOT_DELETED_USERS)->get();
        }

        return $users->lists('fullname', 'id')->all();
    }

    /**
     * Fetch and filter issues in the project.
     *
     * @param int   $status
     * @param array $filter
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listIssues($status = Project\Issue::STATUS_OPEN, array $filter = [])
    {
        $sortOrder = array_get($filter, 'sort.sortorder', 'desc');
        $sortBy    = array_get($filter, 'sort.sortby', null);

        $query = $this->issues()
            ->with('countComments', 'user', 'updatedBy', 'tags', 'tags.parent')
            ->with([
                'tags' => function (Relation $query) use ($sortOrder) {
                    $query->orderBy('name', $sortOrder);
                },
            ])
            ->where('status', '=', $status);

        // Filter issues
        $this->filterAssignTo($query, array_get($filter, 'assignto'));
        $this->filterTitleOrBody($query, array_get($filter, 'keyword'));
        $this->filterTags($query, array_get($filter, 'tag_status'));
        $this->filterTags($query, array_get($filter, 'tag_type'));
        $this->filterCreatedBy($query, array_get($filter, 'created_by'), $this->isPrivateInternal());

        // Sort
        if ($sortBy == 'updated') {
            $this->sortByUpdated($query, $sortOrder);
        } elseif (($tagGroup = substr($sortBy, strlen('tag:'))) > 0) {
            return $this->sortByTag($query, $tagGroup, $sortOrder);
        }

        return $query->get();
    }

    /**
     * Fetch issues assigned to a user.
     *
     * @param User $user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listAssignedOrCreatedIssues(User $user)
    {
        $assignedOrCreate = $user->isUser() ? 'created_by' : 'assigned_to';

        return $this->issues()
            ->with('countComments', 'user', 'updatedBy')
            ->where('status', '=', Project\Issue::STATUS_OPEN)
            ->where($assignedOrCreate, '=', $user->id)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * Returns projects with issues details eager loaded.
     *
     * @param int $status
     * @param int $private
     *
     * @return Relations\HasMany
     */
    public function projectsWidthIssues($status = Project::STATUS_OPEN, $private = Project::PRIVATE_NO)
    {
        $query = $this
            ->where('status', '=', $status)
            ->orderBy('name');

        if ($private !== Project::PRIVATE_ALL) {
            $query->where('private', '=', $private);
        }

        $query->with([
            'issues' => function (Relations\Relation $query) use ($status) {
                $query->with('updatedBy');
                if ($status === Project::STATUS_OPEN) {
                    $query->where('status', '=', Project\Issue::STATUS_OPEN);
                }
            },
            'issues.user' => function () {
            },
            'issues.countComments' => function () {
            },
        ]);

        return $query;
    }

    /**
     * Returns collection of tags for Kanban view.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function getKanbanTagsForUser(User $user)
    {
        $tags = $this->kanbanTags()
            ->where(function (Eloquent\Builder $query) use ($user) {
                $query->where('role_limit', '<=', $user->role_id);
                $query->orWhere('role_limit', '=', null);
            })
            ->get();

        return $tags;
    }

    /**
     * Returns collection of issues grouped by tags.
     *
     * @param $tagIds
     *
     * @return mixed
     */
    public function issuesGroupByTags($tagIds)
    {
        $issues = $this->issues()
        ->with('user', 'tags')
            ->where('status', '=', Project\Issue::STATUS_OPEN)
            ->whereIn('projects_issues_tags.tag_id', $tagIds)
            ->join('projects_issues_tags', 'issue_id', '=', 'id')
            ->orderBy('id')
            ->get()
            ->groupBy(function (Project\Issue $issue) {
                return $issue->getStatusTag()->name;
            });

        return $issues;
    }

    /**
     * Returns users assigned to the project that can fix issues (with edit permission).
     *
     * @return Relations\BelongsToMany
     */
    public function usersCanFixIssue()
    {
        return $this->users()->where('users.role_id', '>', 1)->where('users.deleted', '=', User::NOT_DELETED_USERS);
    }

    abstract public function users();
    abstract public function filterAssignTo(Relations\HasMany $query, $userId);
    abstract public function filterTitleOrBody(Relations\HasMany $query, $keyword);
    abstract public function filterTags(Relations\HasMany $query, $tags);
    abstract public function isPrivateInternal();
    abstract public function filterCreatedBy(Relations\HasMany $query, $userId, $enabled = false);
    abstract public function sortByUpdated(Relations\HasMany $query, $order = 'asc');
    abstract public function sortByTag(Relations\HasMany $query, $tagGroup, $order = 'asc');
}
