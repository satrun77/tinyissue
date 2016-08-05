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

use Illuminate\Database\Eloquent\Model as BaseModel;
use Tinyissue\Model\Permission;
use Tinyissue\Model\Traits\Project\Issue\Comment\CrudTrait;
use Tinyissue\Model\Traits\Project\Issue\Comment\QueueTrait;
use Tinyissue\Model\Traits\Project\Issue\Comment\RelationTrait;
use Tinyissue\Model\User;

/**
 * Comment is model class for project issue comments.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int    $id
 * @property int    $issue_id
 * @property int    $project_id
 * @property string $comment
 * @property int    $created_by
 */
class Comment extends BaseModel
{
    use CrudTrait,
        RelationTrait,
        QueueTrait;

    /**
     * Timestamp enabled.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'projects_issues_comments';

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    protected $fillable = [
        'created_by',
        'project_id',
        'issue_id',
        'comment',
    ];

    /**
     * Whether a user can view the issue.
     *
     * @param User $user
     *
     * @return bool
     */
    public function canView(User $user)
    {
        return $this->issue->canView($user);
    }

    /**
     * Whether a user can edit the comment.
     *
     * @param User $user
     *
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->id === $this->created_by || ($this->canView($user) && $user->permission(Permission::PERM_ISSUE_MODIFY));
    }
}
