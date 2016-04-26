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

/**
 * Tag is model class for tags.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int     $id
 * @property int     $parent_id
 * @property string  $name
 * @property string  $fullname
 * @property string  $bgcolor
 * @property bool $group
 * @property Tag     $parent
 */
class Tag extends Model
{
    use Traits\Tag\CrudTrait,
        Traits\Tag\QueryTrait,
        Traits\Tag\RelationTrait,
        Traits\Tag\CountTrait,
        Traits\Tag\DataMappingTrait;

    /**
     * Core tag: Open.
     *
     * @var string
     */
    const STATUS_OPEN = 'open';

    /**
     * Core tag: Closed.
     *
     * @var string
     */
    const STATUS_CLOSED = 'closed';

    /**
     * Core tag group: Status.
     *
     * @var string
     */
    const GROUP_STATUS = 'status';

    /**
     * Timestamp enabled.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    public $fillable = ['parent_id', 'name', 'bgcolor', 'group'];

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'tags';

    /**
     * Generate a URL for the tag.
     *
     * @param string $url
     *
     * @return mixed
     */
    public function to($url)
    {
        return \URL::to('administration/tag/' . $this->id . (($url) ? '/' . $url : ''));
    }

    /**
     * Returns tag full name with prefix group name and ":" in between.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return ucwords($this->attributes['name']);
    }
}
