<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tinyissue\Model\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Query;

/**
 * Activity is model class for user activities
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @property int    $id
 * @property string $data
 * @property int    $type_id
 * @property int    $parent_id
 * @property int    $user_id
 * @property int    $item_id
 * @property int    $action_id
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Activity extends Model
{
    public $timestamps = true;
    protected $table = 'users_activity';
    protected $fillable = ['type_id', 'parent_id', 'user_id', 'item_id', 'action_id', 'data'];
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Returns the project issue this activity is belongs to by the item_id, which can hold the issue id
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issue()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue', 'item_id');
    }

    /**
     * Returns the user this activity is belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('\Tinyissue\Model\User', 'user_id');
    }

    /**
     * Returns the user that was assigned to the issue. Only for reassign activity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignTo()
    {
        return $this->belongsTo('\Tinyissue\Model\User', 'action_id');
    }

    /**
     * User activity has one activity type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activity()
    {
        return $this->belongsTo('Tinyissue\Model\Activity', 'type_id');
    }

    /**
     * Returns the comment this activity belongs to if any
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue\Comment', 'action_id');
    }

    /**
     * Returns the project his activity belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project', 'parent_id');
    }

    /**
     * Returns the note this activity belongs to if any
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function note()
    {
        return $this->belongsTo('\Tinyissue\Model\Project\Note', 'action_id');
    }

    /**
     * Get a value from the data field using "dot" notation
     *
     * @param string $name
     *
     * @return Collection
     */
    public function dataCollection($name)
    {
        return new Collection($this->dataValue($name));
    }

    /**
     * Get a value from the data field using "dot" notation
     *
     * @param string $name
     *
     * @return mixed
     */
    public function dataValue($name)
    {
        return array_get($this->data, $name);
    }
}
