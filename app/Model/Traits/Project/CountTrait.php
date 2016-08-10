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
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * CountTrait is trait class containing the methods for counting database records for the Project model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait CountTrait
{
    /**
     * Count number of private projects.
     *
     * @return int
     */
    public function countPrivateProjects()
    {
        return $this
            ->where('private', '=', Project::PRIVATE_YES)
            ->orWhere('private', '=', Project::INTERNAL_YES)->count();
    }

    /**
     * Count number of open projects.
     *
     * @return int
     */
    public function countOpenProjects()
    {
        return $this->where('status', '=', Project::STATUS_OPEN)->count();
    }

    /**
     * Count number of archived projects.
     *
     * @return int
     */
    public function countArchivedProjects()
    {
        return $this->where('status', '=', Project::STATUS_ARCHIVED)->count();
    }

    /**
     * Count number of open issue in the project.
     *
     * @return int
     */
    public function countOpenIssues()
    {
        return Project\Issue::join('projects', 'projects.id', '=', 'projects_issues.project_id')
            ->where('projects.status', '=', Project::STATUS_OPEN)
            ->where('projects_issues.status', '=', Project\Issue::STATUS_OPEN)
            ->count();
    }

    /**
     * Count number of closed issue in the project.
     *
     * @return int
     */
    public function countClosedIssues()
    {
        return Project\Issue::join('projects', 'projects.id', '=', 'projects_issues.project_id')
            ->where(function (Eloquent\Builder $query) {
                $query->where('projects.status', '=', Project::STATUS_OPEN);
                $query->where('projects_issues.status', '=', Project\Issue::STATUS_CLOSED);
            })
            ->orWhere('projects_issues.status', '=', Project\Issue::STATUS_CLOSED)
            ->count();
    }

    /**
     * For eager loading: count number of issues.
     *
     * @return Eloquent\Relations\HasOne
     */
    public function issuesCount()
    {
        return $this->issues()
            ->selectRaw('project_id, count(*) as aggregate')
            ->groupBy('project_id');
    }

    /**
     * For eager loading: include number of closed issues.
     *
     * @param User $limitByUser
     *
     * @return Eloquent\Relations\HasOne
     */
    public function closedIssuesCount(User $limitByUser = null)
    {
        return $this->issuesCountByStatus(Project\Issue::STATUS_CLOSED, $limitByUser);
    }

    /**
     * For eager loading: include number of open issues.
     *
     * @param User $limitByUser
     *
     * @return Eloquent\Relations\HasOne
     */
    public function openIssuesCount(User $limitByUser = null)
    {
        return $this->issuesCountByStatus(Project\Issue::STATUS_OPEN, $limitByUser);
    }

    /**
     * For eager loading: include number of issues by open/closed status.
     *
     * @param int       $status
     * @param User|null $limitByUser
     *
     * @return Eloquent\Relations\HasOne
     */
    protected function issuesCountByStatus($status, User $limitByUser = null)
    {
        $query = $this
            ->hasOne(
                'Tinyissue\Model\Project\Issue',
                'project_id'
            )
            ->selectRaw('project_id, count(*) as aggregate')
            ->where('status', '=', $status)
            ->groupBy('project_id');

        if ($limitByUser && $limitByUser->isUser() && $this->isPrivateInternal()) {
            $query->where('created_by', '=', $limitByUser->id);
        }

        return $query;
    }

    /**
     * Return projects with count of open & closed issues.
     *
     * @param array $projectIds
     *
     * @return Eloquent\Collection
     */
    public function projectsWithCountIssues(array $projectIds)
    {
        return $this
            ->with('openIssuesCount', 'closedIssuesCount')
            ->whereIn('id', $projectIds)
            ->get();
    }

    /**
     * Returns projects with open issue count.
     *
     * @param int $status
     * @param int $private
     *
     * @return mixed
     */
    public function projectsWithOpenIssuesCount($status = Project::STATUS_OPEN, $private = Project::PRIVATE_YES)
    {
        $query = $this->with('openIssuesCount')
            ->where('status', '=', $status);

        if ($private !== Project::PRIVATE_ALL) {
            $query->where('private', '=', $private);
        }

        return $query;
    }
}
