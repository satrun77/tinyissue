<?php

namespace Tinyissue\Model\Project;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\User\Activity as UserActivity;
use Tinyissue\Model\Activity;
use Tinyissue\Model\Project\Issue\Attachment;

class Issue extends Model
{
    protected $table = 'projects_issues';
    public $timestamps = true;
    protected $fillable = array('created_by', 'project_id', 'title', 'body', 'assigned_to');

    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 0;

    /**
     * An issue has one user assigned to (inverse relationship of User::issues).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigned()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'assigned_to');
    }

    /**
     * An issue has one user updated by (inverse relationship of User::issuesUpdatedBy).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'updated_by');
    }

    /**
     * An issue has one user closed it (inverse relationship of User::issuesClosedBy).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function closer()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'closed_by');
    }

    /**
     * An issue has one user created it (inverse relationship of User::issuesCreatedBy).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'created_by');
    }

    //----
    public function activities()
    {
        return $this->hasMany('Tinyissue\Model\User\Activity', 'item_id')->orderBy('created_at', 'ASC');
    }

    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project');
    }

    public function comments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Comment', 'issue_id')
                        ->orderBy('created_at', 'ASC');
    }

    public function attachments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Attachment', 'issue_id')->where('comment_id', '=', 0);
    }
    //----

    public function countComments()
    {
        return $this->hasOne('Tinyissue\Model\Project\Issue\Comment', 'issue_id')
                        ->selectRaw('issue_id, count(*) as aggregate')
                        ->groupBy('issue_id')
        ;
    }

    public function getCountCommentsAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('countComments', $this->relations)) {
            $this->load('countComments');
        }

        $related = $this->getRelation('countComments');

        // then return the count directly
        return (isset($related->aggregate)) ? (int) $related->aggregate : 0;
    }

    public function changeUpdatedBy($userId)
    {
        $time = new \DateTime();
        $this->updated_at = $time->format('Y-m-d H:i:s');
        $this->updated_by = $userId;

        return $this->save();
    }

    /**
     * Generate a URL for the active project.
     *
     * @param string $url
     *
     * @return string
     */
    public function to($url = '')
    {
        return \URL::to('project/'.$this->project_id.'/issue/'.$this->id.(($url) ? '/'.$url : ''));
    }

    /**
     * Reassign the issue to a new user.
     *
     * @param int $user_id
     */
    public function reassign($userId)
    {
        $this->assigned_to = $userId;
        $this->save();

        return $this->activities()->save(new UserActivity([
                    'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
                    'parent_id' => $this->project->id,
                    'user_id'   => \Auth::user()->id,
                    'action_id' => $this->assigned_to,
        ]));
    }

    /**
     * Change the status of an issue.
     *
     * @param int $status
     */
    public function changeStatus($status, $userId)
    {
        if ($status == 0) {
            $time = new \DateTime();
            $this->closed_by = $userId;
            $this->closed_at = $time->format('Y-m-d H:i:s');

            $activityType = Activity::TYPE_CLOSE_ISSUE;
        } else {
            $activityType = Activity::TYPE_REOPEN_ISSUE;
        }

        /* Add to activity log */
        $this->activities()->save(new UserActivity([
            'type_id'   => $activityType,
            'parent_id' => $this->project->id,
            'user_id'   => $userId,
        ]));

        $this->status = $status;

        return $this->save();
    }

    /**
     * Update the given issue.
     *
     * @param array $input
     *
     * @return array
     */
    public function updateIssue($input, $userId)
    {
        $fill = array(
            'title'       => $input['title'],
            'body'        => $input['body'],
            'assigned_to' => $input['assigned_to'],
        );

        /* Add to activity log for assignment if changed */
        if ($input['assigned_to'] != $this->assigned_to) {
            $this->activities()->save(new UserActivity([
                'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
                'parent_id' => $this->project->id,
                'user_id'   => $userId,
                'action_id' => $this->assigned_to,
            ]));
        }

        $this->fill($fill);

        return $this->save();
    }

    /**
     * Create a new issue.
     *
     * @param array    $input
     * @param \Project $project
     *
     * @return Issue
     */
    public function createIssue(array $input, $userId)
    {
        $fill = array(
            'created_by' => $userId,
            'project_id' => $this->project->id,
            'title'      => $input['title'],
            'body'       => $input['body'],
        );

        if (\Auth::user()->permission('issue-modify')) {
            $fill['assigned_to'] = $input['assigned_to'];
        }

        $this->fill($fill)->save();

        /* Add to user's activity log */
        $this->activities()->save(new UserActivity([
            'type_id'   => Activity::TYPE_CREATE_ISSUE,
            'parent_id' => $this->project->id,
            'user_id'   => $userId,
        ]));

        /* Add attachments to issue */
        Attachment::where('upload_token', '=', $input['upload_token'])
                ->where('uploaded_by', '=', $userId)
                ->update(array('issue_id' => $this->id));

        return $this;
    }

    public function changeProject($projectId)
    {
        $this->project_id = $projectId;
        $this->save();
        $comments = $this->comments()->get();
        foreach ($comments as $comment) {
            $comment->project_id = $projectId;
            $comment->save();
        }

        $activities = $this->activities()->get();
        foreach ($activities as $activity) {
            $activity->parent_id = $projectId;
            $activity->save();
        }

        return $this;
    }
}
