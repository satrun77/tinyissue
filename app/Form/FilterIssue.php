<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Form;

use Request;
use Tinyissue\Model;

/**
 * FilterIssue is a class to defines fields & rules for issue filter form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class FilterIssue extends FormAbstract
{
    /**
     * An instance of project model.
     *
     * @var Model\Project
     */
    protected $project;

    /**
     * Collection of all tags.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $tags = null;

    /**
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    protected function getTags($type = null)
    {
        if ($this->tags === null) {
            $this->tags = (new Model\Tag())->getGroupTags();
        }

        if ($type) {
            return $this->tags->where('name', $type)->first()->tags;
        }

        return $this->tags;
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function setup(array $params)
    {
        $this->project = $params['project'];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'filter' => [
                'name'  => 'filter',
                'label' => 'filter',
                'type'  => 'info_submit',
            ],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        // Prefix tag groups with "tag:"
        $tagGroups = (new Model\Tag())->groupsDropdown();

        // Array of sort optins
        $sort = ['updated' => trans('tinyissue.updated')] + $tagGroups;

        // Array of project users
        $assignTo = [0 => trans('tinyissue.allusers')] + $this->project->users()->get()->lists('fullname', 'id')->all();

        // On submit, generate list of selected tags to populate the field
        if (Request::has('tags')) {
            $selectTags = (new Model\Tag())->tagsToJson(Request::input('tags'));
        } else {
            $selectTags = '';
        }

        $fields = [
            'keyword' => [
                'type'            => 'text',
                'placeholder'     => trans('tinyissue.keywords'),
                'onGroupAddClass' => 'toolbar-item first',
            ],
            'tag_status' => [
                'type'            => 'select',
                'placeholder'     => trans('tinyissue.status'),
                'onGroupAddClass' => 'toolbar-item',
                'options'         => $this->getTags('status')->lists('fullname', 'id'),
            ],
            'tag_type' => [
                'type'            => 'select',
                'placeholder'     => trans('tinyissue.type'),
                'onGroupAddClass' => 'toolbar-item',
                'options'         => $this->getTags('type')->lists('fullname', 'id'),
            ],
            'sort' => [
                'type'            => 'groupField',
                'onGroupAddClass' => 'toolbar-item',
                'fields'          => [
                    'sortby' => [
                        'type'         => 'select',
                        'placeholder'  => trans('tinyissue.sortby'),
                        'options'      => $sort,
                        'onGroupClass' => 'control-inline control-sortby',
                    ],
                    'sortorder' => [
                        'type'         => 'select',
                        'options'      => ['asc' => trans('tinyissue.sort_asc'), 'desc' => trans('tinyissue.sort_desc')],
                        'onGroupClass' => 'control-inline control-sortorder',
                    ],
                ],
            ],
            'assignto' => [
                'type'            => 'select',
                'placeholder'     => trans('tinyissue.assigned_to'),
                'options'         => $assignTo,
                'onGroupAddClass' => 'toolbar-item last',
            ],
        ];

        return $fields;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->project->to();
    }

    /**
     * @return string
     */
    public function openType()
    {
        return 'inline_open';
    }
}
