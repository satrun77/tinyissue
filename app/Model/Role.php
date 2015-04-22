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
 * Role is model class for roles
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int    $id
 * @property string $name
 * @property string $role
 * @property string $description
 */
class Role extends Model
{
    use Traits\Role\QueryTrait,
        Traits\Role\RelationTrait;

    /**
     * Timestamp enabled
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Name of database table
     *
     * @var string
     */
    protected $table = 'roles';
}
