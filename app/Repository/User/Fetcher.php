<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\User;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;
use Tinyissue\Repository\Repository;

class Fetcher extends Repository
{
    /**
     * @var User
     */
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Returns projects the user member of.
     *
     * @return Eloquent\Collection
     */
    public function getProjects()
    {
        return $this->model->projects()->get();
    }

    /**
     * Get collection of projects with user details.
     *
     * @return Eloquent\Collection
     */
    public function getProjectsWithSettings()
    {
        return $this->model->projects()->with('projectUsers')->get();
    }

    /**
     * Returns public users.
     *
     * @return Eloquent\Collection
     */
    public function getActiveUsers()
    {
        return $this->model->with('role')->notPrivate()->orderBy('firstname')->get();
    }

    /**
     * Returns user projects with activities details eager loaded.
     *
     * @return Eloquent\Collection
     */
    public function getProjectsWithRecentActivities()
    {
        return $this->model->projects()->with('recentActivities')->get();
    }

    /**
     * Returns projects with issues details eager loaded.
     *
     * @return Eloquent\Collection
     */
    public function getProjectsWithRecentIssues()
    {
        return $this->model
            ->projects()
            ->active()
            ->with('issues.user', 'issues.countComments')
            ->with([
                'issues' => function (Relations\Relation $query) {
                    $query->open()->with('updatedBy');
                    $query->assignedOrCreated($this->model);
                },
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get collection of issues group by a list of tags.
     *
     * @param Eloquent\Collection $tagIds
     * @param null|int            $projectId
     *
     * @return Eloquent\Collection
     */
    public function getIssuesGroupByTags(Eloquent\Collection $tagIds, $projectId = null)
    {
        $tagIds = $tagIds->pluck('id')->all();

        $assignedOrCreate = $this->model->isUser() ? 'issuesCreatedBy' : 'issues';
        $issues           = $this->model->$assignedOrCreate()
            ->with('user', 'tags')
            ->open()
            ->forProject($projectId)
            ->join('projects_issues_tags', 'issue_id', '=', 'id')
            ->whereIn('projects_issues_tags.tag_id', $tagIds)
            ->orderBy('id')
            ->get()
            ->groupBy(function (Project\Issue $issue) {
                return $issue->getStatusTag()->name;
            });

        return $issues;
    }

    /**
     * Returns all projects with open issue count.
     *
     * @param int $status
     *
     * @return Eloquent\Collection
     */
    public function getProjectsWithOpenIssuesCount($status = Project::STATUS_OPEN)
    {
        if ($this->model->isManager() || $this->model->isAdmin()) {
            return app()->make(Project::class)->getProjectsWithOpenIssuesCount($status, Project::PRIVATE_ALL);
        }

        return $this->model->projects()->status($status)->with('openIssuesCount')->get();
    }
}
