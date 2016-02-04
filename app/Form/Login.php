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
 * Login is a class to defines fields & rules for the login form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Login extends FormAbstract
{
    /**
     * @return array
     */
    public function fields()
    {
        $fields = [
            'email' => [
                'type'  => 'text',
                'label' => 'email',
            ],
            'password' => [
                'type'  => 'password',
                'label' => 'password',
            ],
            'group' => [
                'type'     => 'group',
                'addClass' => 'form-actions',
                'label'    => '',
                'required' => false,
            ],
            'login' => [
                'type'  => 'primary_submit',
                'value' => 'login',
            ],
            'remember' => [
                'type'     => 'checkbox',
                'required' => false,
                'text'     => 'remember_me',
                'inline'   => null,
            ],
            'closeGroup' => [
                'type' => 'closeGroup',
            ],
        ];

        return $fields;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'password' => 'required',
            'email'    => 'required|email',
        ];

        return $rules;
    }
}
