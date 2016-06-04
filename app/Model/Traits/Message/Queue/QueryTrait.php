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
use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model\Message;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;
use Tinyissue\Model\Message\Queue;

/**
 * QueryTrait is trait class containing the database queries methods for the message queue model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Relations\BelongsToMany tags()
 */
trait QueryTrait
{
    /**
     * Check whether an issue has registered add new issue in the queue.
     *
     * @param Model $model
     * @param User  $user
     *
     * @return bool
     */
    public function isModelCreatedByUser(Model $model, User $user)
    {
        return $this->where('event', '=', $this->getAddEventNameFromModel($model))
            ->where('model_id', '=', $model->id)
            ->where('model_type', '=', get_class($model))
            ->where('change_by_id', '=', $user->id)
            ->first();
    }

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
