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

use Tinyissue\Extensions\Model\FetchTagsTrait;
use Tinyissue\Model;

/**
 * FilterIssue is a class to defines fields & rules for issue filter form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class FilterIssue extends FormAbstract
{
    use FetchTagsTrait;

    /**
     * An instance of project .
     *
     * @var \Tinyissue\Model\Project
     */
    protected $project;

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
        $tagGroups = Model\Tag::instance()->getGroupsDropdown();

        // Array of sort optins
        $sort = ['updated' => trans('tinyissue.updated')] + $tagGroups;

        // Array of project users
        $assignTo = [0 => trans('tinyissue.allusers')] + $this->project->getUsersCanFixIssue()->dropdown('fullname');

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
                'options'         => $this->getTags('status')->dropdown('fullname'),
            ],
            'tag_type' => [
                'type'            => 'select',
                'placeholder'     => trans('tinyissue.type'),
                'onGroupAddClass' => 'toolbar-item',
                'options'         => $this->getTags('type')->dropdown('fullname'),
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
