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

use Tinyissue\Model\Project as ProjectModel;
use Tinyissue\Model\Tag as TagModel;

/**
 * Project is a class to defines fields & rules for add/edit project form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Project extends FormAbstract
{
    /**
     * @return array
     */
    public function actions()
    {
        if ($this->isEditing()) {
            return [
                'submit' => 'update',
                'delete' => [
                    'type'         => 'danger_submit',
                    'label'        => trans('tinyissue.delete_something', ['name' => $this->getModel()->name]),
                    'class'        => 'delete-project',
                    'name'         => 'delete-project',
                    'data-message' => trans('tinyissue.delete_project_confirm'),
                ],
            ];
        }

        return [
            'submit' => 'create_project',
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = [
            'name' => [
                'type'  => 'text',
                'label' => 'name',
            ],
            'private' => [
                'type'    => 'select',
                'label'   => 'visibility',
                'options' => [ProjectModel::PRIVATE_YES => trans('tinyissue.private'), ProjectModel::PRIVATE_NO => trans('tinyissue.public')],
            ],
            'default_assignee' => [
                'type' => 'hidden',
                'id'   => 'default_assignee-id',
            ],
        ];

        // On create project can assign users
        // On edit project can change status or default assignee
        if (!$this->isEditing()) {
            $fields['user'] = [
                'type'        => 'selectUser',
                'label'       => 'assign_users',
                'id'          => 'add-user-project',
                'placeholder' => trans('tinyissue.assign_a_user'),
            ];
        } else {
            $fields['status'] = [
                'type'    => 'select',
                'label'   => 'status',
                'options' => [ProjectModel::STATUS_OPEN => trans('tinyissue.open'), ProjectModel::STATUS_ARCHIVED => trans('tinyissue.archived')],
            ];
            $fields['default_assignee'] = [
                'type'    => 'select',
                'label'   => 'default_assignee',
                'options' => [0 => ''] + $this->getModel()->usersCanFixIssue()->get()->lists('fullname', 'id')->all(),
            ];
        }

        $fields['kanban_board'] = [
            'type' => 'legend',
        ];

        $statusTags = (new TagModel())->getStatusTags()->get()->implode('fullname', ', ');
        if ($this->isEditing()) {
            $selectTags = $this->getModel()->kanbanTags()->get()->filter(function (TagModel $tag) {
                return !($tag->name == TagModel::STATUS_OPEN || $tag->name == TagModel::STATUS_CLOSED);
            })->map(function (TagModel $tag) {
                return [
                    'value'   => $tag->id,
                    'label'   => ($tag->fullname),
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toJson();
        } else {
            $selectTags = (new TagModel())->tagsToJson(\Request::input('tags'));
        }
        $fields['columns'] = [
            'type'        => 'text',
            'label'       => 'columns',
            'placeholder' => trans('tinyissue.tags'),
            'multiple'    => true,
            'class'       => 'tagit',
            'help'        => trans('tinyissue.columns_help', ['status' => $statusTags]),
            'data_tokens' => htmlentities($selectTags, ENT_QUOTES),
        ];

        return $fields;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|max:250',
            'user' => 'array|min:1',
        ];

        return $rules;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        if ($this->isEditing()) {
            return $this->getModel()->to('edit');
        }

        return 'projects/new';
    }
}
