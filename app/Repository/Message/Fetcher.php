<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Message;

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Message;
use Tinyissue\Repository\Repository;

class Fetcher extends Repository
{
    /**
     * @var Message
     */
    protected $model;

    public function __construct(Message $model)
    {
        $this->model = $model;
    }

    /**
     * Get all messages.
     *
     * @return Collection
     */
    public function getAll()
    {
        return $this->model->orderBy('id', 'ASC')->get();
    }
}
