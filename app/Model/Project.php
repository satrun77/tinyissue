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
use URL;

/**
 * Project is model class for projects.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int              $id
 * @property string           $name
 * @property int              $status
 * @property int              $default_assignee
 * @property Project\Issue[]  $issues
 * @property int              $openIssuesCount
 * @property int              $closedIssuesCount
 */
class Project extends Model
{
    use Traits\CountAttributeTrait,
        Traits\Project\CountTrait,
        Traits\Project\FilterTrait,
        Traits\Project\SortTrait,
        Traits\Project\RelationTrait,
        Traits\Project\CrudTrait,
        Traits\Project\QueryTrait;

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
        $total       = $this->openIssuesCount + $this->closedIssuesCount;
        $progress    = 100;
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
     * Whether or not the project is private or public.
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->private === true;
    }
}
