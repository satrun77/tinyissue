<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Services;

use Illuminate\Mail\Message as MailMessage;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Mail\Mailer;
use Tinyissue\Http\Requests\FormRequest\Note;
use Tinyissue\Model\Message;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Role;
use Tinyissue\Model\User;

/**
 * SendMessagesAbstract is an abstract class with for objects that requires sending messages.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
abstract class SendMessagesAbstract
{
    /**
     * Instance of issue that this message belong to.
     *
     * @var Issue
     */
    protected $issue;

    /**
     * Instance of project that this message belong to.
     *
     * @var Issue
     */
    protected $project;

    /**
     * The latest message queued.
     *
     * @var Message\Queue
     */
    protected $latestMessage;

    /**
     * Collection of all of the queued messages.
     *
     * @var Collection
     */
    protected $allMessages;

    /**
     * Instance of a queued message that is for adding a record (ie. adding issue).
     *
     * @var Message\Queue
     */
    protected $addMessage;
    /**
     * Collection of users that must not receive messages.
     *
     * @var Collection
     */
    protected $excludeUsers;
    /**
     * Collection of all of the project users that should receive messages.
     *
     * @var Collection
     */
    protected $projectUsers;
    /**
     * Collection of full subscribers that will always receive messages.
     *
     * @var Collection
     */
    protected $fullSubscribers;
    /**
     * Name of message template.
     *
     * @var string
     */
    protected $template;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * Collection of all messages.
     *
     * @var Collection
     */
    protected $messages;

    /**
     * Set instance of Mailer.
     *
     * @param Mailer $mailer
     *
     * @return $this
     */
    public function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }

    /**
     * The main method to process the massages queue and send them.
     *
     * @param Message\Queue $latestMessage
     * @param Collection    $changes
     *
     * @return void
     */
    public function process(Message\Queue $latestMessage, Collection $changes)
    {
        $this->setup($latestMessage, $changes);

        // Is model deleted?
        if (!$this->getModel()) {
            return;// $this->sendMessageToAll($this->latestMessage);
        }

        if (!$this->validateData()) {
            return;
        }

        $this->processDirectMessages();

        // Skip if no users found
        if ($this->getProjectUsers()->isEmpty()) {
            return;
        }

        $this->populateData();

        // Send the latest message if it is about status (ie. closed issue)
        if ($this->isStatusMessage()) {
            return $this->sendMessageToAll($this->latestMessage);
        }

        // Get message data for all of the messages combined & for add message queue if exists
        $addMessageData = [];
        if ($this->addMessage) {
            $addMessageData = $this->getMessageData($this->addMessage);
        }
        $everythingMessageData = $this->getCombineMessageData($this->allMessages);

        // Send messages to project users
        $this->sendMessages($this->getProjectUsers(), [
            'addMessage' => $addMessageData,
            'everything' => $everythingMessageData,
        ]);
    }

    /**
     * Setup properties needed for the process.
     *
     * @param Message\Queue $latestMessage
     * @param Collection    $allMessages
     *
     * @return void
     */
    protected function setup(Message\Queue $latestMessage, Collection $allMessages)
    {
        // Set queue messages
        $this->latestMessage = $latestMessage;
        $this->allMessages   = $allMessages;

        // Exclude the user who made the change from receiving messages
        $this->addToExcludeUsers($this->latestMessage->changeBy);

        // Extract add model message if exists
        if ($this->getModel()) {
            $addMessageIdentifier = Message\Queue::getAddEventNameFromModel($this->getModel());
            $this->addMessage     = $this->allMessages->where('event', $addMessageIdentifier)->first();
        }

        // Make sure to load issue
        $this->getIssue();
    }

    /**
     * Whether or not we have all the needed properties.
     *
     * @return bool
     */
    abstract protected function validateData();

    /**
     * Process any messages queue that is to send messages to specific users.
     * For example, assign issue to user to message the user about the issue.
     *
     * @return void
     */
    protected function processDirectMessages()
    {
    }

    /**
     * Populate any data or properties.
     *
     * @return void
     */
    protected function populateData()
    {
    }

    /**
     * Whether or not the latest message is about status change such as closed issue.
     *
     * @return bool
     */
    abstract public function isStatusMessage();

    /**
     * Returns the message subject.
     *
     * @return string
     */
    protected function getSubject()
    {
        return '#' . $this->issue->id . ' / ' . $this->issue->title;
    }

    /**
     * Returns an array of data needed for the message.
     *
     * @param Message\Queue $queue
     * @param array         $extraData
     *
     * @return array
     */
    protected function getMessageData(Message\Queue $queue, array $extraData = [])
    {
        // Generic info for all messages emails
        $messageData                         = [];
        $messageData['issue']                = $this->getIssue();
        $messageData['project']              = $this->getProject();
        $messageData['changes']              = [];
        $messageData['changes']['change_by'] = [
            'now' => $queue->changeBy->fullname,
        ];
        if ($this->getIssue()) {
            $messageData['changes']['change_by']['url'] = $this->getIssue()->to();
        } else {
            $messageData['changes']['change_by']['url'] = $this->getProject()->to();
        }
        $messageData['changeByImage']   = $queue->changeBy->image;
        $messageData['changeByHeading'] = $this->getMessageHeading($queue);
        $messageData['event']           = $queue->event;

        // Info specific to a message type
        $method = 'getMessageDataFor' . ucfirst(camel_case($queue->event));
        if (method_exists($this, $method)) {
            $messageData = array_replace_recursive($messageData, $this->{$method}($queue, $extraData));
        }

        return $messageData;
    }

    /**
     * Loop through all of the messages and combine its message data.
     *
     * @param Collection $changes
     *
     * @return array
     */
    protected function getCombineMessageData(Collection $changes)
    {
        $everything = [];
        $changes->reverse()->each(function (Message\Queue $queue) use (&$everything) {
            if (!$everything) {
                $everything = $this->getMessageData($queue);
            } else {
                $messageData = $this->getMessageData($queue);
                $everything['changes'] = array_merge($everything['changes'], $messageData['changes']);
            }
        });
        $latestMessage                 = $changes->first();
        $everything['changeByHeading'] = $this->getMessageHeading($latestMessage, $changes);
        $everything['event']           = $latestMessage->event;
        $messageData                   = $this->getMessageData($latestMessage);
        $everything                    = array_replace_recursive($everything, $messageData);

        return $everything;
    }

    /**
     * Return text to be used for the message heading.
     *
     * @param Message\Queue   $queue
     * @param Collection|null $changes
     *
     * @return string
     */
    protected function getMessageHeading(Message\Queue $queue, Collection $changes = null)
    {
        $heading = $queue->changeBy->fullname . ' ';

        // If other users have made changes too
        if (!is_null($changes) && $changes->unique('change_by_id')->count() > 1) {
            $heading .= '& others ';
        }

        return $heading;
    }

    /**
     * Returns collection of all users in a project that should receive the messages.
     *
     * @return Collection
     */
    protected function getProjectUsers()
    {
        if (null === $this->projectUsers) {
            $this->projectUsers = (new Project\User())
                ->with('message', 'user', 'user.role')
                ->whereNotIn('user_id', $this->getExcludeUsers()->lists('id'))
                ->where('project_id', '=', $this->getProjectId())
                ->get();
        }

        return $this->projectUsers;
    }

    /**
     * Returns the model that is belong to the queue message.
     *
     * @return Issue|Issue\Comment|Note
     */
    protected function getModel()
    {
        return $this->latestMessage->model;
    }

    /**
     * Returns an instance of project issue.
     *
     * @return Issue
     */
    abstract protected function getIssue();

    /**
     * Returns an instance of project.
     *
     * @return Project
     */
    abstract protected function getProject();

    /**
     * Returns the id of a project.
     *
     * @return int
     */
    abstract protected function getProjectId();

    /**
     * Returns collection of all of the users that must not receive messages.
     *
     * @return Collection
     */
    protected function getExcludeUsers()
    {
        if (null === $this->excludeUsers) {
            $this->excludeUsers = collect([]);
        }

        return $this->excludeUsers;
    }

    /**
     * Exclude a user from receiving messages.
     *
     * @param User $user
     *
     * @return $this
     */
    protected function addToExcludeUsers(User $user)
    {
        $this->getExcludeUsers()->push($user);

        return $this;
    }

    /**
     * Find user by id. This search the project users and fallback to excluded list of users.
     *
     * @param int $userId
     *
     * @return User
     */
    protected function getUserById($userId)
    {
        $projectUser = $this->getProjectUsers()->where('user_id', $userId, false)->first();

        if (!$projectUser) {
            return $this->getExcludeUsers()->where('id', $userId, false)->first();
        }

        return $projectUser->user;
    }

    /**
     * Returns collection of all messages.
     *
     * @return Collection
     */
    protected function getMessages()
    {
        if (null === $this->messages) {
            $this->messages = (new Message())->orderBy('id', 'ASC')->get();
        }

        return $this->messages;
    }

    /**
     * Send a message to a user.
     *
     * @param User  $user
     * @param array $data
     *
     * @return mixed
     */
    private function sendMessage(User $user, array $data)
    {
        // Make sure the data contains changes
        if (!array_key_exists('changes', $data) && count($data['changes']) > 1) {
            return;
        }

        return $this->mailer->send('email.' . $this->template, $data, function (MailMessage $message) use ($user) {
            $message->to($user->email, $user->fullname)->subject($this->getSubject());
        });
    }

    /**
     * Send a message to a collection of users, or send customised message per use logic.
     *
     * @param Collection $users
     * @param array      $data
     *
     * @return void
     */
    protected function sendMessages(Collection $users, array $data)
    {
        foreach ($users as $user) {
            $userMessageData = $this->getUserMessageData($user->user_id, $data);
            if (!$this->wantToReceiveMessage($user, $userMessageData)) {
                continue;
            }

            $this->sendMessage($user->user, $userMessageData);
        }
    }

    /**
     * Get customised message per user logic.
     *
     * @param int   $userId
     * @param array $messagesData
     *
     * @return array
     */
    protected function getUserMessageData($userId, array $messagesData)
    {
        if (array_key_exists('event', $messagesData)) {
            return $messagesData;
        }

        // Possible message data
        $addMessageData        = $messagesData['addMessage'];
        $everythingMessageData = $messagesData['everything'];

        // Check if the user has seen the model data and made a change
        $changeMadeByUser = $this->allMessages->where('change_by_id', $userId);

        // This user has never seen this model data
        if (!$changeMadeByUser->count()) {
            if ($this->addMessage) {
                return $addMessageData;
            }

            return $everythingMessageData;
        }

        // This user has seen this model data
        // Get all of the changes that may happened later.
        // Combine them and send message to the user about these changes.
        $everythingMessageData = $this->getCombineMessageData(
            $this->allMessages->forget($changeMadeByUser->keys()->toArray())
        );

        return $everythingMessageData;
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
        /** @var Message $message */
        $message = $user->message;
        if (!$message) {
            $roleName = $user->user->role->role;
            $message  = $this->getMessages()->where('name', Message::$defaultMessageToRole[$roleName])->first();
        }

        // No message to send,
        // - if we can't find message object or
        // - messages are disabled or
        // - event is inactive for the user message setting
        if (!$message || $message->isDisabled() || !$message->isActiveEvent($data['event'])) {
            return false;
        }

        // Wants to see all updates in project
        if ((bool) $message->in_all_issues === true) {
            return true;
        }

        if (!$this->getIssue()) {
            return false;
        }

        // For issue only send messages if user is assignee or creator
        $creator  = $this->getIssue()->user;
        $assignee = $this->getIssue()->assigned;
        if ($user->user_id === $creator->id || ($assignee && $user->user_id === $assignee->id)) {
            return true;
        }

        return false;
    }

    /**
     * Send a message to al users in project and full subscribes.
     *
     * @param Message\Queue $queue
     *
     * @return void
     */
    protected function sendMessageToAll(Message\Queue $queue)
    {
        $messageData = $this->getMessageData($queue);

        $this->sendMessages($this->getProjectUsers(), $messageData);
    }

    /**
     * Load the creator of an issue to the collection of project users. So we can send message to creator if needed.
     *
     * @return void
     */
    protected function loadIssueCreatorToProjectUsers()
    {
        // Stop if we can't get the issue
        if (!$this->getIssue()) {
            return;
        }

        // Get issue creator
        $creator = $this->getIssue()->user;

        // Stop if creator excluded from messages
        $excluded = $this->getExcludeUsers()->where('id', $creator->id, false)->first();
        if ($excluded) {
            return;
        }

        // Stop if the creator already part of the project users
        $existInProject = $this->getProjectUsers()->where('user_id', $creator->id, false)->first();
        if ($existInProject) {
            return;
        }

        // Create virtual project user object & add to collection
        $userProject = new Project\User([
            'user_id'    => $creator->id,
            'project_id' => $this->getProjectId(),
        ]);
        $userProject->setRelation('user', $creator);
        $userProject->setRelation('project', $this->getProject());
        $this->getProjectUsers()->push($userProject);
    }
}
