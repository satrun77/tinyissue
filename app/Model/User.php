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

use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Mail;

/**
 * User is model class for users
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable,
        CanResetPassword;
    const DELETED_USERS = 1;
    const NOT_DELETED_USERS = 0;
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
    protected $fillable = ['name', 'email', 'password', 'firstname', 'lastname', 'role_id'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    protected $permission;
    protected $projects;

    public static function countUsers($deleted = self::NOT_DELETED_USERS)
    {
        return static::where('deleted', '=', $deleted)->count();
    }

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
            if (!in_array($value, [".", ".."])) {
                $languages[$value] = $value;
            }
        }

        return $languages;
    }

    /**
     * A user has one role (inverse relationship of Role::users).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('Tinyissue\Model\Role', 'role_id');
    }

    /**
     * User has many comments (One-many relationship of Comment::user).
     *
     * @return mixed
     */
    public function comments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Comment', 'created_by', 'id');
    }

    /**
     * Returns issues created by the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issuesCreatedBy()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'created_by');
    }

    /**
     * Returns issues closed by the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issuesClosedBy()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'closed_by');
    }

    /**
     * Returns issues updated by the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issuesUpdatedBy()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'updated_by');
    }

    /**
     * User has many attachments (One-many relationship of Attachment::user).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Attachment', 'uploaded_by');
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
     * Returns projects with issues details eager loaded
     *
     * @param int $status
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectsWidthIssues($status = Project::STATUS_OPEN)
    {
        return $this->projects($status)->with([
            'issues'               => function ($query) {
                $query->with('updatedBy');
                $query->where('assigned_to', '=', $this->id);
            },
            'issues.user'          => function ($query) {

            },
            'issues.countComments' => function ($query) {

            },
        ]);
    }

    /**
     * Returns all projects the user can access
     *
     * @param int $status
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects($status = Project::STATUS_OPEN)
    {
        return $this->belongsToMany('Tinyissue\Model\Project', 'projects_users')->where('status', '=', $status);
    }

    /**
     * Returns user projects with activities details eager loaded
     *
     * @param int $status
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectsWidthActivities($status = Project::STATUS_OPEN)
    {
        return $this->projects($status)->with([
            'activities' => function ($query) {
                $query->with('activity', 'issue', 'user', 'assignTo', 'comment', 'note');
                $query->orderBy('created_at', 'DESC');
            },
        ]);
    }

    /**
     * Count number of assigned issues in a project
     *
     * @param int $projectId
     *
     * @return int
     */
    public function assignedIssuesCount($projectId = 0)
    {
        $issues = $this->issues();

        if (0 < $projectId) {
            $issues = $issues->where('project_id', '=', $projectId);
        }
        $issues->where('status', '=', Project\Issue::STATUS_OPEN);

        return $issues->count();
    }

    /**
     * User has many issues assigned to (One-many relationship of Issue::assigned).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issues()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'assigned_to');
    }

    /**
     * Returns all projects with open issue count
     *
     * @param int $status
     *
     * @return $this
     */
    public function projectsWithCountOpenIssues($status = Project::STATUS_OPEN)
    {
        if ($this->permission('project-all')) {
            return Project::with('openIssuesCount')->where('status', '=', $status);
        }

        return $this->projects($status)->with('openIssuesCount');
    }

    /**
     * Whether or not the user has a permission
     *
     * @param string $key
     *
     * @return bool
     */
    public function permission($key)
    {
        $this->loadPermisions();
        foreach ($this->permission as $permission) {
            if ($permission->permission->permission === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load user premissions
     *
     * @return void
     */
    protected function loadPermisions()
    {
        if (null == $this->permission) {
            $this->permission = $this->permissions()->with('permission')->get();
        }
    }

    /**
     * Returns all permission for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany('\Tinyissue\Model\Role\Permission', 'role_id', 'role_id');
    }

    /**
     * Whether or not the user has a valid permission in current context
     * e.g. can access the issue or the project
     *
     * @param string $context
     * @param array  $params
     *
     * @return bool
     */
    public function permissionInContext($context, array $params)
    {
        if ($this->permission(Permission::PERM_PROJECT_ALL)) {
            return true;
        }

        $project = empty($params['project']) ? false : $params['project'];

        switch ($context) {
            case Permission::PERM_PROJECT_MODIFY:
            case Permission::PERM_ISSUE_CREATE:
            case Permission::PERM_ISSUE_COMMENT:
            case Permission::PERM_ISSUE_MODIFY:
                if ($project && $project->users()->where('user_id', '=', $this->id)->count() === 0) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Return user full name with property "fullname"
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->attributes['firstname'] . ' ' . $this->attributes['lastname'];
    }

    /**
     * Add a new user.
     *
     * @param array $info
     *
     * @return array
     */
    public function createUser(array $info)
    {
        $insert = [
            'email'     => $info['email'],
            'firstname' => $info['firstname'],
            'lastname'  => $info['lastname'],
            'role_id'   => $info['role_id'],
            'password'  => Hash::make($password = Str::random(6)),
        ];

        $this->fill($insert)->save();

        /* Send Activation email */
        $viewData = [
            'email'    => $info['email'],
            'password' => $password,
        ];
        Mail::send('email.new_user', $viewData, function ($message) {
            $message->to($this->email, $this->fullname)->subject(trans('tinyissue.subject_your_account'));
        });

        return true;
    }

    /**
     * Soft deletes a user and empties the email
     *
     * @return bool
     */
    public function delete()
    {
        $this->update([
            'email'   => '',
            'deleted' => static::DELETED_USERS,
        ]);
        Project\User::where('user_id', '=', $this->id)->delete();

        return true;
    }

    /**
     * Update the user
     *
     * @param array $info
     *
     * @return bool|int
     */
    public function update(array $info = [])
    {
        $update = [
            'email'     => $info['email'],
            'firstname' => $info['firstname'],
            'lastname'  => $info['lastname'],
            'role_id'   => $info['role_id'],
        ];

        if ($info['password']) {
            $update['password'] = Hash::make($info['password']);
        }

        return parent::update($update);
    }

    /**
     * Updates the users settings, validates the fields.
     *
     * @param array $info
     *
     * @return array
     */
    public function updateSetting(array $info)
    {
        $update = [
            'email'     => $info['email'],
            'firstname' => $info['firstname'],
            'lastname'  => $info['lastname'],
            'language'  => $info['language'],
        ];

        if ($info['password']) {
            $update['password'] = \Hash::make($info['password']);
        }

        return parent::update($update);
    }
}
