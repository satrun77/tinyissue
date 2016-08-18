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

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Tinyissue\Extensions\Auth\LoggedUser;

/**
 * User is model class for users.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int                 $id
 * @property int                 $deleted
 * @property int                 $role_id
 * @property string              $language
 * @property string              $email
 * @property string              $password
 * @property string              $firstname
 * @property string              $lastname
 * @property string              $fullname
 * @property int                 $status
 * @property int                 $private
 * @property string              $image
 * @property Role                $role
 * @property Eloquent\Collection $comments
 * @property Eloquent\Collection $issuesCreatedBy
 * @property Eloquent\Collection $issuesClosedBy
 * @property Eloquent\Collection $issuesUpdatedBy
 * @property Eloquent\Collection $attachments
 * @property Eloquent\Collection $projects
 * @property Eloquent\Collection $issues
 *
 * @method Eloquent\Collection getProjects()
 * @method Eloquent\Collection getProjectsWithSettings()
 * @method Eloquent\Collection getActiveUsers()
 * @method Eloquent\Collection getProjectsWithRecentActivities()
 * @method Eloquent\Collection getProjectsWithRecentIssues()
 * @method Eloquent\Collection getIssuesGroupByTags(Eloquent\Collection $tagIds, $projectId = null)
 * @method Eloquent\Collection getProjectsWithOpenIssuesCount($status = Project::STATUS_OPEN)
 * @method int countNotDeleted()
 * @method int countDeleted()
 * @method int createdIssuesCount($projectId = 0)
 * @method int countProjectsByStatus($status)
 * @method User updateOrCreate(array $attributes, array $values = [])
 * @method $this private($status = false)
 * @method $this notPrivate()
 * @method $this developerOrHigher()
 * @method $this active()
 * @method $this removed()
 * @method $this notMemberOfProject(Project $project)
 */
class User extends ModelAbstract implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable,
        CanResetPassword,
        UserRelations,
        UserScopes,
        LoggedUser,
        Authorizable;

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
     * @param User|null $user
     *
     * @return \Tinyissue\Repository\User\Updater
     */
    public function updater(User $user = null)
    {
        return parent::updater($user);
    }

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Checks to see if $this user is current user.
     *
     * @return bool
     */
    public function me()
    {
        return $this->id == $this->getLoggedUser()->id;
    }

    /**
     * Return user full name with property "fullname".
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        if ((int) $this->private === self::PRIVATE_NO || ($this->isLoggedIn() && $this->getLoggedUser()->can('viewName', $this))) {
            return $this->attributes['firstname'] . ' ' . $this->attributes['lastname'];
        }

        return trans('tinyissue.anonymous');
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
            static::ACTIVE_USER   => trans('tinyissue.active'),
            static::BLOCKED_USER  => trans('tinyissue.blocked'),
            static::INACTIVE_USER => trans('tinyissue.inactive'),
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

    /**
     * Whether or not the user is normal user role.
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->exists && $this->role->role === Role::ROLE_USER;
    }

    /**
     * Whether or not the user is administrator.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->exists && $this->role->role === Role::ROLE_ADMIN;
    }

    /**
     * Whether or not the user is manager.
     *
     * @return bool
     */
    public function isManager()
    {
        return $this->exists && $this->role->role === Role::ROLE_MANAGER;
    }

    /**
     * Whether or not the user is developer.
     *
     * @return bool
     */
    public function isDeveloper()
    {
        return $this->exists && $this->role->role === Role::ROLE_DEVELOPER;
    }

    /**
     * Whether or not the user is manager or admin.
     *
     * @return bool
     */
    public function isManagerOrMore()
    {
        return $this->isAdmin() || $this->isManager();
    }

    /**
     * Whether or not the user is developer or manager or admin.
     *
     * @return bool
     */
    public function isDeveloperOrMore()
    {
        return $this->isAdmin() || $this->isManager() || $this->isDeveloper();
    }

    /**
     * Get user role name.
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->role->role;
    }
}
