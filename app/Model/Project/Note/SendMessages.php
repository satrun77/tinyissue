<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Project\Note;

use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Message\Queue;
use Illuminate\Support\Collection;
use Tinyissue\Model\User;
use Tinyissue\Services\SendMessagesAbstract;

/**
 * SendMessages is a class to manage & process of sending messages about note changes.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class SendMessages extends SendMessagesAbstract
{
    protected $template = 'update_note';

    /**
     * Returns the subject text.
     *
     * @return string
     */
    protected function getSubject()
    {
        return $this->getProject()->name;
    }

    /**
     * Note: changes is belongs to the project (no issue here).
     *
     * @return bool
     */
    protected function getIssue()
    {
        return false;
    }

    /**
     * Returns an instance of Project.
     *
     * @return Project
     */
    protected function getProject()
    {
        if ($this->getModel()) {
            return $this->getModel()->project;
        }

        // Possible deleted note
        if (null === $this->project) {
            $projectId     = $this->latestMessage->getDataFromPayload('origin.project_id');
            $this->project = (new Project())->find($projectId);
        }

        return $this->project;
    }

    /**
     * Returns the project id.
     *
     * @return int
     */
    protected function getProjectId()
    {
        return $this->getProject()->id;
    }

    /**
     * Returns message data belongs to adding a note.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForAddNote(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' added a note';
        $messageData['changes']['note'] = [
            'noLabel' => true,
            'date'    => $this->getModel()->created_at,
            'now'     => \Html::format($this->getModel()->body),
        ];

        return $messageData;
    }

    /**
     * Returns message data belongs to updating a note.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForUpdateNote(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' updated a note in ' . link_to($this->getProject()->to(), $this->getProject()->name);
        $messageData['changes']['note'] = [
            'noLabel' => true,
            'date'    => $queue->getDataFromPayload('origin.updated_at'),
            'was'     => \Html::format($queue->getDataFromPayload('origin.body')),
            'now'     => \Html::format($queue->getDataFromPayload('dirty.body')),
        ];

        return $messageData;
    }

    /**
     * Returns message data belongs to deleting a note.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForDeleteNote(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' deleted a note in ' . link_to($this->getProject()->to(), $this->getProject()->name);
        $messageData['changes']['note'] = [
            'noLabel' => true,
            'date'    => $queue->created_at,
            'was'     => \Html::format($queue->getDataFromPayload('origin.body')),
            'now'     => '',
        ];

        return $messageData;
    }

    /**
     * Return text to be used for the message heading.
     *
     * @param Queue           $queue
     * @param Collection|null $changes
     *
     * @return string
     */
    protected function getMessageHeading(Queue $queue, Collection $changes = null)
    {
        $heading = parent::getMessageHeading($queue, $changes);
        $heading .= ' updated a note in ' . link_to($this->getProject()->to(), '#' . $this->getProjectId());

        return $heading;
    }

    /**
     * Check that the project is loaded and the note is belongs to the project.
     *
     * @return bool
     */
    protected function validateData()
    {
        return $this->getProject() && $this->getModel() && (int) $this->getModel()->project_id === (int) $this->getProject()->id;
    }

    /**
     * Check if the latest message is about deleting a note.
     *
     * @return bool
     */
    public function isStatusMessage()
    {
        return $this->latestMessage->event === Queue::DELETE_NOTE;
    }
}
