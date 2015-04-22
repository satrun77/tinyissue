<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Issue;

use Illuminate\Database\Eloquent;
use Tinyissue\Model\Tag;

/**
 * QueryTrait is trait class containing the database queries methods for the Project|Issue model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method   Eloquent\Collection     tags()
 */
trait QueryTrait
{
    /**
     * Returns issue tags except for the status tags
     *
     * @return Eloquent\Collection
     */
    public function getTagsExceptStatus()
    {
        $statusGroup = Tag::where('name', '=', Tag::GROUP_STATUS)->first();

        return $this->tags()->where('parent_id', '!=', $statusGroup->id);
    }

    /**
     * Returns issue tags except for a specific tag
     *
     * @param string $tag
     *
     * @return Eloquent\Collection
     */
    public function getTagsExcept($tag)
    {
        return $this->tags()->where('name', '!=', $tag);
    }
}
