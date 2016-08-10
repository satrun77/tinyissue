<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Issue\Comment;

use Tinyissue\Model\Project\Issue\Comment;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Message;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\User;

/**
 * QueueTrait is trait class for adding method to insert records into a queue.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait QueueTrait
{
    /**
     * Insert add comment to message queue.
     *
     * @param Comment  $comment
     * @param int|User $changeBy
     *
     * @return void
     */
    public function queueAdd(Comment $comment, $changeBy)
    {
        // Skip message if issue closed
        if (!$comment->issue->isOpen()) {
            return;
        }

        return (new Message\Queue())->queue(Queue::ADD_COMMENT, $comment, $changeBy);
    }

    /**
     * Insert update comment to message queue.
     *
     * @param Comment  $comment
     * @param int|User $changeBy
     *
     * @return void
     */
    public function queueUpdate(Comment $comment, $changeBy)
    {
        // Skip message if issue closed or nothing changed in comment
        if (!$comment->issue->isOpen() || !$comment->isDirty()) {
            return;
        }

        return (new Message\Queue())->queue(Queue::UPDATE_COMMENT, $comment, $changeBy);
    }

    /**
     * Insert delete comment to message queue.
     *
     * @param Comment  $comment
     * @param int|User $changeBy
     *
     * @return void
     */
    public function queueDelete(Comment $comment, $changeBy)
    {
        // Skip message if issue closed
        if ($comment->issue instanceof Issue && !$comment->issue->isOpen()) {
            return;
        }

        return (new Message\Queue())->queueDelete(Queue::DELETE_COMMENT, $comment, $changeBy);
    }
}
