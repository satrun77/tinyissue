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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use Tinyissue\Model;

/**
 * IssueRelations is trait class containing the relationship methods for the Project\Issue model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait IssueRelations
{
    /**
     * An issue has one user assigned to (inverse relationship of User::issues).
     *
     * @return Model\User
     */
    public function assigned()
    {
        return $this->belongsTo(Model\User::class, 'assigned_to');
    }

    /**
     * An issue has one user updated by (inverse relationship of User::issuesUpdatedBy).
     *
     * @return Model\User
     */
    public function updatedBy()
    {
        return $this->belongsTo(Model\User::class, 'updated_by');
    }

    /**
     * An issue has one user closed it (inverse relationship of User::issuesClosedBy).
     *
     * @return Model\User
     */
    public function closer()
    {
        return $this->belongsTo(Model\User::class, 'closed_by');
    }

    /**
     * An issue has one user created it (inverse relationship of User::issuesCreatedBy).
     *
     * @return Model\User
     */
    public function user()
    {
        return $this->belongsTo(Model\User::class, 'created_by');
    }

    /**
     * Issue belong to a project.
     *
     * @return Model\Project
     */
    public function project()
    {
        return $this->belongsTo(Model\Project::class);
    }

    /**
     * Issue can have many attachments.
     *
     * @return Model\Project\Issue\Attachment
     */
    public function attachments()
    {
        return $this
            ->hasMany(Model\Project\Issue\Attachment::class, 'issue_id')
            ->where(function (Builder $query) {
                $query->where('comment_id', '=', 0);
                $query->orWhere('comment_id', '=', null);
            });
    }

    /**
     * Issue have many users activities.
     *
     * @return Model\User\Activity
     */
    public function activities()
    {
        return $this
            ->hasMany(Model\User\Activity::class, 'item_id')
            ->orderBy('created_at', 'ASC');
    }

    /**
     * Issue have many users activities (all except comments).
     *
     * @return Model\User\Activity
     */
    public function generalActivities()
    {
        return $this
            ->hasMany(Model\User\Activity::class, 'item_id')
            ->whereNotIn('type_id', [Model\Activity::TYPE_COMMENT])
            ->orderBy('created_at', 'ASC')
            ->with('activity', 'user', 'assignTo');
    }

    /**
     * Issue have many users activities (comments).
     *
     * @return Model\User\Activity
     */
    public function commentActivities()
    {
        return $this
            ->hasMany(Model\User\Activity::class, 'item_id')
            ->whereIn('type_id', [Model\Activity::TYPE_COMMENT])
            ->orderBy('created_at', 'ASC')
            ->with('activity', 'user', 'comment', 'assignTo', 'comment.attachments');
    }

    /**
     * Issue have many tags.
     *
     * @return Model\Tag
     */
    public function tags()
    {
        return $this->belongsToMany(Model\Tag::class, 'projects_issues_tags', 'issue_id', 'tag_id')
            ->join('tags as p', 'p.id', '=', 'tags.parent_id')
            ->orderBy('parent_id');
    }

    /**
     * Issue have many comments.
     *
     * @return Model\Project\Issue\Comment
     */
    public function comments()
    {
        return $this
            ->hasMany(Model\Project\Issue\Comment::class, 'issue_id')
            ->orderBy('created_at', 'ASC');
    }

    /**
     * Issue can have many messages queue.
     *
     * @return Model\Message\Queue
     */
    public function messagesQueue()
    {
        return $this->morphMany(Model\Message\Queue::class, 'model');
    }

    /**
     * Count number of comments in an issue.
     *
     * @return Model\Project\Issue\Comment
     */
    public function countComments()
    {
        return $this->hasOne(Model\Project\Issue\Comment::class, 'issue_id')
            ->selectRaw('issue_id, count(*) as aggregate')
            ->groupBy('issue_id');
    }

    abstract public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null);
    abstract public function hasMany($related, $foreignKey = null, $localKey = null);
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);
    abstract public function hasOne($related, $foreignKey = null, $localKey = null);
    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);
}
