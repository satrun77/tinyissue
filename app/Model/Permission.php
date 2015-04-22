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
 *
 * @property int    $id
 * @property string $permission
 * @property string $description
 * @property string $auto_has
 */
class Permission extends Model
{
    /**
     * Permission to view issue
     *
     * @var string
     */
    const PERM_ISSUE_VIEW = 'issue-view';

    /**
     * Permission to create issue
     *
     * @var string
     */
    const PERM_ISSUE_CREATE = 'issue-create';

    /**
     * Permission to add/edit/delete comment in an issue
     *
     * @var string
     */
    const PERM_ISSUE_COMMENT = 'issue-comment';

    /**
     * Permission to modify issue
     *
     * @var string
     */
    const PERM_ISSUE_MODIFY = 'issue-modify';

    /**
     * Permission to modify all projects
     *
     * @var string
     */
    const PERM_PROJECT_ALL = 'project-all';

    /**
     * Permission to create project
     *
     * @var string
     */
    const PERM_PROJECT_CREATE = 'project-create';

    /**
     * Permission to modify project
     *
     * @var string
     */
    const PERM_PROJECT_MODIFY = 'project-modify';

    /**
     * Permission as administrator
     *
     * @var string
     */
    const PERM_ADMIN = 'administration';

    /**
     * Timestamp enabled
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Name of database table
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * List of group permissions
     *
     * @var array
     */
    protected $groups = [
        self::PERM_PROJECT_ALL => [
            self::PERM_PROJECT_CREATE,
            self::PERM_PROJECT_MODIFY,
        ],
    ];

    /**
     * Compare if the permission is match
     *
     * @param string $permission
     *
     * @return bool
     */
    public function isEqual($permission)
    {
        if ($permission === $this->permission) {
            return true;
        }

        foreach ($this->groups as $group => $permissions) {
            if (in_array($permission, $permissions) && $group === $this->permission) {
                return true;
            }
        }

        return false;
    }
}
