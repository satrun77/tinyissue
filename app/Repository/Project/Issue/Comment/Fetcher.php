<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project\Issue\Comment;

use Tinyissue\Model\Project\Issue\Comment;
use Tinyissue\Repository\Repository;

class Fetcher extends Repository
{
    public function __construct(Comment $model)
    {
        $this->model = $model;
    }
}
