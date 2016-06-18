<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Message;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Project\Issue\Comment;
use Tinyissue\Model\Project\Note;
use Tinyissue\Model\Project\User;
use Tinyissue\Model\Traits\Message\Queue\CrudTrait;
use Tinyissue\Model\Traits\Message\Queue\QueryTrait;
use Tinyissue\Model\Traits\Message\Queue\RelationTrait;

/**
 * Queue is model class for message queue.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int    $id
 * @property string $event
 * @property id     $model_id
 * @property string $model_type
 * @property string $payload
 * @property id     $change_by_id
 * @property User   $changeBy
 */
class Queue extends Model
{
    use CrudTrait,
        RelationTrait,
        QueryTrait;

    // List of available events
    const ADD_ISSUE             = 'add_issue';
    const CHANGE_TAG_ISSUE      = 'change_tag_issue';
    const UPDATE_ISSUE          = 'update_issue';
    const ASSIGN_ISSUE          = 'assign_issue';
    const CLOSE_ISSUE           = 'close_issue';
    const REOPEN_ISSUE          = 'reopen_issue';
    const ADD_COMMENT           = 'add_comment';
    const UPDATE_COMMENT        = 'update_comment';
    const DELETE_COMMENT        = 'delete_comment';
    const ADD_NOTE              = 'add_note';
    const UPDATE_NOTE           = 'update_note';
    const DELETE_NOTE           = 'delete_note';
    const MESSAGE_IN_ALL_ISSUES = 'in_all_issues';

    protected static $ADD_EVENTS = [
        Issue::class   => self::ADD_ISSUE,
        Comment::class => self::ADD_COMMENT,
        Note::class    => self::ADD_NOTE,
    ];

    /**
     * Timestamp disabled.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    public $fillable = ['event', 'payload', 'model_id', 'model_type', 'change_by_id'];

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'messages_queue';

    /**
     * List of columns and their cast data-type.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Get an item from a payload using "dot" notation.
     *
     * @param string|array $key
     *
     * @return mixed
     */
    public function getDataFromPayload($key)
    {
        return data_get($this->payload, $key, '');
    }

    /**
     * Get add event from a model object.
     *
     * @param Model $model
     *
     * @return mixed
     */
    public static function getAddEventNameFromModel(Model $model)
    {
        return self::$ADD_EVENTS[get_class($model)];
    }

    /**
     * Get an array of all of the available events.
     *
     * @return array
     */
    public static function getEventsNames()
    {
        return [
            self::ADD_ISSUE,
            self::CHANGE_TAG_ISSUE,
            self::UPDATE_ISSUE,
            self::ASSIGN_ISSUE,
            self::CLOSE_ISSUE,
            self::REOPEN_ISSUE,
            self::ADD_COMMENT,
            self::UPDATE_COMMENT,
            self::DELETE_COMMENT,
            self::ADD_NOTE,
            self::UPDATE_NOTE,
            self::DELETE_NOTE,
        ];
    }
}
