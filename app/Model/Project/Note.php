<?php

namespace Tinyissue\Model\Project;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Activity;
use Tinyissue\Model\User\Activity as UserActivity;

class Note extends Model
{
    protected $table      = 'projects_notes';
    public    $timestamps = true;
    protected $fillable   = ['project_id', 'created_by', 'body'];

    /**
     * Generate a URL for the project note
     *
     * @return string
     */
    public function to()
    {
        return \URL::to('project/' . $this->project->id . '/notes#note' . $this->id);
    }

    public function createdBy()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'created_by');
    }

    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project', 'project_id');
    }

    public function activity()
    {
        return $this->hasOne('Tinyissue\Model\User\Activity', 'action_id')->where('type_id', '=', 6);
    }

    public function createNote($input)
    {
        $this->body = $input['note_body'];
        $this->project_id = $this->project->id;
        $this->created_by = $this->createdBy->id;
        $this->save();

        // Add to user's activity log
        $this->activity()->save(new UserActivity([
            'type_id' => Activity::TYPE_NOTE,
            'parent_id' => $this->project->id,
            'user_id' => $this->createdBy->id
        ]));

        return $this;
    }

    public function delete()
    {
        $this->activity()->delete();

        return parent::delete();
    }
}