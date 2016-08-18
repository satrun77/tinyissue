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
 * TagScopes is trait class containing the model scope methods.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait TagScopes
{
    /**
     * Load group tag only.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeGroupOnly(Builder $query)
    {
        return $query->where('group', '=', true)->orderBy('group', 'DESC')->orderBy('name', 'ASC');
    }

    /**
     * Load tags that are not group.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeNotGroup(Builder $query)
    {
        return $query->where('group', '=', false);
    }

    /**
     * Return tag by name.
     *
     * @param Builder $query
     * @param string  $type
     *
     * @return Builder
     */
    public function scopeOfType(Builder $query, $type)
    {
        return $query->where('name', '=', $type)->first();
    }

    /**
     * Load tag accessible to user.
     *
     * @param Builder $query
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeAccessibleToUser(Builder $query, User $user)
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('role_limit', '<=', $user->role_id);
            $query->orWhere('role_limit', '=', null);
        });
    }

    /**
     * Load tag accessible to logged in user.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeAccessibleToLoggedUser(Builder $query)
    {
        return $this->scopeAccessibleToUser($query, $this->getLoggedUser());
    }

    abstract public function getLoggedUser();
}
