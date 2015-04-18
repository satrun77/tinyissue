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
 * FilterIssue is a class to defines fields & rules for issue filter form
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class FilterIssue extends FormAbstract
{
    /**
     * An instance of project model
     *
     * @var Model\Project
     */
    protected $project;

    public function setup(array $params)
    {
        $this->project = $params['project'];
    }

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

    public function fields()
    {
        // Prefix tag groups with "tag:"
        $tagGroups = Model\Tag::where('group', '=', 1)->get()->map(function ($group) {
            $group->keyname = 'tag:' . $group->id;
            $group->name = ucwords($group->name);

            return $group;
        })->lists('name', 'keyname');

        // Array of sort optins
        $sort = ['updated' => trans('tinyissue.updated')] + $tagGroups;

        // Array of project users
        $assignTo = [0 => trans('tinyissue.allusers')] + $this->project->users()->get()->lists('fullname', 'id');

        // On submit, generate list of selected tags to populate the field
        if (Request::has('tags')) {
            $tagIds = array_map('trim', explode(',', Request::input('tags')));
            $selectTags = Model\Tag::whereIn('id', $tagIds)->get()->map(function (Model\Tag $tag) {
                return [
                    'value'   => $tag->id,
                    'label'   => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toJson();
        } else {
            $selectTags = '';
        }

        $fields = [
            'keyword'      => [
                'type'            => 'text',
                'placeholder'     => trans('tinyissue.keywords'),
                'onGroupAddClass' => 'toolbar-item first',
            ],
            'tags'      => [
                'type'            => 'text',
                'placeholder'     => trans('tinyissue.tags'),
                'multiple'        => true,
                'class'           => 'tagit',
                'data_tokens'     => htmlentities($selectTags, ENT_QUOTES),
                'onGroupAddClass' => 'toolbar-item',
            ],
            'sort' => [
                'type'     => 'groupField',
                'onGroupAddClass' => 'toolbar-item',
                'fields'   => [
                    'sortby' => [
                        'type'   => 'select',
                        'placeholder'     => trans('tinyissue.sortby'),
                        'options'         => $sort,
                        'onGroupClass'    => 'control-inline control-sortby',
                    ],
                    'sortorder' => [
                        'type'            => 'select',
                        'options'         => ['asc' => trans('tinyissue.sort_asc'), 'desc' => trans('tinyissue.sort_desc')],
                        'onGroupClass'    => 'control-inline control-sortorder',
                    ],
                ],
            ],
            'assignto'  => [
                'type'            => 'select',
                'placeholder'     => trans('tinyissue.assigned_to'),
                'options'         => $assignTo,
                'onGroupAddClass' => 'toolbar-item last',
            ],
        ];

        return $fields;
    }

    public function getRedirectUrl()
    {
        return $this->project->to();
    }

    public function openType()
    {
        return 'inline_open';
    }
}
