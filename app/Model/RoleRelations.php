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

use Illuminate\Database\Eloquent\Relations;

/**
 * RelationTrait is trait class containing the relationship methods for the Role model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait RoleRelations
{
    /**
     * Role has many users (One-many relationship of User::role).
     *
     * @return User
     */
    public function users()
    {
        return $this
            ->hasMany(User::class, 'role_id', 'id')
            ->where('deleted', '=', User::NOT_DELETED_USERS)
            ->orderBy('firstname', 'asc');
    }

    /**
     * Role has many users in a project_users.
     *
     * @return Project\User
     */
    public function projectUsers()
    {
        return $this->hasMany(Project\User::class);
    }

    abstract public function hasMany($related, $foreignKey = null, $localKey = null);
    abstract public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null);
}
