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

/**
 * Login is a class to defines fields & rules for the login form
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
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
