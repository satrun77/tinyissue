<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Message\Queue;

use Illuminate\Database\Eloquent\Eloquent;

/**
 * QueryTrait is trait class containing the database queries methods for the message queue model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait QueryTrait
{
    /**
     * Get the latest messages queue.
     * 
     * @return mixed
     */
    public function latestMessages()
    {
        return $this->with('changeBy', 'model')->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');
    }
}
