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

use Tinyissue\Model\Project;
use Tinyissue\Model\User;
use Tinyissue\Repository\Repository;

class Counter extends Repository
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
     * Count active users.
     *
     * @return int
     */
    public function countNotDeleted()
    {
        return $this->model->active()->count();
    }

    /**
     * Count deleted users.
     *
     * @return int
     */
    public function countDeleted()
    {
        return $this->model->removed()->count();
    }

    /**
     * Count number of created issues in a project.
     *
     * @param int $projectId
     *
     * @return int
     */
    public function createdIssuesCount($projectId = 0)
    {
        $issues = $this->model->issuesCreatedBy();

        if (0 < $projectId) {
            $issues = $issues->where('project_id', '=', $projectId);
        }
        $issues->where('status', '=', Project\Issue::STATUS_OPEN);

        return $issues->count();
    }

    /**
     * Count number of archived projects.
     *
     * @param $status
     *
     * @return int
     */
    public function countProjectsByStatus($status)
    {
        if ($this->model->isManager() || $this->model->isAdmin()) {
            return app()->make(Project::class)->countProjectsByStatus($status);
        }

        return $this->model->projects()->status($status)->count();
    }
}
