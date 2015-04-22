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
use Tinyissue\Model\Traits\Project\User\RelationTrait;

/**
 * User is model class for project users
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $user_id
 * @property int $project_id
 * @property int $role_id
 */
class User extends Model
{
    use RelationTrait;

    /**
     * Name of database table
     *
     * @var string
     */
    protected $table = 'projects_users';

    /**
     * Timestamp enabled
     *
     * @var bool
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'role_id',
    ];
}
