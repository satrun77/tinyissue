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

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model\Project;
use Tinyissue\Model\Role;

/**
 * QueryTrait is trait class containing the database queries methods for the User model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int                 $id
 * @property Eloquent\Collection $permission
 *
 * @method   Relations\HasMany projects($status = Project::STATUS_OPEN)
 * @method   Relations\HasMany permissions()
 */
trait QueryTrait
{
    /**
     * Returns public users.
     *
     * @return Eloquent\Collection
     */
    public function activeUsers()
    {
        return $this->with('role')
            ->where('private', '=', false)
            ->orderBy('firstname', 'ASC')->get();
    }

    /**
     * Returns user projects with activities details eager loaded.
     *
     * @param int $status
     *
     * @return Relations\HasMany
     */
    public function projectsWidthActivities($status = Project::STATUS_OPEN)
    {
        return $this->projects($status)
            ->with([
                'activities' => function (Relations\Relation $query) {
                    $query->with('activity', 'issue', 'user', 'assignTo', 'comment', 'note');
                    $query->orderBy('users_activity.created_at', 'DESC');

                    // For logged users with role User, show issues that are created by them in internal projects
                    // of issue create by any for other project statuses
                    if (auth()->user()->isUser()) {
                        $query->join('projects_issues', 'projects_issues.id', '=', 'item_id');
                        $query->join('projects', 'projects.id', '=', 'parent_id');
                        $query->where(function (Eloquent\Builder $query) {
                            $query->where(function (Eloquent\Builder $query) {
                                $query->where('created_by', '=', auth()->user()->id);
                                $query->where('private', '=', Project::INTERNAL_YES);
                            });
                            $query->orWhere('private', '<>', Project::INTERNAL_YES);
                        });
                    }
                },
            ]);
    }

    /**
     * Returns projects with issues details eager loaded.
     *
     * @param int $status
     *
     * @return Relations\HasMany
     */
    public function projectsWidthIssues($status = Project::STATUS_OPEN)
    {
        $assignedOrCreate = $this->isUser() ? 'created_by' : 'assigned_to';

        return $this
            ->projects($status)
            ->with([
                'issues' => function (Relations\Relation $query) use ($status, $assignedOrCreate) {
                    $query->with('updatedBy');
                    $query->where($assignedOrCreate, '=', $this->id);
                    if ($status === Project::STATUS_OPEN) {
                        $query->where('status', '=', Project\Issue::STATUS_OPEN);
                    }
                },
                'issues.user'          => function () {},
                'issues.countComments' => function () {},
            ]);
    }

    /**
     * Returns collection of issues grouped by tags.
     *
     * @param $tagIds
     * @param int $projectId
     *
     * @return mixed
     */
    public function issuesGroupByTags($tagIds, $projectId = null)
    {
        $assignedOrCreate = $this->isUser() ? 'issuesCreatedBy' : 'issues';
        $issues           = $this->$assignedOrCreate()
            ->with('user', 'tags')
            ->where('status', '=', Project\Issue::STATUS_OPEN)
            ->whereIn('projects_issues_tags.tag_id', $tagIds)
            ->join('projects_issues_tags', 'issue_id', '=', 'id')
            ->orderBy('id');

        // Limit by project id
        if ($projectId > 0) {
            $issues->where('project_id', '=', $projectId);
        }

        $issues = $issues->get()->groupBy(function (Project\Issue $issue) {
            return $issue->getStatusTag()->name;
        });

        return $issues;
    }

    /**
     * Load user permissions.
     *
     * @return Eloquent\Collection
     */
    protected function loadPermissions()
    {
        if (null === $this->permission) {
            $this->permission = $this->permissions()->with('permission')->get();
        }

        return $this->permission;
    }
}
