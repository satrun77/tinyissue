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

/**
 * Tag is model class for tags.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int    $id
 * @property string $name
 *
 * @method  Collection getAll()
 */
class Message extends ModelAbstract
{
    /**
     * Timestamp disabled.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    public $fillable = [];

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * List of default message to role.
     *
     * @var array
     */
    public static $defaultMessageToRole = [
        Role::ROLE_USER      => 'Minimum',
        Role::ROLE_DEVELOPER => 'Standard',
        Role::ROLE_MANAGER   => 'Full',
        Role::ROLE_ADMIN     => 'Disabled',
    ];

    /**
     * Whether the message is disabled or not.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->name === 'Disabled';
    }

    /**
     * Whether or not an event is active.
     *
     * @param string $event
     *
     * @return bool
     */
    public function isActiveEvent($event)
    {
        return (bool) $this->{$event} === true;
    }
}
