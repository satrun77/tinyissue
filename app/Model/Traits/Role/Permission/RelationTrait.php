<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Role\Permission;

use Illuminate\Database\Eloquent\Relations;

/**
 * RelationTrait is trait class containing the relationship method for the Role\Permission model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait RelationTrait
{
    /**
     * Returns the permission for a role.
     *
     * @return Relations\HasOne
     */
    public function permission()
    {
        return $this->hasOne('Tinyissue\Model\Permission', 'id', 'permission_id');
    }
}
