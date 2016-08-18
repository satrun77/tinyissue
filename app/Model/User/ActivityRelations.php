<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\User;

use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model;

/**
 * ActivityRelations is trait class containing the relationship method for the User\Activity model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait ActivityRelations
{
    /**
     * Returns the project issue this activity is belongs to by the item_id, which can hold the issue id.
     *
     * @return Model\Project\Issue
     */
    public function issue()
    {
        return $this->belongsTo(Model\Project\Issue::class, 'item_id');
    }

    /**
     * Returns the user this activity is belongs to.
     *
     * @return Model\User
     */
    public function user()
    {
        return $this->belongsTo(Model\User::class, 'user_id');
    }

    /**
     * Returns the user that was assigned to the issue. Only for reassign activity.
     *
     * @return Model\User
     */
    public function assignTo()
    {
        return $this->belongsTo(Model\User::class, 'action_id');
    }

    /**
     * User activity has one activity type.
     *
     * @return Model\Activity
     */
    public function activity()
    {
        return $this->belongsTo(Model\Activity::class, 'type_id');
    }

    /**
     * Returns the comment this activity belongs to if any.
     *
     * @return Model\Project\Issue\Comment
     */
    public function comment()
    {
        return $this->belongsTo(Model\Project\Issue\Comment::class, 'action_id');
    }

    /**
     * Returns the project his activity belongs to.
     *
     * @return Model\Project
     */
    public function project()
    {
        return $this->belongsTo(Model\Project::class, 'parent_id');
    }

    /**
     * Returns the note this activity belongs to if any.
     *
     * @return Model\Project\Note
     */
    public function note()
    {
        return $this->belongsTo(Model\Project\Note::class, 'action_id');
    }

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);
}
