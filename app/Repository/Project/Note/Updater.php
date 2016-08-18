<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project\Note;

use Tinyissue\Model;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;
use Tinyissue\Repository\RepositoryUpdater;

class Updater extends RepositoryUpdater
{
    /**
     * @var Project\Note
     */
    protected $model;

    public function __construct(Project\Note $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new note.
     *
     * @param array $input
     *
     * @return Project\Note
     */
    public function create(array $input)
    {
        $this->model->body       = $input['note_body'];
        $this->model->project_id = $this->model->project->id;
        $this->model->created_by = $this->model->createdBy->id;

        // Add event on successful save
        Project\Note::saved(function (Project\Note $note) {
            $this->queueAdd($note, $this->user);
        });

        $this->model->save();

        // Add to user's activity log
        $this->saveToActivity([
            'type_id'   => Model\Activity::TYPE_NOTE,
            'parent_id' => $this->model->project->id,
            'user_id'   => $this->model->createdBy->id,
        ]);

        return $this->model;
    }

    /**
     * Update the note body.
     *
     * @param string $body
     *
     * @return Project\Note
     */
    public function updateBody($body)
    {
        $this->model->body = $body;

        // Add event on successful save
        Project\Note::saved(function (Project\Note $note) {
            $this->queueUpdate($note, $this->user);
        });

        return $this->model->save();
    }

    /**
     * Delete a note.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        $this->model->activity()->delete();

        // Add event on successful delete
        Project\Note::deleted(function (Project\Note $note) {
            $this->queueDelete($note, $this->user);
        });

        return $this->model->delete();
    }

    /**
     * Insert add note to message queue.
     *
     * @param Project\Note $note
     * @param User         $changeBy
     *
     * @return void
     */
    public function queueAdd(Project\Note $note, User $changeBy)
    {
        return (new Queue())->updater($changeBy)->queue(Queue::ADD_NOTE, $note, $changeBy);
    }

    /**
     * Insert update note to message queue.
     *
     * @param Project\Note $note
     * @param User         $changeBy
     *
     * @return void
     */
    public function queueUpdate(Project\Note $note, User $changeBy)
    {
        // Skip message if nothing changed in note
        if (!$note->isDirty()) {
            return;
        }

        return (new Queue())->updater($changeBy)->queue(Queue::UPDATE_NOTE, $note, $changeBy);
    }

    /**
     * Insert delete note to message queue.
     *
     * @param Project\Note $note
     * @param User         $changeBy
     *
     * @return void
     */
    public function queueDelete(Project\Note $note, User $changeBy)
    {
        return (new Queue())->updater($changeBy)->queueDelete(Queue::DELETE_NOTE, $note, $changeBy);
    }
}
