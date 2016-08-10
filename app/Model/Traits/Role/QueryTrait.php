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
use Tinyissue\Model\Role;

/**
 * QueryTrait is trait class containing the database queries methods for the Role model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 * @method $this with($relations);
 */
trait QueryTrait
{
    /**
     * Drop down of all roles.
     *
     * @return Eloquent\Collection
     */
    public static function dropdown()
    {
        return Role::lists('name', 'id');
    }

    /**
     * Returns all roles with users of each role.
     *
     * @return Eloquent\Collection
     */
    public function rolesWithUsers()
    {
        return $this->with('users')->orderBy('id', 'DESC')->get();
    }
}
