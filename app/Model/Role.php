<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Project\User;

/**
 * Role is model class for roles.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 * @property string $name
 * @property string $role
 * @property string $description
 * @property \Tinyissue\Model\User[] $users
 * @property User[] $projectUsers
 * @property Permission[] $permissions
 */
class Role extends Model
{
    use Traits\Role\QueryTrait,
        Traits\Role\RelationTrait;

    const ROLE_USER      = 'user';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_MANAGER   = 'manager';
    const ROLE_ADMIN     = 'administrator';

    /**
     * Timestamp enabled.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Returns a class name based on role type.
     *
     * @return string
     */
    public function className()
    {
        switch (strtolower($this->name)) {
            case self::ROLE_USER:
                return 'tag';
            case self::ROLE_MANAGER:
                return 'success';
            case self::ROLE_DEVELOPER:
                return 'info';
            case self::ROLE_ADMIN:
                return 'primary';
        }

        return 'primary';
    }
}
