<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Project\Issue;

use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model;

/**
 * CommentRelations is trait class containing the relationship methods for the Project\Issue\Comment model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait CommentRelations
{
    /**
     * A comment has one user (inverse relationship of User::comments).
     *
     * @return Model\User
     */
    public function user()
    {
        return $this->belongsTo(Model\User::class, 'created_by');
    }

    /**
     * A comment has belongs to an issue.
     *
     * @return Model\Project\Issue
     */
    public function issue()
    {
        return $this->belongsTo(Model\Project\Issue::class, 'issue_id');
    }

    /**
     * Comment can have many attachments.
     *
     * @return Model\Project\Issue\Attachment
     */
    public function attachments()
    {
        return $this->hasMany(Model\Project\Issue\Attachment::class, 'comment_id');
    }

    /**
     * Comment can have one activity.
     *
     * @return Model\User\Activity
     */
    public function activity()
    {
        return $this->hasOne(Model\User\Activity::class, 'action_id');
    }

    /**
     * Comment can have many messages queue.
     *
     * @return Model\Message\Queue
     */
    public function messagesQueue()
    {
        return $this->morphMany(Model\Message\Queue::class, 'model');
    }

    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    abstract public function hasOne($related, $foreignKey = null, $localKey = null);

    abstract public function hasMany($related, $foreignKey = null, $localKey = null);

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);
}
