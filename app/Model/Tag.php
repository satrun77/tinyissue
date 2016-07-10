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
 * @property int    $id
 * @property int    $parent_id
 * @property string $name
 * @property string $fullname
 * @property string $bgcolor
 * @property bool   $group
 * @property Tag    $parent
 * @property int    $role_limit
 * @property int    $message_limit
 * @property int    $readonly
 */
class Tag extends Model
{
    use Traits\Tag\CrudTrait,
        Traits\Tag\QueryTrait,
        Traits\Tag\RelationTrait,
        Traits\Tag\CountTrait;

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
     * Core tag group: Type.
     *
     * @var string
     */
    const GROUP_TYPE = 'type';

    /**
     * Core tag group: Resolution.
     *
     * @var string
     */
    const GROUP_RESOLUTION = 'resolution';

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
    public $fillable = ['parent_id', 'name', 'bgcolor', 'group', 'role_limit', 'message_limit', 'readonly'];

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

    /**
     * Whether or not the current user can view this tag.
     *
     * @return bool
     */
    public function canView()
    {
        return auth()->user()->role_id >= $this->role_limit;
    }

    /**
     * Whether or not the tag to mark issue as ready only.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isReadOnly(User $user = null)
    {
        if (is_null($user)) {
            return (boolean) $this->readonly;
        }

        return (boolean) $this->readonly && $user->role_id <= $this->readonly;
    }

    /**
     * Return an array of tag details.
     *
     * @return array
     */
    public function toShortArray()
    {
        return [
            'id'            => $this->id,
            'name'          => $this->fullname,
            'bgcolor'       => $this->bgcolor,
            'message_limit' => $this->message_limit,
            'group'         => $this->parent->fullname,
        ];
    }

    /**
     * Returns an array of core groups.
     *
     * @return array
     */
    public static function getCoreGroups()
    {
        return [
            self::GROUP_STATUS,
            self::GROUP_TYPE,
            self::GROUP_RESOLUTION,
        ];
    }

    /**
     * Whether or not the user is allowed to receive messages that contains the current tag.
     *
     * @param User $user
     *
     * @return bool
     */
    public function allowMessagesToUser(User $user)
    {
        if (!$this->message_limit || $this->message_limit <= $user->role_id) {
            return true;
        }

        return false;
    }
}
