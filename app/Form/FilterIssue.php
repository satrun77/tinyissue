<?php

namespace Tinyissue\Form;

use Request;
use Tinyissue\Model;

class FilterIssue extends FormAbstract
{
    protected $project;

    public function setup($params)
    {
        $this->project = $params['project'];
    }

    public function actions()
    {
        return [
            'filter' => [
                'name'  => 'filter',
                'label' => 'filter',
                'type'  => 'info_sm_submit',
            ]
        ];
    }

    public function fields()
    {
        $tagGroups = Model\Tag::where('group', '=', 1)->get()->map(function($group) {
            $group->keyname = 'tag:' . $group->id;
            $group->name = ucwords($group->name);
            return $group;
        })->lists('name', 'keyname');

        $sort = ['updated' => trans('tinyissue.updated')] + $tagGroups;

        $assignTo = [0 => trans('tinyissue.allusers')] + $this->project->users()->get()->lists('fullname', 'id');

        if (Request::has('tags')) {
            $tagIds = array_map('trim', explode(',', Request::input('tags')));
            $selectTags = Model\Tag::whereIn('id', $tagIds)->get()->map(function ($tag) {
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
            'tags'      => [
                'type'            => 'text',
                'placeholder'     => trans('tinyissue.tags'),
                'multiple'        => true,
                'class'           => 'tagit',
                'data_tokens'     => htmlentities($selectTags, ENT_QUOTES),
                'onGroupAddClass' => 'toolbar-item first',
            ],
            'sortby'    => [
                'type'            => 'select',
                'placeholder'     => trans('tinyissue.sortby'),
                'options'         => $sort,
                'onGroupAddClass' => 'toolbar-item first group',
            ],
            'sortorder' => [
                'type'            => 'select',
                'options'         => ['asc' => trans('tinyissue.sort_asc'), 'desc' => trans('tinyissue.sort_desc')],
                'onGroupAddClass' => 'toolbar-item',
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

    public function rules()
    {
        return [];
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
