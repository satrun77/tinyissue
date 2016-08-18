<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Message;

use Tinyissue\Model;

/**
 * QueueRelations is trait class containing the relationship methods for the message queue model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait QueueRelations
{
    /**
     * @return Model\User
     */
    public function changeBy()
    {
        return $this->belongsTo(Model\User::class, 'change_by_id');
    }

    /**
     * @return mixed
     */
    public function model()
    {
        return $this->morphTo();
    }

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);
    abstract public function morphTo($name = null, $type = null, $id = null);
}
