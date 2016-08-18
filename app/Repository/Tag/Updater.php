<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Tag;

use Tinyissue\Model\Tag;
use Tinyissue\Repository\RepositoryUpdater;

class Updater extends RepositoryUpdater
{
    /**
     * @var Tag
     */
    protected $model;

    public function __construct(Tag $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new tag.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function create(array $input)
    {
        $input['group'] = !array_key_exists('group', $input) ? 0 : $input['group'];

        $this->model->fill($input)->save();

        return $this->model;
    }

    /**
     * Delete tag.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        return $this->transaction('deleteTag');
    }

    protected function deleteTag()
    {
        // Remove kanban tags
        \DB::table('projects_kanban_tags')->where('tag_id', '=', $this->model->id)->delete();

        // Remove relation to issues
        \DB::table('projects_issues_tags')->where('tag_id', '=', $this->model->id)->delete();

        return $this->model->delete();
    }
}
