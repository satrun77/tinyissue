<?php

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activity';
    public $timestamps = false;

    const TYPE_CREATE_ISSUE = 1;
    const TYPE_COMMENT = 2;
    const TYPE_CLOSE_ISSUE = 3;
    const TYPE_REOPEN_ISSUE = 4;
    const TYPE_REASSIGN_ISSUE = 5;
    const TYPE_NOTE = 6;
}
