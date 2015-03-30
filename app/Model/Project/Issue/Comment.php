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
use Illuminate\Database\Eloquent\Model as BaseModel;
use Tinyissue\Model;
use Illuminate\Database\Query;

/**
 * Comment is model class for project issue comments
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @property int                    $id
 * @property int                    $issue_id
 * @property int                    $project_id
 * @property string                 $comment
 * @property int                    $created_by
 * @property Model\Project          $project
 * @property Model\Project\Issue    $issue
 * @property Model\User             $user
 * @property Collection             $attachments
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Comment extends BaseModel
{
    public $timestamps = true;
    protected $table = 'projects_issues_comments';
    protected $fillable = [
        'created_by',
        'project_id',
        'issue_id',
        'comment',
    ];

    /**
     * A comment has one user (inverse relationship of User::comments).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('\Tinyissue\Model\User', 'created_by');
    }


    /**
     * Comment can have many attachments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Attachment', 'comment_id');
    }

    /**
     * Create new comment
     *
     * @param array $input
     *
     * @return $this
     */
    public function createComment(array $input)
    {
        $fill = [
            'created_by' => $this->user->id,
            'project_id' => $this->project->id,
            'issue_id'   => $this->issue->id,
            'comment'    => $input['comment'],
        ];

        $this->fill($fill);
        $this->save();

        /* Add to user's activity log */
        $this->activity()->save(new Model\User\Activity([
            'type_id'   => Model\Activity::TYPE_COMMENT,
            'parent_id' => $this->project->id,
            'item_id'   => $this->issue->id,
            'user_id'   => $this->user->id,
        ]));

        /* Add attachments to issue */
        Attachment::where('upload_token', '=', $input['upload_token'])
            ->where('uploaded_by', '=', $this->user->id)
            ->update(['issue_id' => $this->issue->id, 'comment_id' => $this->id]);

        /* Update the project */
        $this->issue->changeUpdatedBy($this->user->id);

        return $this;
    }

    /**
     * Comment can have one activity
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activity()
    {
        return $this->hasOne('Tinyissue\Model\User\Activity', 'action_id');
    }

    /**
     * Delete a comment and its attachments
     *
     * @return bool|null
     * @throws \Exception
     */
    public function deleteComment()
    {
        $this->activity()->delete();

        foreach ($this->attachments as $attachment) {
            $path = config('filesystems.disks.local.root') . '/' . config('tinyissue.uploads_dir') . '/' . $this->project_id . '/' . $attachment->upload_token;
            $attachment->deleteFile($path, $attachment->filename);
            $attachment->delete();
        }

        return $this->delete();
    }
}
