<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Issue\Comment;

use Illuminate\Database\Eloquent\Relations;

/**
 * RelationTrait is trait class containing the relationship methods for the Project\Issue\Comment model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Relations\HasMany   hasMany($related, $foreignKey = null, $localKey = null)
 * @method Relations\BelongsTo belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
 * @method Relations\HasOne    hasOne($related, $foreignKey = null, $localKey = null)
 */
trait RelationTrait
{
    /**
     * A comment has one user (inverse relationship of User::comments).
     *
     * @return Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('\Tinyissue\Model\User', 'created_by');
    }

    /**
     * Comment can have many attachments.
     *
     * @return Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Attachment', 'comment_id');
    }

    /**
     * Comment can have one activity.
     *
     * @return Relations\HasOne
     */
    public function activity()
    {
        return $this->hasOne('Tinyissue\Model\User\Activity', 'action_id');
    }
}
