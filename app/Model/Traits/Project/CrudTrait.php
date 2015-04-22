<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project;

use Illuminate\Database\Query;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the Project model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int           $id
 *
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method   Query\Builder join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
 * @method   Project       fill(array $attributes)
 * @method   RelationTrait projectUsers()
 */
trait CrudTrait
{
    /**
     * removes a user from a project
     *
     * @param int $userId
     *
     * @return mixed
     */
    public function unassignUser($userId)
    {
        return $this->projectUsers()->where('user_id', '=', $userId)->delete();
    }

    /**
     * Create a new project
     *
     * @param array $input
     *
     * @return $this
     */
    public function createProject(array $input = [])
    {
        $this->fill($input)->save();

        /* Assign selected users to the project */
        if (isset($input['user']) && count($input['user']) > 0) {
            foreach ($input['user'] as $id) {
                $this->assignUser($id);
            }
        }

        return $this;
    }

    /**
     * Assign a user to a project
     *
     * @param int $userId
     * @param int $roleId
     *
     * @return Project\User
     */
    public function assignUser($userId, $roleId = 0)
    {
        return $this->projectUsers()->save(new Project\User([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]));
    }

    /**
     *  Delete a project
     *
     * @return void
     *
     * @throws \Exception
     */
    public function delete()
    {
        $id = $this->id;
        parent::delete();

        /* Delete all children from the project */
        Project\Issue::where('project_id', '=', $id)->delete();
        Project\Issue\Comment::where('project_id', '=', $id)->delete();
        Project\User::where('project_id', '=', $id)->delete();
        User\Activity::where('parent_id', '=', $id)->delete();
    }
}
