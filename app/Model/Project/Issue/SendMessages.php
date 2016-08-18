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
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;
use Tinyissue\Services\SendMessagesAbstract;

/**
 * SendMessages is a class to manage & process of sending messages about issue changes.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class SendMessages extends SendMessagesAbstract
{
    protected $template = 'update_issue';

    /**
     * Collection of tags.
     *
     * @var Collection
     */
    protected $tags;

    /**
     * Returns an instance of Issue.
     *
     * @return Project\Issue|bool
     */
    protected function getIssue()
    {
        if (null === $this->issue) {
            $this->issue = $this->getModel();
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
     * Returns message data belongs to adding an issue.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForAddIssue(Queue $queue)
    {
        $messageData                    = ['changes' => []];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' created a new issue';
        if ($this->issue->assigned) {
            $messageData['changes']['assignee'] = $this->issue->assigned->fullname;
        }

        $tags = $this->issue->tags()->with('parent')->get();
        foreach ($tags as $tag) {
            $tagArray                                   = $tag->toShortArray();
            $tagArray['now']                            = $tagArray['name'];
            $messageData['changes'][$tag->parent->name] = $tagArray;
        }

        if ($this->issue->time_quote && !$this->issue->isQuoteLocked()) {
            $messageData['changes']['time_quote'] = \Html::duration($this->issue->time_quote);
        }

        return $messageData;
    }

    /**
     * Returns message data belongs to updating an issue.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForUpdateIssue(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' updated an issue';
        $whiteListFields                = ['change_by', 'title', 'body', 'assignee', 'status', 'type', 'resolution', 'time_quote'];

        foreach ($queue->payload['dirty'] as $field => $value) {
            // Skip fields that not part of the white list or quote is locked
            if (!in_array($field, $whiteListFields) || ($field == 'time_quote' && $this->issue->isQuoteLocked())) {
                continue;
            }

            // Format quote to readable time
            $value                          = $field === 'time_quote' ? \Html::duration($value) : $value;
            $value                          = $field === 'body' ? \Html::format($value) : $value;
            $messageData['changes'][$field] = [
                'now' => $value,
                'was' => $queue->getDataFromPayload('origin.' . $field),
            ];
        }

        return $messageData;
    }

    /**
     * Returns message data belongs to reopening an issue.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForReopenIssue(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' reopened an issue';
        $statusNow                      = $this->issue
            ->tags()->with('parent')->get()->where('parent.name', Tag::GROUP_STATUS)->last();
        $messageData['changes']['status'] = [
            'was' => trans('tinyissue.closed'),
            'now' => ($statusNow ? $statusNow->fullname : ''),
            'id'  => ($statusNow ? $statusNow->id : ''),
        ];

        return $messageData;
    }

    /**
     * Returns message data belongs to closing an issue.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForCloseIssue(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' closed an issue';
        $statusWas                      = $this->issue
            ->tags()->with('parent')->get()->where('parent.name', Tag::GROUP_STATUS)->last();
        $messageData['changes']['status'] = [
            'was' => ($statusWas ? $statusWas->fullname : ''),
            'now' => trans('tinyissue.closed'),
        ];

        return $messageData;
    }

    /**
     * Returns message data belongs to assigning an issue to a user.
     *
     * @param Queue $queue
     * @param array $extraData
     *
     * @return array
     */
    protected function getMessageDataForAssignIssue(Queue $queue, array $extraData)
    {
        $messageData = [];
        if (!array_key_exists('now', $extraData)) {
            $assignTo  = $this->getUserById($queue->getDataFromPayload('dirty.assigned_to'));
            $extraData = ['now' => $assignTo->fullname];
        }

        $messageData['changes']['assignee'] = $extraData;
        $messageData['changeByHeading']     = $queue->changeBy->fullname . ' assigned an issue to ' . $extraData['now'];

        return $messageData;
    }

    /**
     * Returns message data belongs to changing an issue tags.
     *
     * @param Queue $queue
     *
     * @return array
     */
    protected function getMessageDataForChangeTagIssue(Queue $queue)
    {
        $messageData                    = [];
        $messageData['changeByHeading'] = $queue->changeBy->fullname . ' changed an issue tag';

        foreach ($queue->payload['added'] as $tag) {
            $group                          = strtolower($tag['group']);
            $messageData['changes'][$group] = [
                'now'           => $tag['name'],
                'id'            => $tag['id'],
                'message_limit' => $tag['message_limit'],
            ];
        }

        foreach ($queue->payload['removed'] as $tag) {
            $group                                 = strtolower($tag['group']);
            $messageData['changes'][$group]['was'] = $tag['name'];
        }

        return $messageData;
    }

    /**
     * Check that the issue is load.
     *
     * @return bool
     */
    protected function validateData()
    {
        // if issue closed and last issue not closed, then something is wrong skip
        if (!$this->issue->isOpen() && $this->latestMessage->event !== Queue::CLOSE_ISSUE) {
            return false;
        }

        return true;
    }

    /**
     * Populate assigned relation in the current issue.
     *
     * @return void
     */
    protected function populateData()
    {
        $this->issue->setRelation('assigned', $this->getUserById($this->issue->assigned_to));
        $creator = $this->getUserById($this->issue->created_by);
        if ($creator) {
            $this->issue->setRelation('user', $creator);
        }
        $this->loadIssueCreatorToProjectUsers();
    }

    /**
     * Check if the latest message is about closing or reopening an issue.
     *
     * @return bool
     */
    public function isStatusMessage()
    {
        return ((!$this->issue->isOpen() && $this->latestMessage->event === Queue::CLOSE_ISSUE)
            || ($this->issue->isOpen() && $this->latestMessage->event === Queue::REOPEN_ISSUE));
    }

    /**
     * Process assign to user message. Send direct message to new and previous users and full subscribers.
     *
     * @return void
     */
    protected function processDirectMessages()
    {
        // Stop if issue is closed
        if (!$this->getIssue()->isOpen()) {
            return;
        }

        // Fetch all of the assign issue changes
        $assignMessages = $this->allMessages->where('event', Queue::ASSIGN_ISSUE);

        // Skip if no changes
        if ($assignMessages->isEmpty()) {
            return;
        }

        // Fetch the latest assignee
        /** @var Queue $assignMessage */
        $assignMessage = $assignMessages->first();

        // Fetch the user details of the new assignee & previous assignee if this isn't new issue
        $assigns        = [];
        $assigns['new'] = (int) $assignMessage->getDataFromPayload('dirty.assigned_to');
        if (!$this->addMessage) {
            $previousAssignMessage = $assignMessages->last();
            $assigns['old']        = (int) $previousAssignMessage->getDataFromPayload('origin.assigned_to');
        }

        // Fetch users objects for old and new assignee
        /** @var \Illuminate\Database\Eloquent\Collection $assignObjects */
        $assignObjects = (new User())->whereIn('id', $assigns)->get();

        // If for what ever reason the user does not exists, skip this change
        // or if there is only one user and is not matching the new assignee.
        // then skip this message
        if ($assignObjects->count() === 0
            || ($assignObjects->count() === 1 && (int) $assignObjects->first()->id !== $assigns['new'])
        ) {
            return;
        }

        // Get the object of the new assignee
        $assignTo = $assignObjects->where('id', $assigns['new'])->first();
        $users    = collect([$this->createProjectUserObject($assignTo)]);

        // Exclude the user from any other message for this issue
        $this->addToExcludeUsers($assignTo);

        // Data about new and previous assignee
        $extraMessageData = [
            'now' => $assignTo->fullname,
        ];

        // Make sure that the previous assignee was not the same as the new user
        if (array_key_exists('old', $assigns) && $assigns['old'] > 0 && $assigns['new'] !== $assigns['old']) {
            $previousAssign = $assignObjects->where('id', $assigns['old'])->first();
            if ($previousAssign) {
                $extraMessageData['was'] = $previousAssign->fullname;
                $users->push($this->createProjectUserObject($previousAssign));
            }
        }

        // Get message data needed for the message & send
        $messageData = $this->getMessageData($assignMessage, $extraMessageData);
        $this->sendMessages($users, $messageData);
    }

    /**
     * Create user project object for a user.
     *
     * @param User $user
     *
     * @return Project\User
     */
    protected function createProjectUserObject(User $user)
    {
        $userProject = new Project\User([
            'user_id'    => $user->id,
            'project_id' => $this->getProjectId(),
        ]);
        $userProject->setRelation('user', $user);
        $userProject->setRelation('project', $this->getProject());

        return $userProject;
    }

    /**
     * Get collection of tags or one by ID.
     *
     * @param int $tagId
     *
     * @return Tag
     */
    protected function getTag($tagId)
    {
        if (null === $this->tags) {
            $this->tags = collect([]);
        }

        // Load & extract tag by ID
        $tag = $this->tags->where('id', $tagId)->first();
        if (!$tag) {
            $tag = (new Tag())->find($tagId);
            $this->tags->push($tag);
        }

        return $tag;
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

        // Search for tag changes and verify if the user can receive messages
        foreach ($data['changes'] as $label => $change) {
            // Skip other changes that are not related to tag
            if (!in_array($label, Tag::getCoreGroups())) {
                continue;
            }

            // If the change was only remove a tag
            if (!array_key_exists('now', $change) && array_key_exists('was', $change)) {
                return true;
            }

            // If tag id not found
            if (!array_key_exists('id', $change)) {
                return true;
            }

            // Fetch tag details
            $tag = $this->getTag($change['id']);

            // Check if user allowed to receive the message
            if (!$tag->allowMessagesToUser($user->user)) {
                return false;
            }
        }

        return true;
    }
}
