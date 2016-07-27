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
use Illuminate\Support\Collection;
use Tinyissue\Model\Tag;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the Project model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 *
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method   Query\Builder join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
 * @method   Project       fill(array $attributes)
 * @method   RelationTrait projectUsers()
 */
trait CrudTrait
{
    /**
     * removes a user from a project.
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
     * Create a new project.
     *
     * @param array $input
     *
     * @return $this
     */
    public function createProject(array $input = [])
    {
        $this->fill($input)->save();

        if (!empty($input['columns'])) {
            $this->saveTags($input['columns']);

            unset($input['columns']);
        }

        /* Assign selected users to the project */
        if (isset($input['user']) && count($input['user']) > 0) {
            foreach ($input['user'] as $id) {
                $this->assignUser($id);
            }
        }

        return $this;
    }

    /**
     * Update project details.
     *
     * @param array $attributes
     *
     * @return bool
     */
    public function update(array $attributes = [])
    {
        if (array_key_exists('columns', $attributes)) {
            $this->saveTags($attributes['columns']);

            unset($attributes['columns']);
        }

        return parent::update($attributes);
    }

    /**
     * Save the project tags.
     *
     * @param array $tagIds
     *
     * @return bool
     */
    public function saveTags(array $tagIds)
    {
        // Transform the user input tags into tag objects
        // Filter out invalid tags entered by the user
        $tags = new Collection($tagIds);
        $tags = $tags->transform(function ($tagNameOrId) {
            return Tag::find($tagNameOrId);
        })->filter(function ($tag) {
            return $tag instanceof Tag;
        });

        // Delete all existing
        $this->kanbanTags()->detach();

        // Save tags
        $kanbanTags = $this->kanbanTags();
        foreach ($tags as $position => $tag) {
            $kanbanTags->attach([$tag->id => ['position' => $position]]);
        }

        return true;
    }

    /**
     * Assign a user to a project.
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
     *  Delete a project.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function delete()
    {
        // Remove issues
        $issues = $this->issues()->get();
        foreach ($issues as $issue) {
            $issue->delete();
        }

        // Remove project notes
        $notes = $this->notes()->get();
        foreach ($notes as $note) {
            $note->delete();
        }

        // Remove project users
        Project\User::where('project_id', '=', $this->id)->delete();

        // Remove user activities
        User\Activity::where('parent_id', '=', $this->id)->delete();

        // Remove kanban tags
        \DB::table('projects_kanban_tags')->where('project_id', '=', $this->id)->delete();

        // Remove the project
        $dir = config('filesystems.disks.local.root') . '/' . config('tinyissue.uploads_dir') . '/' . $this->id;
        if (is_dir($dir)) {
            rmdir($dir);
        }

        return parent::delete();
    }
}
