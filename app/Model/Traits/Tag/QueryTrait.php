<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Tag;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Query;
use Tinyissue\Model\Role;
use Tinyissue\Model\Tag as TagModel;

/**
 * QueryTrait is trait class containing the database queries methods for the Tag model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Eloquent\Builder with($relations)
 * @method Eloquent\Model   where($column, $operator = null, $value = null, $boolean = 'and')
 * @method Query\Builder    whereIn($column, $values, $boolean = 'and', $not = false)
 */
trait QueryTrait
{
    /**
     * Returns collection of all groups and eager load their tags.
     *
     * @return Eloquent\Collection
     */
    public function getGroupTags()
    {
        return $this->with([
            'tags' => function (Eloquent\Relations\HasMany $query) {
                $query->where(function (Eloquent\Builder $query) {
                    $query->where('role_limit', '<=', auth()->user()->role_id);
                    $query->orWhere('role_limit', '=', null);
                });
            },
        ])
            ->where('group', '=', true)->orderBy('group', 'DESC')->orderBy('name', 'ASC')->get();
    }

    /**
     * Search tags by name.
     *
     * @param string $term
     *
     * @return Eloquent\Collection|static[]
     */
    public function searchTags($term)
    {
        return $this->with('parent')->where('name', 'like', '%' . $term . '%')->where('parent_id', '<>', 0)->get();
    }

    /**
     * Returns tag groups list.
     *
     * @return array
     */
    public function groupsDropdown()
    {
        return $this->getGroups()->map(function ($group) {
            $group->keyname = 'tag:' . $group->id;
            $group->name = ucwords($group->name);

            return $group;
        })->lists('name', 'keyname')->all();
    }

    /**
     * Returns collection of all groups.
     *
     * @return Eloquent\Collection
     */
    public function getGroups()
    {
        return $this->where('group', '=', true)->orderBy('name', 'ASC')->get();
    }

    /**
     * Return tag by name.
     *
     * @param string $name
     *
     * @return static
     */
    public function getTagByName($name)
    {
        return static::where('name', '=', $name)->first();
    }

    /**
     * Returns collection of tags in status group.
     *
     * @return Eloquent\Relations\HasMany
     */
    public function getStatusTags()
    {
        return $this->getTagByName('status')->tags();
    }

    /**
     * Returns collection of tags in type group.
     *
     * @return Eloquent\Collection
     */
    public function getTypeTags()
    {
        return $this->getTagByName('type')->tags();
    }

    /**
     * Returns collection of tags in type group.
     *
     * @return Eloquent\Collection
     */
    public function getResolutionTags()
    {
        return $this->getTagByName('resolution')->tags();
    }
}
