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

use Illuminate\Database\Eloquent\Builder;
use Tinyissue\Model;

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
     * Filter for project user by user id.
     *
     * @param Builder    $query
     * @param Model\User $user
     *
     * @return Builder
     */
    public function scopeForUser(Builder $query, Model\User $user)
    {
        return $query->where('user_id', '=', $user->id);
    }

    /**
     * Filter for project users in list or project ids.
     *
     * @param Builder $query
     * @param array   $projectIds
     *
     * @return Builder
     */
    public function scopeInProjects(Builder $query, array $projectIds)
    {
        return $query->whereIn('project_id', $projectIds);
    }
}
