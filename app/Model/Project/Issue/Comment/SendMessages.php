<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Project\Issue\Comment;

use Illuminate\Support\Collection;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Services\SendMessagesAbstract;

/**
 * SendMessages is a class to manage & process of sending messages about comment changes.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class SendMessages extends SendMessagesAbstract
{
    protected $template = 'update_comment';

    /**
     * Returns an instance of Issue.
     *
     * @return Issue|bool
     */
    protected function getIssue()
    {
        if (!$this->issue instanceof Issue) {
            if ($this->getModel()) {
                $this->issue = $this->getModel()->issue;
            } else {
                // Possible deleted comment
                $issueId     = $this->latestMessage->getDataFromPayload('origin.issue.id');
                $this->issue = (new Issue())->find($issueId);
            }
        }

        return $this->issue;
    }

    /**
     * Returns an instance of Project.
     *
     * @return Project
     */
    protected function getProject()
    {
        return $this->getIssue()->project;
    }

    /**
     * Returns the project id.
     *
     * @return int
     */
    protected function getProjectId()
    {
        return $this->getIssue()->project_id;
    }

    /**
     * Returns message data belongs to adding a comment.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForAddComment(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname .
            ' commented on ' . link_to($this->getIssue()->to(), '#' . $this->getIssue()->id);
        $messageData['changes']['comment'] = [
            'noLabel' => true,
            'date'    => $this->getModel()->created_at,
            'now'     => \Html::format($this->getModel()->comment),
        ];

        return $messageData;
    }

    /**
     * Returns message data belongs to updating a comment.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForUpdateComment(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' updated a comment in ' . link_to($this->getIssue()->to(),
                '#' . $this->getIssue()->id);
        $messageData['changes']['comment'] = [
            'noLabel' => true,
            'date'    => $queue->getDataFromPayload('origin.updated_at'),
            'was'     => \Html::format($queue->getDataFromPayload('origin.comment')),
            'now'     => \Html::format($queue->getDataFromPayload('dirty.comment')),
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
    protected function getMessageDataForDeleteComment(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname .
            ' deleted a comment from ' . link_to($this->getIssue()->to(), '#' . $this->getIssue()->id);

        $messageData['changes']['comment'] = [
            'noLabel' => true,
            'date'    => $queue->created_at,
            'was'     => \Html::format($queue->getDataFromPayload('origin.comment')),
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
        $heading = $queue->changeBy->fullname .
            ' commented on ' . link_to($this->getIssue()->to(), '#' . $this->getIssue()->id);

        return $heading;
    }

    /**
     * Check that the issue comment is loaded and the comment is belongs to the issue.
     *
     * @return bool
     */
    protected function validateData()
    {
        return $this->getIssue() && $this->getModel() && $this->getModel()->issue_id === $this->getIssue()->id;
    }

    /**
     * Populate assigned relation in the current issue.
     *
     * @return void
     */
    protected function populateData()
    {
        // Set the relation of assigned in issue object from the fetch data
        $this->issue->setRelation('assigned', $this->getUserById($this->issue->assigned_to));
        $creator = $this->getUserById($this->issue->created_by);
        if ($creator) {
            $this->issue->setRelation('user', $creator);
        }
        $this->loadIssueCreatorToProjectUsers();
    }

    /**
     * Check if the latest message is about deleting a comment.
     *
     * @return bool
     */
    public function isStatusMessage()
    {
        return $this->latestMessage->event === Queue::DELETE_COMMENT;
    }

    /**
     * Whether or not the user wants to receive the message.
     *
     * @param Project\User $user
     * @param array        $data
     *
     * @return bool
     */
    protected function wantToReceiveMessage(Project\User $user, array $data)
    {
        $status = parent::wantToReceiveMessage($user, $data);

        if (!$status) {
            return false;
        }

        // Check if user allowed to receive the message
        $tags = $this->getIssue()->tags;
        foreach ($tags as $tag) {
            if (!$tag->allowMessagesToUser($user->user)) {
                return false;
            }
        }

        return true;
    }
}
