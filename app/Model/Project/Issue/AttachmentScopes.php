<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Project\Issue;

use Illuminate\Database\Eloquent\Builder;
use Tinyissue\Model\User;

/**
 * AttachmentScopes is trait class containing the model scope methods.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait AttachmentScopes
{
    /**
     * Filter by uploaded by field.
     *
     * @param Builder  $query
     * @param User|int $userOrId
     *
     * @return Builder
     */
    public function scopeByUser(Builder $query, $userOrId)
    {
        $userId = $userOrId instanceof User ? $userOrId->id : $userOrId;

        return $query->where('uploaded_by', '=', $userId);
    }

    /**
     * Filter by upload token field.
     *
     * @param Builder $query
     * @param string  $token
     *
     * @return Builder
     */
    public function scopeForToken(Builder $query, $token)
    {
        return $query->where('upload_token', '=', $token);
    }

    /**
     * Filter by file name field.
     *
     * @param Builder $query
     * @param string  $name
     *
     * @return Builder
     */
    public function scopeFilename(Builder $query, $name)
    {
        return $query->where('filename', '=', $name);
    }
}
