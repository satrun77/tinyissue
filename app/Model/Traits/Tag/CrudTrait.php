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
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the Tag model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Eloquent\Model where($column, $operator = null, $value = null, $boolean = 'and')
 */
trait CrudTrait
{
    /**
     * Create a new tag.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function createTag(array $input)
    {
        return $this->fill($this->prepareTagToSave($input))->save();
    }

    /**
     * Update the tags in the database.
     *
     * @param array $attributes
     *
     * @return bool|int
     */
    public function update(array $attributes = [])
    {
        return parent::update($this->prepareTagToSave($attributes));
    }

    /**
     * Create a new tag if valid or return existing one.
     *
     * @param string   $name
     * @param null|Tag $parent
     *
     * @return bool|$this
     */
    public function validOrCreate($name, Tag $parent = null)
    {
        $group = $parent === null ? true : false;
        $tag   = $this->where('name', '=', $name)->first();
        if ($tag && $tag->group != $group) {
            return false;
        }

        if (!$tag) {
            $tag        = new Tag();
            $tag->name  = $name;
            $tag->group = $group;
            if (!is_null($parent)) {
                $tag->parent_id = $parent->id;
                $tag->setRelation('parent', $parent);
            }
            $tag->save();
        }

        return $tag;
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
        // Remove kanban tags
        \DB::table('projects_kanban_tags')->where('tag_id', '=', $this->id)->delete();

        // Remove relation to issues
        \DB::table('projects_issues_tags')->where('tag_id', '=', $this->id)->delete();

        return parent::delete();
    }

    /**
     * Prepare tag details to save.
     *
     * @param array $input
     *
     * @return array
     */
    protected function prepareTagToSave(array $input)
    {
        $input['group'] = 0;

        return $input;
    }
}
