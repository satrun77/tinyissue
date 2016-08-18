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

use Tinyissue\Model\Message;
use Tinyissue\Model\ModelAbstract;
use Tinyissue\Model\Project;

/**
 * User is model class for project users.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $user_id
 * @property int $project_id
 * @property int $role_id
 * @property int $message_id
 * @property \Tinyissue\Model\User $user
 * @property Message $message
 * @property Project $project
 *
 * @method  $this forUser(\Tinyissue\Model\User $user)
 * @method  $this inProjects(array $projectIds)
 */
class User extends ModelAbstract
{
    use UserRelations, UserScopes;

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'projects_users';

    /**
     * Timestamp enabled.
     *
     * @var bool
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'role_id',
        'message_id',
    ];
}
