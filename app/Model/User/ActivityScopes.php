<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\User;

use Illuminate\Database\Eloquent\Builder;
use Tinyissue\Model\Project;

/**
 * ActivityScopes is trait class containing the model scope methods.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait ActivityScopes
{
    /**
     * Include activity related details from comments, issue, users, note.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeLoadRelatedDetails(Builder $query)
    {
        return $query->with('activity', 'issue', 'user', 'assignTo', 'comment', 'note')
            ->orderBy('users_activity.created_at', 'DESC');
    }

    /**
     * For logged users with role User, show issues that are created by them in internal projects
     * of issue create by any for other project statuses.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeLimitResultForUserRole(Builder $query)
    {
        $user = $this->getLoggedUser();
        if (!is_null($user) && !$user->isUser()) {
            $query->join('projects_issues', 'projects_issues.id', '=', 'item_id');
            $query->join('projects', 'projects.id', '=', 'parent_id');
            $query->where(function (Builder $query) use ($user) {
                $query->where(function (Builder $query) use ($user) {
                    $query->where('created_by', '=', $user->id);
                    $query->where('private', '=', Project::INTERNAL_YES);
                });
                $query->orWhere('private', '<>', Project::INTERNAL_YES);
            });
        }

        return $query;
    }

    abstract public function getLoggedUser();
}
