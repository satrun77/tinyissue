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

use Auth as Auth;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Project\Issue;

/**
 * User is model class for users.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int    $id
 * @property int    $deleted
 * @property int    $role_id
 * @property string $language
 * @property string $email
 * @property string $password
 * @property string $firstname
 * @property string $lastname
 * @property string $fullname
 * @property int    $status
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable,
        CanResetPassword,
        Traits\User\CountTrait,
        Traits\User\RelationTrait,
        Traits\User\CrudTrait,
        Traits\User\QueryTrait;

    /**
     * User name is private.
     *
     * @var int
     */
    const PRIVATE_YES = 1;

    /**
     * User name is public.
     *
     * @var int
     */
    const PRIVATE_NO = 0;

    /**
     * User status Deleted.
     *
     * @var int
     */
    const DELETED_USERS = 1;

    /**
     * User status not deleted.
     *
     * @var int
     */
    const NOT_DELETED_USERS = 0;

    /**
     * User status active. (Standard).
     *
     * @var int
     */
    const ACTIVE_USER = 1;

    /**
     * User status blocked. (Too many login attempts).
     *
     * @var int
     */
    const BLOCKED_USER = 2;

    /**
     * User status inactive. (Cannot login at the moment).
     *
     * @var int
     */
    const INACTIVE_USER = 0;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['deleted', 'email', 'password', 'firstname', 'lastname', 'role_id', 'private', 'language', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Collection of user permissions.
     *
     * @var Eloquent\Collection
     */
    protected $permission;

    /**
     * Get available languages from translations folder.
     *
     * @return array
     */
    public static function getLanguages()
    {
        $languages = [];

        $cdir = scandir(__DIR__ . '/../../resources/lang');
        foreach ($cdir as $value) {
            if (!in_array($value, ['.', '..'])) {
                $languages[$value] = $value;
            }
        }

        return $languages;
    }

    /**
     * Checks to see if $this user is current user.
     *
     * @return bool
     */
    public function me()
    {
        return $this->id == \Auth::user()->id;
    }

    /**
     * Whether or not the user has a valid permission in current context
     * e.g. can access the issue or the project.
     *
     * @param array $params
     *
     * @return bool
     */
    public function permissionInContext(array $params)
    {
        // Can access all projects
        if ($this->permission(Permission::PERM_PROJECT_ALL)) {
            return true;
        }

        $project = array_get($params, 'project', function () use ($params) {
            $issue = array_get($params, 'issue');
            if ($issue instanceof Issue) {
                return $issue->project;
            }

            return;
        });

        // Is member of the project
        if ($project && !$project->isMember($this->id)) {
            return false;
        }

        return true;
    }

    /**
     * Whether or not the user has a permission.
     *
     * @param string $key
     *
     * @return bool
     */
    public function permission($key)
    {
        $this->loadPermissions();
        foreach ($this->permission as $permission) {
            if ($permission->permission->isEqual($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return user full name with property "fullname".
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        if ($this->private && (Auth::guest() || !Auth::user()->permission('administration'))) {
            return trans('tinyissue.anonymous');
        }

        return $this->attributes['firstname'] . ' ' . $this->attributes['lastname'];
    }

    /**
     * Return user image.
     *
     * @return string
     */
    public function getImageAttribute()
    {
        return app('gravatar')->src($this->email);
    }

    /**
     * Returns list of user statuses.
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            static::ACTIVE_USER     => trans('tinyissue.active'),
            static::BLOCKED_USER    => trans('tinyissue.blocked'),
            static::INACTIVE_USER   => trans('tinyissue.inactive'),
        ];
    }

    /**
     * Whether or not the user is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return (int) $this->status === static::ACTIVE_USER;
    }

    /**
     * Whether or not the user is inactive.
     *
     * @return bool
     */
    public function isInactive()
    {
        return (int) $this->status === static::INACTIVE_USER;
    }

    /**
     * Whether or not the user is blocked.
     *
     * @return bool
     */
    public function isBlocked()
    {
        return (int) $this->status === static::BLOCKED_USER;
    }
}
