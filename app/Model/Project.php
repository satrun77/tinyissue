<?php

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Project\Issue as ProjectIssue;
use Tinyissue\Model\Project\Issue\Comment as IssueComment;
use Tinyissue\Model\Project\User as ProjectUser;
use Tinyissue\Model\User\Activity as UserActivity;
use URL;

class Project extends Model
{
    const STATUS_OPEN = 1;
    const STATUS_ARCHIVED = 0;
    public $timestamps = true;
    protected $table = 'projects';
    protected $fillable = ['name', 'default_assignee'];

    public static function countOpenProjects()
    {
        return static::where('status', '=', static ::STATUS_OPEN)->count();
    }

    public static function countArchivedProjects()
    {
        return static::where('status', '=', static ::STATUS_ARCHIVED)->count();
    }

    public static function countOpenIssues()
    {
        return Project\Issue::join('projects', 'projects.id', '=', 'projects_issues.project_id')
            ->where('projects.status', '=', static ::STATUS_OPEN)
            ->where('projects_issues.status', '=', Project\Issue::STATUS_OPEN)
            ->count();
    }

    public static function countClosedIssues()
    {
        return Project\Issue::join('projects', 'projects.id', '=', 'projects_issues.project_id')
            ->where(function ($query) {
                $query->where('projects.status', '=', static ::STATUS_OPEN);
                $query->where('projects_issues.status', '=', Project\Issue::STATUS_CLOSED);
            })
            ->orWhere('projects_issues.status', '=', Project\Issue::STATUS_CLOSED)
            ->count();
    }

    public static function activeProjects()
    {
        return static::where('status', '=', static ::STATUS_OPEN)
            ->orderBy('name', 'ASC')
            ->get();
    }

    /**
     * Generate a URL for the active project.
     *
     * @param string $url
     *
     * @return string
     */
    public function to($url = '')
    {
        return URL::to('project/'.$this->id.(($url) ? '/'.$url : ''));
    }

    public function openIssuesCount()
    {
        return $this->hasOne('Tinyissue\Model\Project\Issue',
            'project_id')->selectRaw('project_id, count(*) as aggregate')
            ->where('status', '=', \Tinyissue\Model\Project\Issue::STATUS_OPEN)
            ->groupBy('project_id');
    }

    public function getOpenIssuesCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('openIssuesCount', $this->relations)) {
            $this->load('openIssuesCount');
        }

        $related = $this->getRelation('openIssuesCount');

        // then return the count directly
        return (isset($related->aggregate)) ? (int) $related->aggregate : 0;
    }

    public function closedIssuesCount()
    {
        return $this->hasOne('Tinyissue\Model\Project\Issue',
            'project_id')->selectRaw('project_id, count(*) as aggregate')
            ->where('status', '=', \Tinyissue\Model\Project\Issue::STATUS_CLOSED)
            ->groupBy('project_id');
    }

    public function getClosedIssuesCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('closedIssuesCount', $this->relations)) {
            $this->load('closedIssuesCount');
        }

        $related = $this->getRelation('closedIssuesCount');

        // then return the count directly
        return (isset($related->aggregate)) ? (int) $related->aggregate : 0;
    }

    public function issuesByUser()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'project_id')->with('user')->get();
    }

    /**
     * Returns all users that are not assigned in the current project.
     *
     * @return type
     */
    public function usersNotIn()
    {
        if ($this->id > 0) {
            $userIds = $this->users()->lists('user_id');
            $users = User::where('deleted', '=', User::NOT_DELETED_USERS)->whereNotIn('id', $userIds)->get();
        } else {
            $users = User::where('deleted', '=', User::NOT_DELETED_USERS)->get();
        }

        return $users->lists('fullname', 'id');
    }

    /**
     * Returns all users assigned in the current project.
     *
     * @return type
     */
    public function users()
    {
        return $this->belongsToMany('\Tinyissue\Model\User', 'projects_users', 'project_id', 'user_id');
    }

    /**
     * Create a new project.
     *
     * @param array $input
     *
     * @return array
     */
    public function createProject(array $input = array())
    {
        $this->fill($input)->save();

        /* Assign selected users to the project */
        if (isset($input['user']) && count($input['user']) > 0) {
            foreach ($input['user'] as $id) {
                $this->assignUser($id);
            }
        }

        return $this;
    }

    /**
     * Assign a user to a project.
     *
     * @param int $user_id
     * @param int $role_id
     */
    public function assignUser($userId, $roleId = 0)
    {
        return $this->projectUsers()->save(new ProjectUser([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]));
    }

    public function projectUsers()
    {
        return $this->hasMany('Tinyissue\Model\Project\User', 'project_id');
    }

    /**
     * Removes a user from a project.
     *
     * @param int $user_id
     * @param int $project_id
     */
    public function unassignUser($userId)
    {
        return $this->projectUsers()->where('user_id', '=', $userId)->delete();
    }

    public function issuesCount()
    {
        return $this->issues()
            ->selectRaw('project_id, count(*) as aggregate')
            ->groupBy('project_id');
    }

    /**
     * Returns all issues related to project.
     *
     * @return mixed
     */
    public function issues()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'project_id');
    }

    public function getIssuesCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('issuesCount', $this->relations)) {
            $this->load('issuesCount');
        }

        $related = $this->getRelation('issuesCount');

        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }

    public function activities()
    {
        return $this->hasMany('Tinyissue\Model\User\Activity', 'parent_id');
    }

    public function listIssues($status = \Tinyissue\Model\Project\Issue::STATUS_OPEN)
    {
        return $this->issues()
            ->with('countComments', 'user', 'updatedBy')
            ->where('status', '=', $status)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    public function listAssignedIssues($userId)
    {
        return $this->issues()
            ->with('countComments', 'user', 'updatedBy')
            ->where('status', '=', \Tinyissue\Model\Project\Issue::STATUS_OPEN)
            ->where('assigned_to', '=', $userId)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * Delete a project and it's children.
     *
     * @param Project $project
     */
    public function delete()
    {
        $id = $this->id;
        parent::delete();

        /* Delete all children from the project */
        ProjectIssue::where('project_id', '=', $id)->delete();
        IssueComment::where('project_id', '=', $id)->delete();
        ProjectUser::where('project_id', '=', $id)->delete();
        UserActivity::where('parent_id', '=', $id)->delete();
    }
}
