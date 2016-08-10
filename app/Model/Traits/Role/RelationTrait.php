<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Role;

use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model\User;

/**
 * RelationTrait is trait class containing the relationship methods for the Role model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait RelationTrait
{
    /**
     * Role has many users (One-many relationship of User::role).
     *
     * @return Relations\HasMany
     */
    public function users()
    {
        return $this
            ->hasMany('Tinyissue\Model\User', 'role_id', 'id')
            ->where('deleted', '=', User::NOT_DELETED_USERS)
            ->orderBy('firstname', 'asc');
    }

    /**
     * Role has many users in a project_users.
     *
     * @return Relations\HasMany
     */
    public function projectUsers()
    {
        return $this->hasMany('Tinyissue\Model\Project\User');
    }

    /**
     * Role has many role permission.
     *
     * @return Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            '\Tinyissue\Model\Permission',
            'roles_permissions',
            'role_id',
            'permission_id',
            'role_id'
        );
    }
}
