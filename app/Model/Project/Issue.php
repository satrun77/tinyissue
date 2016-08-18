<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Project;

use Illuminate\Support\Collection;
use Tinyissue\Extensions\Auth\LoggedUser;
use Tinyissue\Model;
use Tinyissue\Model\ModelAbstract;

/**
 * Issue is model class for project issues.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int           $id
 * @property int           $created_by
 * @property int           $project_id
 * @property string        $title
 * @property string        $body
 * @property int           $assigned_to
 * @property int           $time_quote
 * @property bool          $lock_quote
 * @property int           $closed_by
 * @property int           $closed_at
 * @property int           status
 * @property int           $updated_at
 * @property int           $updated_by
 * @property Model\Project $project
 * @property Model\User    $user
 * @property Model\User    $assigned
 * @property Model\User    $closers
 * @property Model\User    $updatedBy
 * @property Collection    $attachments
 * @property Collection    $activities
 * @property Collection    $generalActivities
 * @property Collection    $commentActivities
 * @property Collection    $tags
 * @property Collection    $comments
 * @property Collection    $messagesQueue
 *
 * @method  Model\Tag getStatusTag()
 * @method  Model\Tag getTypeTag()
 * @method  Model\Tag getResolutionTag()
 * @method  Collection getGeneralActivities()
 * @method  Collection getCommentActivities()
 * @method  int countOpenIssues()
 * @method  int countClosedIssues()
 * @method  $this open()
 * @method  $this closed()
 * @method  $this status($status = Issue::STATUS_OPEN)
 * @method  $this assignedOrCreated(Model\User $user = null)
 * @method  $this assignedTo($user = null)
 * @method  $this createdBy(Model\User $user = null)
 * @method  $this limitByCreatedForInternalProject(Model\Project $project, Model\User $user = null)
 * @method  $this forProject($projectId)
 * @method  $this searchContent($keyword)
 * @method  $this whereTags(...$tags)
 */
class Issue extends ModelAbstract
{
    use IssueRelations,
        IssueScopes,
        LoggedUser;

    /**
     * Issue status: Open.
     *
     * @var int
     */
    const STATUS_OPEN = 1;

    /**
     * Issue status: Closed.
     *
     * @var int
     */
    const STATUS_CLOSED = 0;

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
    protected $table = 'projects_issues';

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    protected $fillable = ['created_by', 'project_id', 'title', 'body', 'assigned_to', 'time_quote', 'lock_quote'];

    /**
     * Set attributes default value.
     *
     * @var array
     */
    protected $attributes = [
        'status' => self::STATUS_OPEN,
    ];

    /**
     * @param Model\User|null $user
     *
     * @return \Tinyissue\Repository\Project\Issue\Updater
     */
    public function updater(Model\User $user = null)
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
        return \URL::to('project/' . $this->project_id . '/issue/' . $this->id . (($url) ? '/' . $url : ''));
    }

    /**
     * Convert time quote from an array into seconds.
     *
     * @param array $value
     */
    public function setTimeQuoteAttribute($value)
    {
        $seconds = $value;
        if (is_array($value)) {
            $seconds = 0;
            $seconds += isset($value['m']) ? ($value['m'] * 60) : 0;
            $seconds += isset($value['h']) ? ($value['h'] * 60 * 60) : 0;
        }
        $this->attributes['time_quote'] = (int)$seconds;
    }

    /**
     * Returns the color of tag status.
     *
     * @return string
     */
    public function getTypeColorAttribute()
    {
        $tag = $this->tags->filter(function (Model\Tag $tag) {
            return $tag->parent->name === 'type';
        })->first();

        if ($tag) {
            return $tag->bgcolor;
        }

        return null;
    }

    /**
     * Whether or not the issue is new.
     *
     * @return bool
     */
    public function isNew()
    {
        if ($this->status === 0) {
            return false;
        }

        return $this->tags->count() === 0;
    }

    /**
     * Whether or not the issue is open or closed.
     *
     * @return bool
     */
    public function isOpen()
    {
        return (boolean)$this->status;
    }

    /**
     * Check if the issue contains a tag with option to set the issue as readonly to current user.
     *
     * @param Model\User $user
     *
     * @return bool
     */
    public function hasReadOnlyTag(Model\User $user)
    {
        $hasReadOnly = $this->tags->where('readonly', $user->role_id);

        return !$hasReadOnly->isEmpty();
    }

    /**
     * Whether or not the issue quote is locked by manager.
     *
     * @return bool
     */
    public function isQuoteLocked()
    {
        return (boolean)$this->lock_quote;
    }

    /**
     * Whether or not a user is the creator of the issue.
     *
     * @param Model\User $user
     *
     * @return bool
     */
    public function isCreatedBy(Model\User $user)
    {
        return $this->created_by === $user->id;
    }
}
