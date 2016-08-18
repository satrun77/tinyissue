<?php
/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Extensions\Model;

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Tag;

/**
 * FetchTagsTrait is trait class contains method to set and fetch tags.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait FetchTagsTrait
{
    /**
     * Collection of all tags.
     *
     * @var Collection
     */
    protected $tags = null;

    /**
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    protected function getTags($type)
    {
        if ($this->tags === null) {
            $this->tags = Tag::instance()->getGroupWithTags();
        }

        return $this->tags->getByName($type)->tags;
    }
}
