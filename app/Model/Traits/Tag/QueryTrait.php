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
use Tinyissue\Model\Tag;

/**
 * QueryTrait is trait class containing the database queries methods for the Tag model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Eloquent\Model with($relations)
 * @method Eloquent\Model where()
 */
trait QueryTrait
{
    /**
     * Returns collection of all groups and eager load their tags
     *
     * @return Eloquent\Collection
     */
    public function getGroupTags()
    {
        return $this->with('tags')->where('group', '=', true)->orderBy('group', 'DESC')->orderBy('name', 'ASC')->get();
    }

    /**
     * Search tags by name
     *
     * @param string $term
     *
     * @return Eloquent\Collection|static[]
     */
    public function searchTags($term)
    {
        return $this->with('parent')->where('name', 'like', '%' . $term . '%')->where('group', '=', false)->get();
    }

    /**
     * Returns tag groups list
     *
     * @return array
     */
    public function groupsDropdown()
    {
        return $this->getGroups()->map(function ($group) {
            $group->keyname = 'tag:' . $group->id;
            $group->name = ucwords($group->name);

            return $group;
        })->lists('name', 'keyname');
    }

    /**
     * Returns collection of all groups
     *
     * @return Eloquent\Collection
     */
    public function getGroups()
    {
        return $this->where('group', '=', true)->orderBy('name', 'ASC')->get();
    }

    /**
     * Returns Json string of tags details.
     * Used for JS auto-complete
     *
     * @param string|array $ids
     *
     * @return string
     */
    public function tagsToJson($ids)
    {
        if (!is_array($ids)) {
            $ids = array_map('trim', explode(',', $ids));
        }

        return $this->whereIn('id', $ids)->get()->map([$this, 'mapTagDetails'])->toJson();
    }

    /**
     * Callback function to return tag details
     *
     * @param Tag $tag
     *
     * @return array
     */
    public function mapTagDetails(Tag $tag)
    {
        return [
            'value'   => $tag->id,
            'label'   => $tag->fullname,
            'bgcolor' => $tag->bgcolor,
        ];
    }
}
