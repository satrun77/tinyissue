<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Project;

use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model;

/**
 * NoteRelations is trait class containing the relationship methods for the Project\Note model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait NoteRelations
{
    /**
     * Note created by a user.
     *
     * @return Model\User
     */
    public function createdBy()
    {
        return $this->belongsTo(Model\User::class, 'created_by');
    }

    /**
     * Note belong to a project.
     *
     * @return Model\Project
     */
    public function project()
    {
        return $this->belongsTo(Model\Project::class, 'project_id');
    }

    /**
     * Note has a user activity record.
     *
     * @return Model\User\Activity
     */
    public function activity()
    {
        return $this
            ->hasOne(Model\User\Activity::class, 'action_id')
            ->where('type_id', '=', Model\Activity::TYPE_NOTE);
    }

    /**
     * Note can have many messages queue.
     *
     * @return Model\Message\Queue
     */
    public function messagesQueue()
    {
        return $this->morphMany(Model\Message\Queue::class, 'model');
    }

    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    abstract public function hasOne($related, $foreignKey = null, $localKey = null);

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);
}
