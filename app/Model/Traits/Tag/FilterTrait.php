<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Tag;

use Tinyissue\Model\Tag;

/**
 * FilterTrait is trait class containing the methods for filtering database queries of the Tag model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait FilterTrait
{
    /**
     * Callback to check if a tag name is not 'open'
     *
     * @param Tag $tag
     *
     * @return bool
     */
    public function tagsExceptStatusOpenCallback(Tag $tag)
    {
        return $tag->name !== Tag::STATUS_OPEN;
    }

    /**
     * Callback to check if tag name is 'open'
     *
     * @param int $index
     * @param Tag $tag
     *
     * @return bool
     */
    public function onlyStatusOpenCallback($index, Tag $tag)
    {
        return $tag->name === Tag::STATUS_OPEN;
    }
}
