<?php

namespace Tinyissue\Form;

class Login extends FormAbstract
{
    public function actions()
    {
        return [
            'submit' => [
                'type' => 'primary_submit',
                'label' => 'login',
            ],
        ];
    }

    public function fields()
    {
        $fields = [
            'email'    => [
                'type'  => 'text',
                'label' => 'email',
            ],
            'password' => [
                'type'  => 'password',
                'label' => 'password',
            ],
            'remember' => [
                'type'   => 'checkbox',
                'text'   => 'remember_me',
                'inline' => null,
                'onGroupAddClass' => 'remember-me',
            ],
        ];

        return $fields;
    }

    public function rules()
    {
        $rules = [
            'password' => 'required',
            'email'    => 'required|email',
        ];

        return $rules;
    }
}
