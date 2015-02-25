<?php

namespace Tinyissue\Model\User;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Project;

class Activity extends Model
{
    protected $table = 'users_activity';
    public $timestamps = true;
    protected $fillable = array('type_id', 'parent_id', 'user_id', 'item_id', 'action_id', 'data');

    public function issue()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue', 'item_id');
    }

    public function projectIssue()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue', 'project_id');
    }

    public function user()
    {
        return $this->belongsTo('\Tinyissue\Model\User', 'user_id');
    }

    public function assignTo()
    {
        return $this->belongsTo('\Tinyissue\Model\User', 'action_id');
    }

    public function activity()
    {
        return $this->belongsTo('Tinyissue\Model\Activity', 'type_id');
    }

    public function comment()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue\Comment', 'action_id');
    }

    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project', 'parent_id');
    }

    public function note()
    {
        return $this->belongsTo('\Tinyissue\Model\Project\Note', 'action_id');
    }

    public static function projectActivity(Project $project, $limit = 5)
    {
        return UserActivity::with('activity', 'issue', 'user', 'comment', 'assignTo')
                        ->where('parent_id', '=', $project->id)
                        ->orderBy('created_at', 'DESC')
                        ->take($limit)
                        ->get();
    }
}
