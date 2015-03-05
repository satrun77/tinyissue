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

/**
 * Permission is model class for role permissions
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Permission extends Model
{
    protected $table = 'roles_permissions';
    protected $permission = [];
    public $timestamps = false;

    /**
     * Returns the permission for a role
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function permission()
    {
        return $this->hasOne('Tinyissue\Model\Permission', 'id', 'permission_id');
    }
}
