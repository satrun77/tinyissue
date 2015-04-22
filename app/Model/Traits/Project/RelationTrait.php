<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project;

use Illuminate\Database\Eloquent;
use Tinyissue\Model\Project;

/**
 * RelationTrait is trait class containing the relationship methods for the Project model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Eloquent\Model hasMany($related, $foreignKey = null, $localKey = null)
 * @method Eloquent\Model belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
 */
trait RelationTrait
{
    /**
     * Returns all issues related to project.
     *
     * @return Eloquent\Relations\HasMany
     */
    public function issues()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'project_id');
    }

    /**
     * Returns issues in the project with user details eager loaded
     *
     * @return Eloquent\Relations\HasMany
     */
    public function issuesByUser()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue', 'project_id')->with('user')->get();
    }

    /**
     * Returns all users assigned in the current project.
     *
     * @return Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('\Tinyissue\Model\User', 'projects_users', 'project_id', 'user_id');
    }

    /**
     * Return a user that is member of a project
     *
     * @param int $userId
     *
     * @return Eloquent\Relations\BelongsToMany
     */
    public function user($userId)
    {
        return $this->users()->where('user_id', '=', (int) $userId);
    }

    /**
     * Project has many project users
     *
     * @return Eloquent\Relations\HasMany
     */
    public function projectUsers()
    {
        return $this->hasMany('Tinyissue\Model\Project\User', 'project_id');
    }

    /**
     * Returns project activities
     *
     * @return Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany('Tinyissue\Model\User\Activity', 'parent_id');
    }

    /**
     * Returns notes in the project
     *
     * @return Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany('Tinyissue\Model\Project\Note', 'project_id');
    }
}
