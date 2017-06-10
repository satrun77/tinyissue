<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;
use Tinyissue\Repository\Repository;

class Fetcher extends Repository
{
    /**
     * @var Project
     */
    protected $model;

    public function __construct(Project $model)
    {
        $this->model = $model;
    }

    /**
     * Get project by its key.
     *
     * @param string $key
     *
     * @return Project
     */
    public function getByKey($key)
    {
        return $this->model->where('key', '=', $key)->limit(1)->first();
    }

    /**
     * Returns collection of active projects.
     *
     * @return Collection
     */
    public function getActiveProjects()
    {
        return $this->model->active()->orderBy('name', 'ASC')->get();
    }

    /**
     * Get collection of notes in project.
     *
     * @return Collection
     */
    public function getNotes()
    {
        return $this->model->notes()->with('createdBy')->get();
    }

    /**
     * Returns collection of public projects.
     *
     * @return Collection
     */
    public function getPublicProjects()
    {
        return $this->model->public()->orderBy('name', 'ASC')->get();
    }

    /**
     * Returns all users that are not assigned in the current project.
     *
     * @return array
     */
    public function getNotMembers()
    {
        return (new User())->active()->notMemberOfProject($this->model)->get();
    }

    /**
     * Fetch and filter issues in the project.
     *
     * @param int   $status
     * @param array $filter
     *
     * @return Collection
     */
    public function getIssues($status = Project\Issue::STATUS_OPEN, array $filter = [])
    {
        $sortOrder = array_get($filter, 'sort.sortorder', 'desc');
        $sortBy    = array_get($filter, 'sort.sortby', null);

        $query = $this->model->issues()
            ->with('countComments', 'user', 'updatedBy', 'tags', 'tags.parent')
            ->with([
                'tags' => function (Relation $query) use ($sortOrder) {
                    $query->orderBy('name', $sortOrder);
                },
            ])
            ->status($status)
            ->assignedTo(array_get($filter, 'assignto'))
            ->searchContent(array_get($filter, 'keyword'))
            ->createdBy(array_get($filter, 'created_by'))
            ->whereTags(array_get($filter, 'tag_status'), array_get($filter, 'tag_type'));

        // Sort
        if ($sortBy === 'updated') {
            $this->sortByUpdated($query, $sortOrder);
        } elseif (($tagGroup = substr($sortBy, strlen('tag:'))) > 0) {
            return $this->sortByTag($query, $tagGroup, $sortOrder);
        }

        return $query->get();
    }

    /**
     * Fetch and filter issues in the project.
     *
     * @param int   $status
     * @param array $filter
     *
     * @return Collection
     */
    public function getIssuesForLoggedUser($status = Project\Issue::STATUS_OPEN, array $filter = [])
    {
        if ($this->model->isPrivateInternal() && $this->getLoggedUser()->isUser()) {
            $filter['created_by'] = $this->getLoggedUser()->id;
        }

        return $this->getIssues($status, $filter);
    }

    /**
     * Fetch issues assigned to a user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getAssignedOrCreatedIssues(User $user)
    {
        if ($user->isUser()) {
            return $this->getCreatedIssues($user);
        }

        return $this->getAssignedIssues($user);
    }

    /**
     * Get collection of issue created by user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getCreatedIssues(User $user)
    {
        return $this->model->openIssues()
            ->with('countComments', 'user', 'updatedBy')
            ->createdBy($user)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * Get collection of issue assigned to user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getAssignedIssues(User $user)
    {
        return $this->model->openIssues()
            ->with('countComments', 'user', 'updatedBy')
            ->assignedTo($user)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * Get collection of recent activities in the project.
     *
     * @param User|null $user
     * @param int       $limit
     *
     * @return Collection
     */
    public function getRecentActivities(User $user = null, $limit = 10)
    {
        $activities = $this->model->activities()
            ->with('activity', 'issue', 'user', 'assignTo', 'comment', 'note')
            ->orderBy('users_activity.created_at', 'DESC')
            ->take($limit);

        // Internal project and logged user can see created only
        if ($this->model->isPrivateInternal() && $user instanceof User && $user->isUser()) {
            $activities->join('projects_issues', 'projects_issues.id', '=', 'item_id');
            $activities->where('created_by', '=', $user->id);
        }

        return $activities->get();
    }

    /**
     * Returns projects with issues details eager loaded.
     *
     * @return Collection
     */
    public function getPublicProjectsWithRecentIssues()
    {
        return $this->model
            ->active()
            ->public()
            ->with('openIssuesWithUpdater', 'issues.user', 'issues.countComments')
            ->orderBy('name')
            ->get();
    }

    /**
     * Returns collection of tags for Kanban view.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getKanbanTagsForUser(User $user)
    {
        return $this->model->kanbanTags()->accessibleToUser($user)->get();
    }

    /**
     * Get collection of tags for kanban view.
     *
     * @return Collection
     */
    public function getKanbanTags()
    {
        return $this->model->kanbanTags()->get();
    }

    /**
     * Returns users assigned to the project that can fix issues (with edit permission).
     *
     * @return Collection
     */
    public function getUsersCanFixIssue()
    {
        return $this->model->users()->developerOrHigher()->active()->get();
    }

    /**
     * Get collection of users in project.
     *
     * @return Collection
     */
    public function getUsers()
    {
        return $this->model->users()->active()->get();
    }

    /**
     * Sort by updated_at column.
     *
     * @param Builder $query
     * @param string  $order
     *
     * @return void
     */
    protected function sortByUpdated(Builder $query, $order = 'asc')
    {
        $query->orderBy('updated_at', $order);
    }

    /**
     * Sort by issues tag group
     * Note: this sort will return the collection.
     *
     * @param Builder $query
     * @param string  $tagGroup
     * @param string  $order
     *
     * @return Collection
     */
    protected function sortByTag(Builder $query, $tagGroup, $order = 'asc')
    {
        // If tag group is string prefixed with tag:
        if (!is_numeric($tagGroup)) {
            $tagGroup = substr($tagGroup, strlen('tag:'));
        }

        $results = $query->get()
            ->sort(function (Project\Issue $issue1, Project\Issue $issue2) use ($tagGroup, $order) {
                $tag1 = $issue1->tags->where('parent.id', $tagGroup)->first();
                $tag2 = $issue2->tags->where('parent.id', $tagGroup)->first();
                $tag1 = $tag1 ? $tag1->name : '';
                $tag2 = $tag2 ? $tag2->name : '';

                if ($order === 'asc') {
                    return strcmp($tag1, $tag2);
                }

                return strcmp($tag2, $tag1);
            });

        return $results;
    }

    /**
     * Returns projects with open issue count.
     *
     * @param int $status
     * @param int $private
     *
     * @return Collection
     */
    public function getProjectsWithOpenIssuesCount($status = Project::STATUS_OPEN, $private = Project::PRIVATE_YES)
    {
        $query = $this->model->with('openIssuesCount')->status($status);

        if ($private !== Project::PRIVATE_ALL) {
            $query->where('private', '=', $private);
        }

        return $query->get();
    }

    /**
     * Return projects with count of open & closed issues.
     *
     * @param array $projectIds
     *
     * @return Collection
     */
    public function getProjectsWithCountIssues(array $projectIds)
    {
        return $this->model
            ->with('openIssuesCount', 'closedIssuesCount')
            ->whereIn('id', $projectIds)
            ->get();
    }

    /**
     * Get collection of issues group by a list of tags.
     *
     * @param Collection $tagIds
     * @return Collection
     */
    public function getIssuesGroupByTags(Collection $tagIds)
    {
        $tagIds = $tagIds->pluck('id')->all();

        $issues = $this->model->issues()
            ->with('user', 'tags')
            ->open()
            ->forProject($this->model->id)
            ->join('projects_issues_tags', 'issue_id', '=', 'id')
            ->whereIn('projects_issues_tags.tag_id', $tagIds)
            ->orderBy('id')
            ->get()
            ->groupBy(function (Project\Issue $issue) {
                return $issue->getStatusTag()->name;
            });

        return $issues;
    }
}
