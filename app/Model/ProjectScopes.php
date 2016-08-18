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
 * ProjectScopes is trait class containing the model scope methods.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait ProjectScopes
{
    /**
     * Filter project by status.
     *
     * @param Builder $query
     * @param int     $status
     *
     * @return Builder
     */
    public function scopeStatus(Builder $query, $status = Project::STATUS_OPEN)
    {
        return $query->where('status', '=', $status);
    }

    /**
     * Filter project to active status.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $this->scopeStatus($query, Project::STATUS_OPEN);
    }

    /**
     * Filter project to archived status.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeArchived(Builder $query)
    {
        return $this->scopeStatus($query, Project::STATUS_ARCHIVED);
    }

    /**
     * Filter project to public project.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopePublic(Builder $query)
    {
        return $query->where('private', '=', Project::PRIVATE_NO);
    }

    /**
     * Filter project to public not project.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeNotPublic(Builder $query)
    {
        return $query->where(function (Builder $query) {
            $query
                ->where('private', '=', static::PRIVATE_YES)
                ->orWhere('private', '=', static::INTERNAL_YES);
        });
    }
}
