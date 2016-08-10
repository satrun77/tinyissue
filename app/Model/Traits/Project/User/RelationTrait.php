<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\User;

use Illuminate\Database\Eloquent\Relations;

/**
 * RelationTrait is trait class containing the relationship methods for the Project\User model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait RelationTrait
{
    /**
     * Returns the instance of the user in the project.
     *
     * @return Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'user_id')->orderBy('firstname', 'ASC');
    }

    /**
     * Returns the instance of the message in the project.
     *
     * @return Relations\BelongsTo
     */
    public function message()
    {
        return $this->belongsTo('Tinyissue\Model\Message', 'message_id');
    }

    /**
     * Returns the instance of the project.
     *
     * @return Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project', 'project_id')->orderBy('name', 'ASC');
    }

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);
}
