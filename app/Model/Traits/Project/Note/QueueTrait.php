<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Note;

use Tinyissue\Model\Project\Note;
use Tinyissue\Model\Message;
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
     * Insert add note to message queue.
     *
     * @param Note $note
     * @param User $changeBy
     *
     * @return void
     */
    public function queueAdd(Note $note, User $changeBy)
    {
        return (new Message\Queue())->queue(Queue::ADD_NOTE, $note, $changeBy);
    }

    /**
     * Insert update note to message queue.
     *
     * @param Note $note
     * @param User $changeBy
     *
     * @return void
     */
    public function queueUpdate(Note $note, User $changeBy)
    {
        // Skip message if nothing changed in note
        if (!$note->isDirty()) {
            return;
        }

        return (new Message\Queue())->queue(Queue::UPDATE_NOTE, $note, $changeBy);
    }

    /**
     * Insert delete note to message queue.
     *
     * @param Note $note
     * @param User $changeBy
     *
     * @return void
     */
    public function queueDelete(Note $note, User $changeBy)
    {
        return (new Message\Queue())->queueDelete(Queue::DELETE_NOTE, $note, $changeBy);
    }
}
