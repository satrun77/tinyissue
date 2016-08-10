<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Message\Queue;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Message;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\User;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the message queue model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait CrudTrait
{
    /**
     * Insert a record into the message queue.
     *
     * @param string   $name
     * @param Model    $model
     * @param int|User $changeBy
     *
     * @return void
     */
    public function queue($name, Model $model, $changeBy)
    {
        // Get modified attributes
        $dirty = $model->getDirty();
        $isNew = strpos($name, 'add_') === 0;

        // Stop if nothing changed
        if (!$model->isDirty() && !$isNew) {
            return;
        }

        // Get the original value of the modified attributes
        $origin = [];
        foreach ($dirty as $field => $value) {
            $origin[$field] = $model->getOriginal($field, $value);
        }

        // Fill and save to message queue
        $fill = $this->getFillAttributes($name, $model, $changeBy, [
            'dirty'  => $dirty,
            'origin' => $origin,
        ]);

        return $this->fill($fill)->save();
    }

    /**
     * Insert a record into the message queue about a delete event.
     *
     * @param string   $name
     * @param Model    $model
     * @param int|User $changeBy
     *
     * @return void
     */
    public function queueDelete($name, Model $model, $changeBy)
    {
        // Fill and save to message queue
        $fill = $this->getFillAttributes($name, $model, $changeBy, [
            'dirty'  => [],
            'origin' => $model->toArray(),
        ]);

        return $this->fill($fill)->save();
    }

    /**
     * Insert records about tag changes into the message queue.
     *
     * @param Issue $issue
     * @param array $added
     * @param array $removed
     * @param User  $changeBy
     *
     * @return mixed
     */
    public function queueIssueTagChanges(Issue $issue, array $added, array $removed, User $changeBy)
    {
        // Fill and save to message queue
        $fill = $this->getFillAttributes(Message\Queue::CHANGE_TAG_ISSUE, $issue, $changeBy, [
            'added'   => $added,
            'removed' => $removed,
        ]);

        return $this->fill($fill)->save();
    }

    /**
     * Returns an array containing the data needed for the message queue save.
     *
     * @param string   $name
     * @param Model    $model
     * @param int|User $changeBy
     * @param array    $payload
     *
     * @return array
     */
    protected function getFillAttributes($name, Model $model, $changeBy, array $payload)
    {
        $changeById =  (int) ($changeBy instanceof User ? $changeBy->id : $changeBy);

        $fill                 = [];
        $fill['event']        = $name;
        $fill['payload']      = $payload;
        $fill['model_type']   = get_class($model);
        $fill['model_id']     = $model->id;
        $fill['change_by_id'] = $changeById;

        return $fill;
    }
}
