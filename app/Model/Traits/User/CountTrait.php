<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\User;

use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * CountTrait is trait class containing the methods for counting database records for the User model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method bool              permission($key)
 * @method Relations\HasMany projects($status = Project::STATUS_OPEN)
 * @method Relations\HasMany issues()
 */
trait CountTrait
{
    /**
     * @param int $deleted
     *
     * @return int
     */
    public static function countUsers($deleted = User::NOT_DELETED_USERS)
    {
        return User::where('deleted', '=', $deleted)->count();
    }

    /**
     * Count number of assigned issues in a project.
     *
     * @param int $projectId
     *
     * @return int
     */
    public function assignedIssuesCount($projectId = 0)
    {
        $issues = $this->issues();

        if (0 < $projectId) {
            $issues = $issues->where('project_id', '=', $projectId);
        }
        $issues->where('status', '=', Project\Issue::STATUS_OPEN);

        return $issues->count();
    }

    /**
     * Returns all projects with open issue count.
     *
     * @param int $status
     *
     * @return $this
     */
    public function projectsWithCountOpenIssues($status = Project::STATUS_OPEN)
    {
        if ($this->permission('project-all')) {
            $project = new Project();

            return $project->projectsWithOpenIssuesCount($status, Project::PRIVATE_ALL);
        }

        return $this->projects($status)->with('openIssuesCount');
    }
}
