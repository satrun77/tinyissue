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

use Illuminate\Database\Eloquent\Collection;
use URL;

/**
 * Project is model class for projects.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property int $default_assignee
 * @property int $private
 * @property int $openIssuesCount
 * @property int $closedIssuesCount
 * @property Collection $issues
 * @property Collection $issuesByUser
 * @property Collection $users
 * @property Collection $projectUsers
 * @property Collection $activities
 * @property Collection $notes
 * @property Collection $kanbanTags
 *
 * @method  Collection getActiveProjects()
 * @method  Collection getNotes()
 * @method  Collection getPublicProjects()
 * @method  Collection getNotMembers()
 * @method  Collection getIssues($status = Project\Issue::STATUS_OPEN, array $filter = [])
 * @method  Collection getIssuesForLoggedUser($status = Project\Issue::STATUS_OPEN, array $filter = [])
 * @method  Collection getAssignedOrCreatedIssues(User $user)
 * @method  Collection getCreatedIssues(User $user)
 * @method  Collection getAssignedIssues(User $user)
 * @method  Collection getRecentActivities(User $user = null, $limit = 10)
 * @method  Collection getPublicProjectsWithRecentIssues()
 * @method  Collection getKanbanTagsForUser(User $user)
 * @method  Collection getKanbanTags()
 * @method  Collection getUsersCanFixIssue()
 * @method  Collection getUsers()
 * @method  Collection getProjectsWithOpenIssuesCount($status = Project::STATUS_OPEN, $private = Project::PRIVATE_YES)
 * @method  Collection getProjectsWithCountIssues(array $projectIds)
 * @method  int countOpenIssues(User $forUser)
 * @method  int countClosedIssues(User $forUser)
 * @method  int countNotes()
 * @method  int countAssignedIssues(User $forUser)
 * @method  int countCreatedIssues(User $forUser)
 * @method  int countPrivateProjects()
 * @method  int countActiveProjects()
 * @method  int countArchivedProjects()
 * @method  int countProjectsByStatus($status)
 * @method  $this status($status = Project::STATUS_OPEN)
 * @method  $this active()
 * @method  $this archived()
 * @method  $this public()
 * @method  $this notPublic()
 */
class Project extends ModelAbstract
{
    use ProjectRelations,
        ProjectScopes;

    /**
     * Project private & user role can see their own issues only.
     *
     * @var int
     */
    const INTERNAL_YES = 2;

    /**
     * Project not public to view and create issue.
     *
     * @var int
     */
    const PRIVATE_YES = 1;

    /**
     * Project public to view and create issue.
     *
     * @var int
     */
    const PRIVATE_NO = 0;

    /**
     * All projects.
     *
     * @var int
     */
    const PRIVATE_ALL = -1;

    /**
     * Project status Open.
     *
     * @var int
     */
    const STATUS_OPEN = 1;

    /**
     * Project status Archived.
     *
     * @var int
     */
    const STATUS_ARCHIVED = 0;

    /**
     * Timestamp enabled.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'projects';

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    protected $fillable = ['name', 'default_assignee', 'status', 'private'];

    /**
     * List of HTML classes for each status.
     *
     * @var array
     */
    protected $attrClassNames = [
        self::PRIVATE_NO   => 'note',
        self::PRIVATE_YES  => 'info',
        self::INTERNAL_YES => 'primary',
    ];

    /**
     * List of statuses names.
     *
     * @var array
     */
    protected $statusesNames = [
        self::PRIVATE_NO   => 'public',
        self::PRIVATE_YES  => 'private',
        self::INTERNAL_YES => 'internal',
    ];

    /**
     * @param User|null $user
     *
     * @return \Tinyissue\Repository\Project\Updater
     */
    public function updater(User $user = null)
    {
        return parent::updater($user);
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
        return URL::to('project/' . $this->id . (($url) ? '/' . $url : ''));
    }

    /**
     * Returns the aggregate value of number of open issues in the project.
     *
     * @return int
     */
    public function getOpenIssuesCountAttribute()
    {
        return $this->getCountAttribute('openIssuesCount');
    }

    /**
     * Returns the aggregate value of number of closed issues in the project.
     *
     * @return int
     */
    public function getClosedIssuesCountAttribute()
    {
        return $this->getCountAttribute('closedIssuesCount');
    }

    /**
     * Set default assignee attribute.
     *
     * @param int $value
     *
     * @return $this
     */
    public function setDefaultAssigneeAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['default_assignee'] = (int) $value;
        }

        return $this;
    }

    /**
     * Returns the aggregate value of number of issues in the project.
     *
     * @return int
     */
    public function getIssuesCountAttribute()
    {
        return $this->getCountAttribute('issuesCount');
    }

    /**
     * Get total issues total quote time.
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
     * Calculate the progress (open & closed issues).
     *
     * @return float|int
     */
    public function getProgress()
    {
        $total    = $this->openIssuesCount + $this->closedIssuesCount;
        $progress = 100;
        if ($total > 0) {
            $progress = (float) ($this->closedIssuesCount / $total) * 100;
        }
        $progressInt = (int) $progress;
        if ($progressInt > 0) {
            $progress = number_format($progress, 2);
            $fraction = $progress - $progressInt;
            if ($fraction === 0.0) {
                $progress = $progressInt;
            }
        }

        return $progress;
    }

    /**
     * Whether or not a user is member of the project.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isMember($userId)
    {
        return $this->user($userId)->count() > 0;
    }

    /**
     * Whether or not the project is private.
     *
     * @return bool
     */
    public function isPrivate()
    {
        return (int) $this->private === self::PRIVATE_YES;
    }

    /**
     * Whether or not the project is public.
     *
     * @return bool
     */
    public function isPublic()
    {
        return (int) $this->private === self::PRIVATE_NO;
    }

    /**
     * Whether or not the project is private internal.
     *
     * @return bool
     */
    public function isPrivateInternal()
    {
        return (int) $this->private === self::INTERNAL_YES;
    }

    /**
     * Returns project status as string name.
     *
     * @return string
     */
    public function getStatusAsName()
    {
        if (array_key_exists((int) $this->private, $this->statusesNames)) {
            return $this->statusesNames[(int) $this->private];
        }

        return '';
    }

    /**
     * Returns the class name to be used for project status.
     *
     * @return string
     */
    public function getStatusClass()
    {
        if (array_key_exists((int) $this->private, $this->attrClassNames)) {
            return $this->attrClassNames[(int) $this->private];
        }

        return '';
    }

    /**
     * Get user preferred messaging type.
     *
     * @param int $userId
     *
     * @return mixed
     */
    public function getPreferredMessageIdForUser($userId)
    {
        return $this->projectUsers()
            ->where('user_id', '=', $userId)
            ->first()
            ->message_id;
    }
}
