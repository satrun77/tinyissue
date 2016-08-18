<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Role;

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Role;
use Tinyissue\Repository\Repository;

class Fetcher extends Repository
{
    /**
     * @var Role
     */
    protected $model;

    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    /**
     * Get list of all of the role names.
     *
     * @return array
     */
    public function getNameDropdown()
    {
        return $this->model->pluck('name', 'id')->prepend('Disabled')->all();
    }

    /**
     * Get collection of all of the roles with users within each role.
     *
     * @return Collection
     */
    public function getRolesWithUsers()
    {
        return $this->model->with('users')->orderBy('id', 'DESC')->get();
    }
}
