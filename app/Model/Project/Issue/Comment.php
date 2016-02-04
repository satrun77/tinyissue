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
use Tinyissue\Model\Traits\Project\Issue\Comment\CrudTrait;
use Tinyissue\Model\Traits\Project\Issue\Comment\RelationTrait;

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
        RelationTrait;

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
}
