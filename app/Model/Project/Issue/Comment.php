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

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\ModelAbstract;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\User;

/**
 * Comment is model class for project issue comments.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 * @property int $issue_id
 * @property int $project_id
 * @property string $comment
 * @property int $created_by
 * @property User $user
 * @property Issue $issue
 * @property Project $project
 * @property Collection $attachments
 * @property User\Activity $activity
 * @property Queue $messagesQueue
 */
class Comment extends ModelAbstract
{
    use CommentRelations;

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
     * @param User|null $user
     *
     * @return \Tinyissue\Repository\Project\Issue\Comment\Updater
     */
    public function updater(User $user = null)
    {
        return parent::updater($user);
    }
}
