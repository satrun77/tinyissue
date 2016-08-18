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

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Tag;
use Tinyissue\Repository\Repository;

class Fetcher extends Repository
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
     * Returns collection of all groups and eager load their tags.
     *
     * @return Collection
     */
    public function getGroupWithTags()
    {
        return $this->model->with('userAccessibleTags')->groupOnly()->get();
    }

    /**
     * Returns tag groups list.
     *
     * @return array
     */
    public function getGroupsDropdown()
    {
        return $this->getGroups()->map(function ($group) {
            $group->keyname = 'tag:' . $group->id;
            $group->name = ucwords($group->name);

            return $group;
        })->dropdown('name', 'keyname');
    }

    /**
     * Returns collection of all groups.
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->model->groupOnly()->get();
    }

    /**
     * Returns collection of tags in status group.
     *
     * @return Collection
     */
    public function getStatusTags()
    {
        return $this->model->ofType('status')->tags;
    }

    /**
     * Returns collection of tags in type group.
     *
     * @return Collection
     */
    public function getTypeTags()
    {
        return $this->model->ofType('type')->tags;
    }

    /**
     * Returns collection of tags in type group.
     *
     * @return Collection
     */
    public function getResolutionTags()
    {
        return $this->model->ofType('resolution')->tags;
    }
}
