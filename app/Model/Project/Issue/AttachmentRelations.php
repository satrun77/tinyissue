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
 * AttachmentRelations is trait class containing the relationship methods for the Project\Issue\Attachment model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait AttachmentRelations
{
    /**
     * An attachment is belong to one issue  (inverse relationship of Project\Issue::attachments).
     *
     * @return Model\Project\Issue
     */
    public function issue()
    {
        return $this->belongsTo(Model\Project\Issue::class, 'issue_id');
    }

    /**
     * An attachment has one user upladed to (inverse relationship of User::attachments).
     *
     * @return Model\User
     */
    public function user()
    {
        return $this->belongsTo(Model\User::class, 'uploaded_by');
    }

    /**
     * An attachment can belong to a comment (inverse relationship of Comments::attachments).
     *
     * @return Model\Project\Issue\Comment
     */
    public function comment()
    {
        return $this->belongsTo(Model\Project\Issue\Comment::class, 'comment_id');
    }

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);
}
