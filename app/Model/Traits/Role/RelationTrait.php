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

use Illuminate\Database\Eloquent;
use Tinyissue\Model\User;

/**
 * RelationTrait is trait class containing the relationship methods for the Role model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Eloquent\Model hasMany($related, $foreignKey = null, $localKey = null)
 * @method Eloquent\Model belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
 * @method Eloquent\Model belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
 */
trait RelationTrait
{
    /**
     * Role has many users (One-many relationship of User::role).
     *
     * @return Eloquent\Relations\HasMany
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
     * @return Eloquent\Relations\HasMany
     */
    public function projectUsers()
    {
        return $this->hasMany('Tinyissue\Model\Project\User');
    }

    /**
     * Role has many role permission.
     *
     * @return Eloquent\Relations\BelongsToMany
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
