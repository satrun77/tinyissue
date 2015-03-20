<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tinyissue\Model\Project;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Project as Project;
use Illuminate\Database\Query;

/**
 * User is model class for project users
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @property int $user_id
 * @property int $project_id
 * @property int $role_id
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class User extends Model
{
    protected $table = 'projects_users';
    protected $fillable = [
        'user_id',
        'project_id',
        'role_id',
    ];

    /**
     * Returns the instance of the user in the project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id')->orderBy('firstname', 'ASC');
    }

    /**
     * Returns the instance of the project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project', 'project_id')->orderBy('name', 'ASC');
    }
}
