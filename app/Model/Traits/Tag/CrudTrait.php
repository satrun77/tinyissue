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
 * @property int $id
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
        $input['group'] = !array_key_exists('group', $input) ? 0 : $input['group'];

        return $this->fill($input)->save();
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
}
