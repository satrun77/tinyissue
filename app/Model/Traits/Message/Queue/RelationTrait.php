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

use Illuminate\Database\Eloquent\Relations;

/**
 * RelationTrait is trait class containing the relationship methods for the message queue model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait RelationTrait
{
    /**
     * @return mixed
     */
    public function changeBy()
    {
        return $this->belongsTo('\Tinyissue\Model\User', 'change_by_id');
    }

    /**
     * @return mixed
     */
    public function model()
    {
        return $this->morphTo();
    }
}