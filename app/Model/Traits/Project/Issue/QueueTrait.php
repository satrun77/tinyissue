<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Issue;

use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\User;

/**
 * QueueTrait is trait class for adding method to insert records into a queue.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait QueueTrait
{
    /**
     * Insert update issue to message queue.
     *
     * @param Issue $issue
     * @param User  $changeBy
     *
     * @return void
     */
    public function queueUpdate(Issue $issue, User $changeBy)
    {
        // Number of changed attributes
        $countChanges = count($issue->getDirty());

        // Whether or not the assignee has changed
        $noMessageForMe = false;

        // is Closed?
        if (!$issue->isOpen()) {
            return (new Queue())->queue(Queue::CLOSE_ISSUE, $issue, $changeBy);
        }

        // is Reopened?
        if ((int) $issue->getOriginal('status') === Issue::STATUS_CLOSED) {
            return (new Queue())->queue(Queue::REOPEN_ISSUE, $issue, $changeBy);
        }

        // If the assignee has changed and it is not the logged in user who made the action
        if ($issue->assigned_to !== $issue->getOriginal('assigned_to', $issue->assigned_to)
            && $changeBy->id !== $issue->assigned_to
        ) {
            (new Queue())->queue(Queue::ASSIGN_ISSUE, $issue, $changeBy);
            $noMessageForMe = $issue->assigned_to;
        }

        // If the update was just for assigning user, then skip update issue
        if (!($countChanges === 1 && $noMessageForMe !== false)) {
            return (new Queue())->queue(Queue::UPDATE_ISSUE, $issue, $changeBy);
        }
    }

    /**
     * Insert add issue to message queue.
     *
     * @param Issue $issue
     * @param User  $changeBy
     *
     * @return void
     */
    public function queueAdd(Issue $issue, User $changeBy)
    {
        return (new Queue())->queue(Queue::ADD_ISSUE, $issue, $changeBy);
    }

    /**
     * Insert assign issue to message queue.
     *
     * @param Issue $issue
     * @param User  $changeBy
     *
     * @return void
     */
    public function queueAssign(Issue $issue, User $changeBy)
    {
        // If the assignee has changed and it is not the logged in user who made the action
        if ($issue->assigned_to > 0 && $changeBy->id !== $issue->assigned_to) {
            return (new Queue())->queue(Queue::ASSIGN_ISSUE, $issue, $changeBy);
        }
    }

    /**
     * Insert issue tag changes to message queue.
     *
     * @param Issue $issue
     * @param array $addedTags
     * @param array $removedTags
     * @param User  $changeBy
     *
     * @return mixed
     */
    public function queueChangeTags(Issue $issue, array $addedTags, array $removedTags, User $changeBy)
    {
        $queue = new Queue();

        return $queue->queueIssueTagChanges($issue, $addedTags, $removedTags, $changeBy);
    }
}
