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
 * UserRelations is trait class containing the relationship methods for the Project\User model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait UserRelations
{
    /**
     * Returns the instance of the user in the project.
     *
     * @return Model\User
     */
    public function user()
    {
        return $this->belongsTo(Model\User::class, 'user_id')->orderBy('firstname', 'ASC');
    }

    /**
     * Returns the instance of the message in the project.
     *
     * @return Model\Message
     */
    public function message()
    {
        return $this->belongsTo(Model\Message::class, 'message_id');
    }

    /**
     * Returns the instance of the project.
     *
     * @return Model\Project
     */
    public function project()
    {
        return $this->belongsTo(Model\Project::class, 'project_id')->orderBy('name', 'ASC');
    }

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);
}
