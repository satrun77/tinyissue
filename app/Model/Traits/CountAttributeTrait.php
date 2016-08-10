<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits;

/**
 * CountAttributeTrait is trait class for adding method to return count attribute.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait CountAttributeTrait
{
    /**
     * Returns the aggregate value of a field.
     *
     * @param string $field
     *
     * @return int
     */
    protected function getCountAttribute($field)
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists($field, $this->relations)) {
            $this->load($field);
        }

        $related = $this->getRelation($field);

        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }
}
