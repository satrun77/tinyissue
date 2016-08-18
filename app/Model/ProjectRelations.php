<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Relations;

/**
 * ProjectRelations is trait class containing the relationship methods for the Project model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait ProjectRelations
{
    /**
     * For eager loading: include number of closed issues.
     *
     * @param User $limitByUser
     *
     * @return Project\Issue
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
     * @return Project\Issue
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
     * @return Project\Issue
     */
    protected function issuesCountByStatus($status, User $limitByUser = null)
    {
        $query = $this
            ->hasOne(Project\Issue::class, 'project_id')
            ->selectRaw('project_id, count(*) as aggregate')
            ->where('status', '=', $status)
            ->groupBy('project_id');

        if ($limitByUser && $limitByUser->isUser() && $this->isPrivateInternal()) {
            $query->where('created_by', '=', $limitByUser->id);
        }

        return $query;
    }

    /**
     * Returns all issues related to project.
     *
     * @return Project\Issue
     */
    public function issues()
    {
        return $this->hasMany(Project\Issue::class, 'project_id');
    }

    /**
     * Relation to opened issues only.
     *
     * @return Project\Issue
     */
    public function openIssues()
    {
        return $this->issues()->open();
    }

    /**
     * Relation to closed issues only.
     *
     * @return Project\Issue
     */
    public function closedIssues()
    {
        return $this->issues()->closed();
    }

    /**
     * Relation to opened issues with updated by user loaded.
     *
     * @return Project\Issue
     */
    public function openIssuesWithUpdater()
    {
        return $this->openIssues()->with('updatedBy');
    }

    /**
     * Returns issues in the project with user details eager loaded.
     *
     * @return Project\Issue
     */
    public function issuesByUser()
    {
        return $this->hasMany(Project\Issue::class, 'project_id')->with('user')->get();
    }

    /**
     * Returns all users assigned in the current project.
     *
     * @return User
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'projects_users', 'project_id', 'user_id');
    }

    /**
     * Return a user that is member of a project.
     *
     * @param int $userId
     *
     * @return User
     */
    public function user($userId)
    {
        return $this->users()->where('user_id', '=', (int) $userId);
    }

    /**
     * Project has many project users.
     *
     * @return Project\User
     */
    public function projectUsers()
    {
        return $this->hasMany(Project\User::class, 'project_id');
    }

    /**
     * Returns project activities.
     *
     * @return User\Activity
     */
    public function activities()
    {
        return $this->hasMany(User\Activity::class, 'parent_id');
    }

    /**
     * Returns project recent activities.
     *
     * @return User\Activity
     */
    public function recentActivities()
    {
        return $this->activities()->loadRelatedDetails()->limitResultForUserRole();
    }

    /**
     * Returns notes in the project.
     *
     * @return Project\Note
     */
    public function notes()
    {
        return $this->hasMany(Project\Note::class, 'project_id');
    }

    /**
     * Project have many kanban tags.
     *
     * @return Tag
     */
    public function kanbanTags()
    {
        return $this->belongsToMany(Tag::class, 'projects_kanban_tags', 'project_id', 'tag_id')
            ->orderBy('position');
    }

    abstract public function hasMany($related, $foreignKey = null, $localKey = null);

    abstract public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null);

    abstract public function hasOne($related, $foreignKey = null, $localKey = null);

    abstract public function isPrivateInternal();
}
