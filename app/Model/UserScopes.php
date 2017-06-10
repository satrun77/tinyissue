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

use Illuminate\Database\Eloquent\Builder;

/**
 * UserScopes is trait class containing the model scope methods.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait UserScopes
{
    /**
     * Get user by private status.
     *
     * @param Builder $query
     * @param bool    $status
     *
     * @return Builder
     */
    public function scopePrivate(Builder $query, $status = false)
    {
        return $query->where('private', '=', $status);
    }

    /**
     * Get user that are not private status.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeNotPrivate(Builder $query)
    {
        return $query->where('private', '=', false);
    }

    /**
     * Get user with role developer or manager or admin.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeDeveloperOrHigher(Builder $query)
    {
        return $query->where('users.role_id', '>', 1);
    }

    /**
     * Not deleted user.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('deleted', '=', static::NOT_DELETED_USERS);
    }

    /**
     * Deleted user.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeRemoved(Builder $query)
    {
        return $query->where('deleted', '=', static::DELETED_USERS);
    }

    /**
     * Get users that are not member of a project.
     *
     * @param Builder $query
     * @param Project $project
     *
     * @return Builder
     */
    public function scopeNotMemberOfProject(Builder $query, Project $project)
    {
        if ($project->id > 0) {
            return $query->whereNotIn('id', $project->users->dropdown('user_id'));
        }

        return $query;
    }
}
