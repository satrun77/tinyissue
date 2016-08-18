<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Message\Queue;

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Repository\Repository;

class Fetcher extends Repository
{
    /**
     * @var Queue
     */
    protected $model;

    public function __construct(Queue $model)
    {
        $this->model = $model;
    }

    /**
     * Get latest items in the messages queue.
     *
     * @return Collection
     */
    public function getLatestMessages()
    {
        return $this->model->with('changeBy', 'model')->latest()->get();
    }
}
