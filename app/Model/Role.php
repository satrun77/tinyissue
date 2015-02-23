<?php

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    public $timestamps = false;

    /**
     * Dropdown of all roles.
     *
     * @return array
     */
    public static function dropdown()
    {
        return static::lists('name', 'id');
    }

    /**
     * Role has many users (One-many relationship of User::role).
     *
     * @return mixed
     */
    public function users()
    {
        return $this->hasMany('Tinyissue\Model\User', 'role_id', 'id')->where('deleted', '=', User::NOT_DELETED_USERS)->orderBy('firstname', 'asc');
    }

    /**
     * Role has many users in a project_users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectUsers()
    {
        return $this->hasMany('Tinyissue\Model\Project\User');
    }

    /**
     * Role has many role permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany('\Tinyissue\Model\Permission', 'roles_permissions', 'role_id', 'permission_id', 'role_id');
    }
}
