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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model\Project\Issue as ProjectIssue;
use Tinyissue\Model\Project\Issue\Comment as IssueComment;
use Tinyissue\Model\Project\User as ProjectUser;
use Tinyissue\Model\User\Activity as UserActivity;
use URL;

/**
 * Project is model class for projects
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Project extends Model
{
    const STATUS_OPEN = 1;
    const STATUS_ARCHIVED = 0;
    public $timestamps = true;
    protected $table = 'projects';
    protected $fillable = ['name', 'default_assignee'];

    /**
     * Count number of open projects
     *
     * @return int
     */
    public static function countOpenProjects()
    {
        return static::where('status', '=', static ::STATUS_OPEN)->count();
    }

    /**
     * Count number of archived projects
     *
     * @return int
     */
    public static function countArchivedProjects()
    {
        return static::where('status', '=', static ::STATUS_ARCHIVED)->count();
    }

    /**
     * Count number of open issue in the project
     *
     * @return int
     */
    public static function countOpenIssues()
    {
        return Project\Issue::join('projects', 'projects.id', '=', 'projects_issues.project_id')
            ->where('projects.status', '=', static ::STATUS_OPEN)
            ->where('projects_issues.status', '=', ProjectIssue::STATUS_OPEN)
            ->count();
    }

    /**
     * Count number of closed issue in the project
     *
     * @return int
     */
    public static function countClosedIssues()
    {
        return ProjectIssue::join('projects', 'projects.id', '=', 'projects_issues.project_id')
            ->where(function ($query) {
                $query->where('projects.status', '=', static ::STATUS_OPEN);
                $query->where('projects_issues.status', '=', ProjectIssue::STATUS_CLOSED);
            })
            ->orWhere('projects_issues.status', '=', ProjectIssue::STATUS_CLOSED)
            ->count();
    }

    /**
     * Returns collection of active projects
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function activeProjects()
    {
        return static::where('status', '=', static ::STATUS_OPEN)
            ->orderBy('name', 'ASC')
            ->get();
    }

    /**
     * Generate a URL for the active project
     *
     * @param string $url
     *
     * @return string
     */
    public function to($url = '')
    {
        return URL::to('project/' . $this->id . (($url) ? '/' . $url : ''));
    }

    /**
     * For eager loading: include number of open issues
     *
     * @return Relations\HasOne
     */
    public function openIssuesCount()
    {
        return $this->hasOne('Tinyissue\Model\Project\Issue',
            'project_id')->selectRaw('project_id, count(*) as aggregate')
            ->where('status', '=', ProjectIssue::STATUS_OPEN)
            ->groupBy('project_id');
    }

    /**
     * Returns the aggregate value of number of open issues in the project
     *
     * @return int
     */
    public function getOpenIssuesCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('openIssuesCount', $this->relations)) {
            $this->load('openIssuesCount');
        }

        $related = $this->getRelation('openIssuesCount');

        // then return the count directly
        return (isset($related->aggregate)) ? (int)$related->aggregate : 0;
    }

    /**
     * For eager loading: include number of closed issues
     *
     * @return Relations\HasOne
     */
    public function closedIssuesCount()
    {
        return $this->hasOne('Tinyissue\Model\Project\Issue',
            'project_id')->selectRaw('project_id, count(*) as aggregate')
            ->where('status', '=', ProjectIssue::STATUS_CLOSED)
            ->groupBy('project_id');
    }

    /**
     * Returns the aggregate value of number of closed issues in the project
     *
     * @return int
     */
    public function getClosedIssuesCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('closedIssuesCount', $this->relations)) {
            $this->load('closedIssuesCount');
        }

        $related = $this->getRelation('closedIssuesCount');

        // then return the count directly
        return (isset($related->aggregate)) ? (int)$related->aggregate : 0;
    }

    /**
     * Returns issues in the project with user details eager loaded
     *
     * @return Relations\HasMany
     */
    public function issuesByUser()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'project_id')->with('user')->get();
    }

    /**
     * Returns all users that are not assigned in the current project.
     *
     * @return array
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
     * @return Relations\HasMany
     */
    public function users()
    {
        return $this->belongsToMany('\Tinyissue\Model\User', 'projects_users', 'project_id', 'user_id');
    }

    /**
     * Create a new project
     *
     * @param array $input
     *
     * @return $this
     */
    public function createProject(array $input = [])
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
     * Assign a user to a project
     *
     * @param   int $userId
     * @param int   $roleId
     *
     * @return Model
     */
    public function assignUser($userId, $roleId = 0)
    {
        return $this->projectUsers()->save(new ProjectUser([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]));
    }

    /**
     * Project has many project users
     *
     * @return Relations\HasMany
     */
    public function projectUsers()
    {
        return $this->hasMany('Tinyissue\Model\Project\User', 'project_id');
    }

    /**
     * removes a user from a project
     *
     * @param int $userId
     *
     * @return mixed
     */
    public function unassignUser($userId)
    {
        return $this->projectUsers()->where('user_id', '=', $userId)->delete();
    }

    /**
     * For eager loading: count number of issues
     *
     * @return Relations\HasOne
     */
    public function issuesCount()
    {
        return $this->issues()
            ->selectRaw('project_id, count(*) as aggregate')
            ->groupBy('project_id');
    }

    /**
     * Returns all issues related to project.
     *
     * @return Relations\HasMany
     */
    public function issues()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'project_id');
    }

    /**
     * Returns the aggregate value of number of issues in the project
     *
     * @return int
     */
    public function getIssuesCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('issuesCount', $this->relations)) {
            $this->load('issuesCount');
        }

        $related = $this->getRelation('issuesCount');

        // then return the count directly
        return ($related) ? (int)$related->aggregate : 0;
    }

    /**
     * Returns project activities
     *
     * @return Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany('Tinyissue\Model\User\Activity', 'parent_id');
    }

    /**
     * Fetch and filter issues in the project
     *
     * @param int   $status
     * @param array $filter
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listIssues($status = ProjectIssue::STATUS_OPEN, array $filter = [])
    {
        $sortOrder = array_get($filter, 'sortorder', 'desc');

        $query = $this->issues()
            ->with('countComments', 'user', 'updatedBy', 'tags', 'tags.parent')
            ->with([
                'tags' => function ($query) use ($status, $sortOrder) {
                    $status = $status == ProjectIssue::STATUS_OPEN ? Tag::STATUS_OPEN : Tag::STATUS_CLOSED;
                    $query->where('name', '!=', $status);
                    $query->orderBy('name', $sortOrder);
                }
            ])
            ->where('status', '=', $status);

        // Filter by assign to
        if (!empty($filter['assignto']) && $filter['assignto'] > 0) {
            $query->where('assigned_to', '=', (int)$filter['assignto']);
        }

        // Filter by tag
        if (!empty($filter['tags'])) {
            $tagIds = array_map('trim', explode(',', $filter['tags']));
            $query->whereHas('tags', function ($query) use ($tagIds) {
                $query->whereIn('id', $tagIds);
            });
        }

        // Sort
        if (!empty($filter['sortby'])) {
            $sortOrder = array_get($filter, 'sortorder', 'desc');
            if ($filter['sortby'] == 'updated') {
                $query->orderBy('updated_at', $sortOrder);
            } elseif (($tagGroup = substr($filter['sortby'], strlen('tag:'))) > 0) {
                $results = $query->get()->sort(function ($issue1, $issue2) use ($tagGroup, $sortOrder) {
                    $tag1 = $issue1->tags->where('parent.id', $tagGroup, false)->first();
                    $tag2 = $issue2->tags->where('parent.id', $tagGroup, false)->first();
                    $tag1 = $tag1 ? $tag1->name : '';
                    $tag2 = $tag2 ? $tag2->name : '';
                    if ($sortOrder === 'asc') {
                        return strcmp($tag1, $tag2);
                    }

                    return strcmp($tag2, $tag1);
                });

                return $results;
            }
        }

        return $query->get();
    }

    /**
     * Fetch issues assigned to a user
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
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
     *  Delete a project
     *
     * @return void
     * @throws \Exception
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

    /**
     * Get total issues total quote time
     *
     * @return int
     */
    public function getTotalQuote()
    {
        $total = 0;
        foreach ($this->issues as $issue) {
            $total += $issue->time_quote;
        }

        return $total;
    }

    /**
     * Returns notes in the project
     *
     * @return Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany('Tinyissue\Model\Project\Note', 'project_id');
    }
}
