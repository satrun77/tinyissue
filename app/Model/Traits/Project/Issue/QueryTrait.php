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
use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model\Tag;

/**
 * QueryTrait is trait class containing the database queries methods for the Project|Issue model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Relations\BelongsToMany tags()
 */
trait QueryTrait
{
    /**
     * Returns the status tag.
     *
     * @return Tag
     */
    public function getStatusTag()
    {
        return $this->getTagOfGroup(Tag::GROUP_STATUS);
    }

    /**
     * Returns the type tag.
     *
     * @return Tag
     */
    public function getTypeTag()
    {
        return $this->getTagOfGroup(Tag::GROUP_TYPE);
    }

    /**
     * Returns the resolution tag.
     *
     * @return Tag
     */
    public function getResolutionTag()
    {
        return $this->getTagOfGroup(Tag::GROUP_RESOLUTION);
    }

    /**
     * Return tag by it group name.
     *
     * @param string $group
     *
     * @return Tag
     */
    protected function getTagOfGroup($group)
    {
        return $this->tags
            ->where('parent.name', $group)
            ->first();
    }
}
