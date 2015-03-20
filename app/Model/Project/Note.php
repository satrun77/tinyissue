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

use Illuminate\Database\Eloquent\Model as BaseModel;
use Tinyissue\Model;
use Illuminate\Database\Query;

/**
 * Note is model class for project notes
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @property int           $id
 * @property int           $project_id
 * @property int           $created_by
 * @property string        $body
 * @property Model\Project $project
 * @property Model\User    $createdBy
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Note extends BaseModel
{
    public $timestamps = true;
    protected $table = 'projects_notes';
    protected $fillable = ['project_id', 'created_by', 'body'];

    /**
     * Generate a URL for the project note
     *
     * @return string
     */
    public function to()
    {
        return \URL::to('project/' . $this->project->id . '/notes#note' . $this->id);
    }

    /**
     * Note created by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'created_by');
    }

    /**
     * Note belong to a project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project', 'project_id');
    }

    /**
     * Create a new note
     *
     * @param array $input
     *
     * @return $this
     */
    public function createNote(array $input)
    {
        $this->body = $input['note_body'];
        $this->project_id = $this->project->id;
        $this->created_by = $this->createdBy->id;
        $this->save();

        // Add to user's activity log
        $this->activity()->save(new Model\User\Activity([
            'type_id'   => Model\Activity::TYPE_NOTE,
            'parent_id' => $this->project->id,
            'user_id'   => $this->createdBy->id
        ]));

        return $this;
    }

    /**
     * Note has a user activity record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activity()
    {
        return $this->hasOne('Tinyissue\Model\User\Activity', 'action_id')->where('type_id', '=', Model\Activity::TYPE_NOTE);
    }

    /**
     * Delete a note
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        $this->activity()->delete();

        return parent::delete();
    }
}
