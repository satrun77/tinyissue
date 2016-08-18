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

use Tinyissue\Model\Project;
use Tinyissue\Model\User;
use Tinyissue\Repository\Repository;

class Counter extends Repository
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
     * Count number of open issue in the project.
     *
     * @param User|null $limitByUser
     *
     * @return int
     */
    public function countOpenIssues(User $limitByUser = null)
    {
        return $this->model->openIssues()->limitByCreatedForInternalProject($this->model, $limitByUser)->count();
    }

    /**
     * Count number of closed issue in the project.
     *
     * @param User|null $limitByUser
     *
     * @return int
     */
    public function countClosedIssues(User $limitByUser = null)
    {
        return $this->model
            ->closedIssues()
            ->limitByCreatedForInternalProject($this->model, $limitByUser)
            ->count();
    }

    /**
     * Count notes in project.
     *
     * @return int
     */
    public function countNotes()
    {
        return $this->model->notes()->count();
    }

    /**
     * Count number of assigned issues in a project.
     *
     * @param User $forUser
     *
     * @return int
     */
    public function countAssignedIssues(User $forUser)
    {
        return $this->model->openIssues()->assignedTo($forUser)->count();
    }

    /**
     * Count number of created issues in a project.
     *
     * @param User $forUser
     *
     * @return int
     */
    public function countCreatedIssues(User $forUser)
    {
        return $this->model->openIssues()->createdBy($forUser)->count();
    }

    /**
     * Count number of private projects.
     *
     * @return int
     */
    public function countPrivateProjects()
    {
        return $this->model->notPublic()->count();
    }

    /**
     * Count number of open projects.
     *
     * @return int
     */
    public function countActiveProjects()
    {
        return $this->model->active()->count();
    }

    /**
     * Count number of archived projects.
     *
     * @return int
     */
    public function countArchivedProjects()
    {
        return $this->model->archived()->count();
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
        if ($status === Project::STATUS_OPEN) {
            return $this->countActiveProjects();
        }

        return $this->countArchivedProjects();
    }
}
