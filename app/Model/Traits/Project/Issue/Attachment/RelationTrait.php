<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Issue\Attachment;

use Illuminate\Database\Eloquent\Relations;

/**
 * RelationTrait is trait class containing the relationship methods for the Project\Issue\Attachment model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Relations\BelongsTo belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
 */
trait RelationTrait
{
    /**
     * An attachment is belong to one issue  (inverse relationship of Project\Issue::attachments).
     *
     * @return Relations\BelongsTo
     */
    public function issue()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue', 'issue_id');
    }

    /**
     * An attachment has one user upladed to (inverse relationship of User::attachments).
     *
     * @return Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'uploaded_by');
    }

    /**
     * An attachment can belong to a comment (inverse relationship of Comments::attachments).
     *
     * @return Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue\Comment', 'comment_id');
    }
}
