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
 * Permission is model class for permissions
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Permission extends Model
{
    protected $table = 'permissions';
    public $timestamps = false;

    const PERM_ISSUE_VIEW = 'issue-view';
    const PERM_ISSUE_CREATE = 'issue-create';
    const PERM_ISSUE_COMMENT = 'issue-comment';
    const PERM_ISSUE_MODIFY = 'issue-modify';
    const PERM_PROJECT_ALL = 'project-all';
    const PERM_PROJECT_CREATE = 'project-create';
    const PERM_PROJECT_MODIFY = 'project-modify';
    const PERM_ADMIN = 'administration';
}
