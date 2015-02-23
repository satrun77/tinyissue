<?php

namespace Tinyissue\Form;

use Tinyissue\Model\Project as ProjectModel;

class Project extends FormAbstract
{
    public function actions()
    {
        if ($this->isEditing()) {
            return [
                'submit' => 'update',
                'delete' => [
                    'type' => 'danger_submit',
                    'label' => trans('tinyissue.delete_something', ['name' => $this->getModel()->name]),
                    'class' => 'delete-project',
                    'name' => 'delete-project',
                    'data-message' => trans('tinyissue.delete_project_confirm'),
                ],
            ];
        }

        return [
            'submit' => 'create_project',
        ];
    }

    public function fields()
    {
        $fields = [
            'name' => [
                'type'  => 'text',
                'label' => 'name',
            ],
            'default_assignee' => [
                'type'  => 'hidden',
                'id'    => 'default_assignee-id',
            ],
        ];

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
                'options' => [0 => ''] + $this->getModel()->users()->get()->lists('fullname', 'id'),
            ];
        }

        return $fields;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|max:250',
            'user' => 'array|min:1',
        ];

        return $rules;
    }
}
