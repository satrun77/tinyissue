<?php

namespace Tinyissue\Form;

use Tinyissue\Model\Role;

class User extends FormAbstract
{
    public function actions()
    {
        if ($this->isEditing()) {
            return [
                'submit' => 'update',
            ];
        }

        return ['submit' => 'add_user'];
    }

    public function fields()
    {
        $fields = [
            'firstname' => [
                'type'  => 'text',
                'label' => 'first_name',
            ],
            'lastname'  => [
                'type'  => 'text',
                'label' => 'last_name',
            ],
            'email'     => [
                'type'  => 'text',
                'label' => 'email',
            ],
        ];

        $fields += $this->innerFields();

        return $fields;
    }

    protected function passwordFields()
    {
        $fields = [];
        $fields['only_complete_if_changing_password'] = [
            'type' => 'legend',
        ];
        $fields['password'] = [
            'type'  => 'password',
            'label' => 'new_password',
        ];
        $fields['password_confirmation'] = [
            'type'  => 'password',
            'label' => 'confirm',
        ];

        return $fields;
    }

    protected function innerFields()
    {
        $fields = [
            'role_id' => [
                'type'    => 'select',
                'label'   => 'role',
                'options' => Role::dropdown(),
            ],
        ];

        if ($this->isEditing()) {
            $fields += $this->passwordFields();
        }

        return $fields;
    }

    public function rules()
    {
        $rules = [
            'firstname' => 'required|max:50',
            'lastname'  => 'required|max:50',
            'email'     => 'required|email',
        ];

        if ($this->isEditing()) {
            $rules['email'] .= '|unique:users,email,'.$this->getModel()->id;
            $rules['password'] = 'confirmed';
        }

        return $rules;
    }
}
