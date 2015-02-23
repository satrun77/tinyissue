<?php

namespace Tinyissue\Model\Project\Issue;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Activity;
use Tinyissue\Model\User\Activity as UserActivity;

class Comment extends Model
{
    public $timestamps = true;
    protected $table = 'projects_issues_comments';
    protected $fillable = array(
        'created_by',
        'project_id',
        'issue_id',
        'comment',
    );

    /**
     * A comment has one user (inverse relationship of User::comments).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('\Tinyissue\Model\User', 'created_by');
    }

    public function attachments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Attachment', 'comment_id');
    }

    //----

    /**
     * Create a new comment.
     *
     * @param array          $input
     * @param \Project       $project
     * @param \Project\Issue $issue
     *
     * @return Comment
     */
    public function createComment($input, $userId)
    {
        $fill = array(
            'created_by' => $userId,
            'project_id' => $this->project->id,
            'issue_id' => $this->issue->id,
            'comment' => $input['comment'],
        );

        $this->fill($fill);
        $this->save();

        /* Add to user's activity log */
        $this->activity()->save(new UserActivity([
            'type_id' => Activity::TYPE_COMMENT,
            'parent_id' => $this->project->id,
            'item_id' => $this->issue->id,
            'user_id' => $userId,
        ]));

        /* Add attachments to issue */
        Attachment::where('upload_token', '=', $input['upload_token'])
            ->where('uploaded_by', '=', $userId)
            ->update(array('issue_id' => $this->issue->id, 'comment_id' => $this->id));

        /* Update the project */
        $this->issue->changeUpdatedBy($userId);

        return $this;
    }
    ///----

    public function activity()
    {
        return $this->hasOne('Tinyissue\Model\User\Activity', 'action_id');
    }

    /**
     * Delete a comment and its attachments.
     *
     * @param int $comment
     *
     * @return bool
     */
    public function deleteComment()
    {
        $this->activity()->delete();

        foreach ($this->attachments as $attachment) {
            $path = config('filesystems.disks.local.root').'/uploads/'.$this->project_id.'/'.$attachment->upload_token;
            $attachment->deleteFile($path, $attachment->filename);
            $attachment->delete();
        }

        return $this->delete();
    }
}
