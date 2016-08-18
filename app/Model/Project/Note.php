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

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model;
use Tinyissue\Model\ModelAbstract;

/**
 * Note is model class for project notes.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 * @property int $project_id
 * @property int $created_by
 * @property string $body
 * @property Model\Project $project
 * @property Model\User $createdBy
 * @property Model\User\Activity $activity
 * @property Collection $messagesQueue
 */
class Note extends ModelAbstract
{
    use NoteRelations;

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
    protected $table = 'projects_notes';

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    protected $fillable = ['project_id', 'created_by', 'body'];

    /**
     * @param Model\User|null $user
     *
     * @return \Tinyissue\Repository\Project\Note\Updater
     */
    public function updater(Model\User $user = null)
    {
        return parent::updater($user);
    }

    /**
     * Generate a URL for the project note.
     *
     * @return string
     */
    public function to()
    {
        return \URL::to('project/' . $this->project->id . '/notes#note' . $this->id);
    }
}
