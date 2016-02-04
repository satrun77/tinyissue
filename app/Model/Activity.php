<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Activity is model class for activities.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int    $id
 * @property string $description
 * @property string $activity
 */
class Activity extends Model
{
    /**
     * Activity Id: create issue.
     *
     * @var int
     */
    const TYPE_CREATE_ISSUE = 1;

    /**
     * Activity Id: add comment to an issue.
     *
     * @var int
     */
    const TYPE_COMMENT = 2;

    /**
     * Activity Id: close issue.
     *
     * @var int
     */
    const TYPE_CLOSE_ISSUE = 3;

    /**
     * Activity Id: reopen issue.
     *
     * @var int
     */
    const TYPE_REOPEN_ISSUE = 4;

    /**
     * Activity Id: re-assign issue.
     *
     * @var int
     */
    const TYPE_REASSIGN_ISSUE = 5;

    /**
     * Activity Id: add note to an issue.
     *
     * @var int
     */
    const TYPE_NOTE = 6;

    /**
     * Activity Id: modify issue tags.
     *
     * @var int
     */
    const TYPE_ISSUE_TAG = 7;

    /**
     * Timestamp enabled.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'activity';
}
