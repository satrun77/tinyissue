<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Role;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Traits\Role\Permission\RelationTrait;

/**
 * Permission is model class for role permissions
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $role_id
 * @property int $permission_id
 */
class Permission extends Model
{
    use RelationTrait;

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
    protected $table = 'roles_permissions';
}
