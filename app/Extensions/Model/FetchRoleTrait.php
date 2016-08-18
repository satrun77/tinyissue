<?php
/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Extensions\Model;

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Role;

/**
 * FetchRoleTrait is trait class contains method to set and fetch roles.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait FetchRoleTrait
{
    /**
     * Collection of all tags.
     *
     * @var array
     */
    protected $roles = null;

    /**
     * @return array
     */
    protected function getRoleNameDropdown()
    {
        if (is_null($this->roles)) {
            $this->roles = (new Role())->getNameDropdown();
        }

        return $this->roles;
    }
}
