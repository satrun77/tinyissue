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

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Tinyissue\Model;

/**
 * IssueScopes is trait class containing the model scope methods.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait IssueScopes
{
    /**
     * Filter status by open status.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOpen(Builder $query)
    {
        return $this->scopeStatus($query, Issue::STATUS_OPEN);
    }

    /**
     * Filter status by closed status.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeClosed(Builder $query)
    {
        return $this->scopeStatus($query, Issue::STATUS_CLOSED);
    }

    /**
     * Filter status by a status.
     *
     * @param Builder $query
     * @param int     $status
     *
     * @return Builder
     */
    public function scopeStatus(Builder $query, $status = Issue::STATUS_OPEN)
    {
        return $query->where('status', '=', $status);
    }

    /**
     * Filter issue by created by or assigned to field based on user role.
     *
     * @param Builder   $query
     * @param Model\User|null $user
     *
     * @return Builder
     */
    public function scopeAssignedOrCreated(Builder $query, Model\User $user = null)
    {
        if ($user instanceof Model\User && $user->isUser()) {
            return $this->scopeCreatedBy($query, $user);
        }

        return $this->scopeAssignedTo($query, $user);
    }

    /**
     * Filter issue by assigned to.
     *
     * @param Builder         $query
     * @param null|Model\User $user
     *
     * @return Builder
     */
    public function scopeAssignedTo(Builder $query, $user = null)
    {
        return $this->whereIdEqual($query, 'assigned_to', $user);
    }

    /**
     * Filter issue by created by.
     *
     * @param Builder   $query
     * @param Model\User|null $user
     *
     * @return Builder
     */
    public function scopeCreatedBy(Builder $query, Model\User $user = null)
    {
        return $this->whereIdEqual($query, 'created_by', $user);
    }

    /**
     * Filter issue by created by if user logged in as User role adn current project is private internal.
     *
     * @param Builder       $query
     * @param Model\Project $project
     * @param Model\User|null     $user
     *
     * @return Builder
     */
    public function scopeLimitByCreatedForInternalProject(Builder $query, Model\Project $project, Model\User $user = null)
    {
        if ($user && $user->isUser() && $project->isPrivateInternal()) {
            $query = $this->scopeCreatedBy($query, $user);
        }

        return $query;
    }

    /**
     * Filter issue by project id.
     *
     * @param Builder $query
     * @param int     $projectId
     *
     * @return Builder
     */
    public function scopeForProject(Builder $query, $projectId)
    {
        return $this->whereIdEqual($query, 'project_id', $projectId);
    }

    /**
     * Helper method to filter by a field and a value.
     *
     * @param Builder            $query
     * @param string             $field
     * @param int|Eloquent\Model|null $idOrObject
     *
     * @return $this|Builder
     */
    protected function whereIdEqual(Builder $query, $field, $idOrObject)
    {
        $id = $idOrObject instanceof Eloquent\Model ? $idOrObject->id : $idOrObject;
        if ($id > 0) {
            return $query->where($field, '=', $id);
        }

        return $query;
    }

    /**
     * Filter with like %% on issue string fields title/body.
     *
     * @param Builder $query
     * @param string  $keyword
     *
     * @return Builder
     */
    public function scopeSearchContent(Builder $query, $keyword)
    {
        if (!empty($keyword)) {
            return $query->where(function (Builder $query) use ($keyword) {
                $query->where('title', 'like', '%' . $keyword . '%');
                $query->orWhere('body', 'like', '%' . $keyword . '%');
            });
        }

        return $query;
    }

    /**
     * Filter issues by an array of tag ids.
     *
     * @param Builder $query
     * @param array   ...$tags
     *
     * @return Builder
     */
    public function scopeWhereTags(Builder $query, ...$tags)
    {
        $tags = array_filter($tags);

        if (!empty($tags)) {
            $query->whereHas('tags', function (Builder $query) use ($tags) {
                $query->whereIn('id', $tags);
            });
        }

        return $query;
    }
}
