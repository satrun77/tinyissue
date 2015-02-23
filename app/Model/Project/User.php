<?php

namespace Tinyissue\Model\Project;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Project as Project;

class User extends Model
{
    protected $table = 'projects_users';
    protected $fillable = array(
        'user_id',
        'project_id',
        'role_id',
    );

    /**
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id')->orderBy('firstname', 'ASC');
    }

    /**
     * @return Project
     */
    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project', 'project_id')->orderBy('name', 'ASC');
    }
}
