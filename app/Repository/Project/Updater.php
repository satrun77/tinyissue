<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project;

use Illuminate\Support\Collection;
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;
use Tinyissue\Repository\RepositoryUpdater;

class Updater extends RepositoryUpdater
{
    /**
     * @var Project
     */
    protected $model;

    public function __construct(Project $model)
    {
        $this->model = $model;
    }

    /**
     * removes a user from a project.
     *
     * @param int $userId
     *
     * @return mixed
     */
    public function unassignUser($userId)
    {
        return $this->model->projectUsers()->where('user_id', '=', $userId)->delete();
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
        if ($userId <= 0) {
            return false;
        }

        return $this->model->projectUsers()->save(new Project\User([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]));
    }

    /**
     * Assign list of user ids to the project.
     *
     * @param array $userIds
     *
     * @return void
     */
    public function assignUsers(array $userIds)
    {
        foreach ($userIds as $userId) {
            $this->assignUser($userId);
        }
    }

    /**
     * Create a new project.
     *
     * @param array $input
     *
     * @return $this
     */
    public function create(array $input = [])
    {
        $this->model->fill($input)->save();

        $this->saveKanbanTags(array_get($input, 'columns', []));
        $this->assignUsers(array_get($input, 'user', []));

        return $this->model;
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
        $this->saveKanbanTags(array_get($attributes, 'columns', []));

        return $this->model->update($attributes);
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
        return $this->transaction('deleteProject');
    }

    protected function deleteProject()
    {
        // Remove issues
        $issues = $this->model->issues()->get();
        foreach ($issues as $issue) {
            $issue->delete();
        }

        // Remove project notes
        $notes = $this->model->notes()->get();
        foreach ($notes as $note) {
            $note->delete();
        }

        // Remove project users
        Project\User::where('project_id', '=', $this->model->id)->delete();

        // Remove user activities
        User\Activity::where('parent_id', '=', $this->model->id)->delete();

        // Remove kanban tags
        \DB::table('projects_kanban_tags')->where('project_id', '=', $this->model->id)->delete();

        // Remove the project
        $this->removeProjectStorage($this->model);

        return $this->model->delete();
    }

    /**
     * Save the project tags.
     *
     * @param array $tagIds
     *
     * @return bool
     */
    protected function saveKanbanTags(array $tagIds)
    {
        if (empty($tagIds)) {
            return true;
        }

        // Transform the user input tags into tag objects
        // Filter out invalid tags entered by the user
        $tags = new Collection($tagIds);
        $tags = $tags->transform(function ($tagNameOrId) {
            return Tag::find($tagNameOrId);
        })->filter(function ($tag) {
            return $tag instanceof Tag;
        });

        // Delete all existing
        $this->model->kanbanTags()->detach();

        // Save tags
        $kanbanTags = $this->model->kanbanTags();
        foreach ($tags as $position => $tag) {
            $kanbanTags->attach([$tag->id => ['position' => $position]]);
        }

        return true;
    }
}
