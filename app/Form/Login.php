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
        $fields = $this->emailField();
        $fields += $this->passwordField();
        $fields += $this->startGroup();
        $fields += $this->submitField();
        $fields += $this->rememberField();
        $fields += $this->closeGroup();

        return $fields;
    }

    /**
     * @return array
     */
    protected function emailField()
    {
        return [
            'email' => [
                'type'  => 'text',
                'label' => 'email',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function passwordField()
    {
        return [
            'password' => [
                'type'  => 'password',
                'label' => 'password',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function startGroup()
    {
        return [
            'group' => [
                'type'     => 'group',
                'addClass' => 'form-actions',
                'label'    => '',
                'required' => false,
            ],
        ];
    }

    /**
     * @return array
     */
    protected function submitField()
    {
        return [
            'login' => [
                'type'  => 'primary_submit',
                'value' => 'login',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function rememberField()
    {
        return [
            'remember' => [
                'type'     => 'checkbox',
                'required' => false,
                'text'     => 'remember_me',
                'inline'   => null,
            ],
        ];
    }

    /**
     * @return array
     */
    protected function closeGroup()
    {
        return [
            'closeGroup' => [
                'type' => 'closeGroup',
            ],
        ];
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
