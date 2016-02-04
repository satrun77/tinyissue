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
 * DataMappingTrait is trait class containing methods to manipulate the data of the Tag model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait DataMappingTrait
{
    /**
     * Callback to return tag details for JS library tokenfield.
     *
     * @param Tag $tag
     *
     * @return array
     */
    public function tokenFieldCallback(Tag $tag)
    {
        return array_combine([
            'value',
            'label',
            'bgcolor',
        ], $this->toArray($tag));
    }

    /**
     * Callback to return tag details as array.
     *
     * @param Tag $tag
     *
     * @return array
     */
    public function toArrayCallback(Tag $tag)
    {
        return [
            'id'      => $tag->id,
            'name'    => $tag->fullname,
            'bgcolor' => $tag->bgcolor,
        ];
    }
}
