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

use Illuminate\Database\Eloquent\Model as BaseModel;
use Tinyissue\Model;
use Tinyissue\Model\Traits\CountAttributeTrait;
use Tinyissue\Model\Traits\Project\Issue\CountTrait;
use Tinyissue\Model\Traits\Project\Issue\CrudTrait;
use Tinyissue\Model\Traits\Project\Issue\CrudTagTrait;
use Tinyissue\Model\Traits\Project\Issue\RelationTrait;
use Tinyissue\Model\Traits\Project\Issue\QueryTrait;

/**
 * Issue is model class for project issues.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int              $id
 * @property int              $created_by
 * @property int              $project_id
 * @property string           $title
 * @property string           $body
 * @property int              $assigned_to
 * @property int              $time_quote
 * @property int              $closed_by
 * @property int              $closed_at
 * @property int              status
 * @property int              $updated_at
 * @property int              $updated_by
 * @property Model\Project    $project
 * @property Model\User       $user
 * @property Model\User       $updatedBy
 */
class Issue extends BaseModel
{
    use CountAttributeTrait,
        CountTrait,
        CrudTrait,
        CrudTagTrait,
        RelationTrait,
        QueryTrait;

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
    protected $fillable = ['created_by', 'project_id', 'title', 'body', 'assigned_to', 'time_quote'];

    /**
     * Returns the aggregate value of number of comments in an issue.
     *
     * @return int
     */
    public function getCountCommentsAttribute()
    {
        return $this->getCountAttribute('countComments');
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
        $this->attributes['time_quote'] = (int) $seconds;
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
}
