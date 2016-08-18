<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project\Issue;

use Tinyissue\Model\Project\Issue;
use Tinyissue\Repository\Repository;

class Counter extends Repository
{
    /**
     * @var Issue
     */
    protected $model;

    public function __construct(Issue $model)
    {
        $this->model = $model;
    }

    /**
     * Count number of open issues.
     *
     * @return int
     */
    public function countOpenIssues()
    {
        return $this->model->open()->count();
    }

    /**
     * Count number of closed issues.
     *
     * @return int
     */
    public function countClosedIssues()
    {
        return $this->model->closed()->count();
    }
}
