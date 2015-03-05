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
 * Activity is model class for activities
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Activity extends Model
{
    protected $table = 'activity';
    public $timestamps = false;

    /**
     * Activities IDs!
     */
    const TYPE_CREATE_ISSUE = 1;
    const TYPE_COMMENT = 2;
    const TYPE_CLOSE_ISSUE = 3;
    const TYPE_REOPEN_ISSUE = 4;
    const TYPE_REASSIGN_ISSUE = 5;
    const TYPE_NOTE = 6;
    const TYPE_ISSUE_TAG = 7;
}
