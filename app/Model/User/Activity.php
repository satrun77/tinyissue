<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\User;

use Illuminate\Support\Collection;
use Tinyissue\Extensions\Auth\LoggedUser;
use Tinyissue\Model\ModelAbstract;

/**
 * Activity is model class for user activities.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int   $type_id
 * @property int   $parent_id
 * @property int   $user_id
 * @property int   $item_id
 * @property int   $action_id
 * @property array $data
 *
 * @method  $this loadRelatedDetails()
 * @method  $this limitResultForUserRole()
 */
class Activity extends ModelAbstract
{
    use ActivityRelations, ActivityScopes, LoggedUser;

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
    protected $table = 'users_activity';

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    protected $fillable = ['type_id', 'parent_id', 'user_id', 'item_id', 'action_id', 'data'];

    /**
     * List of columns and their cast data-type.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get a value from the data field using "dot" notation.
     *
     * @param string $name
     *
     * @return Collection
     */
    public function dataCollection($name)
    {
        return new Collection($this->dataValue($name));
    }

    /*
     * Get a value from the data field using "dot" notation.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function dataValue($name)
    {
        return array_get($this->data, $name);
    }
}
