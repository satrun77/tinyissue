<?php

namespace Tinyissue\Model\Role;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'roles_permissions';
    protected $permission = [];
    public $timestamps = false;

    public function permission()
    {
        return $this->hasOne('Tinyissue\Model\Permission', 'id', 'permission_id');
    }
}
