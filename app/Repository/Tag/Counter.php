<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Tag;

use Tinyissue\Model\Tag;
use Tinyissue\Repository\Repository;

class Counter extends Repository
{
    /**
     * @var Tag
     */
    protected $model;

    public function __construct(Tag $model)
    {
        $this->model = $model;
    }

    /**
     * Count number of tags.
     *
     * @return int
     */
    public function countNumberOfTags()
    {
        return $this->model->notGroup()->count();
    }
}
