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

use Illuminate\Database\Eloquent\Relations;

/**
 * UserRelations is trait class containing the relationship methods for the User model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait UserRelations
{
    /**
     * A user has one role (inverse relationship of Role::users).
     *
     * @return Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * User has many comments (One-many relationship of Comment::user).
     *
     * @return Project\Issue\Comment
     */
    public function comments()
    {
        return $this->hasMany(Project\Issue\Comment::class, 'created_by', 'id');
    }

    /**
     * Returns issues created by the user.
     *
     * @return Project\Issue
     */
    public function issuesCreatedBy()
    {
        return $this->hasMany(Project\Issue::class, 'created_by');
    }

    /**
     * Returns issues closed by the user.
     *
     * @return Project\Issue
     */
    public function issuesClosedBy()
    {
        return $this->hasMany(Project\Issue::class, 'closed_by');
    }

    /**
     * Returns issues updated by the user.
     *
     * @return Project\Issue
     */
    public function issuesUpdatedBy()
    {
        return $this->hasMany(Project\Issue::class, 'updated_by');
    }

    /**
     * User has many attachments (One-many relationship of Attachment::user).
     *
     * @return Project\Issue\Attachment
     */
    public function attachments()
    {
        return $this->hasMany(Project\Issue\Attachment::class, 'uploaded_by');
    }

    /**
     * Returns all projects the user can access.
     *
     * @return Project
     */
    public function projects()
    {
        return $this
            ->belongsToMany(Project::class, 'projects_users')
            ->orderBy('name');
    }

    /**
     * User has many issues assigned to (One-many relationship of Issue::assigned).
     *
     * @return Project\Issue
     */
    public function issues()
    {
        return $this->hasMany(Project\Issue::class, 'assigned_to');
    }

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);

    abstract public function hasMany($related, $foreignKey = null, $localKey = null);

    abstract public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null);
}
