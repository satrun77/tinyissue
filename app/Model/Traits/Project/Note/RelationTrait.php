<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Note;

use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model;

/**
 * RelationTrait is trait class containing the relationship methods for the Project\Note model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Relations\BelongsTo belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
 * @method Relations\HasOne    hasOne($related, $foreignKey = null, $localKey = null)
 */
trait RelationTrait
{
    /**
     * Note created by a user.
     *
     * @return Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'created_by');
    }

    /**
     * Note belong to a project.
     *
     * @return Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project', 'project_id');
    }

    /**
     * Note has a user activity record.
     *
     * @return Relations\HasOne
     */
    public function activity()
    {
        return $this
            ->hasOne('Tinyissue\Model\User\Activity', 'action_id')
            ->where('type_id', '=', Model\Activity::TYPE_NOTE);
    }
}
