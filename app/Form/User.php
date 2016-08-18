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

use Tinyissue\Extensions\Model\FetchRoleTrait;
use Tinyissue\Model\User as UserModel;

/**
 * User is a class to defines fields & rules for add/edit user form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class User extends FormAbstract
{
    use FetchRoleTrait;

    /**
     * @return array
     */
    public function actions()
    {
        if ($this->isEditing()) {
            return [
                'submit' => 'update',
            ];
        }

        return ['submit' => 'add_user'];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = $this->firstNameField();
        $fields += $this->lastNameField();
        $fields += $this->emailField();
        $fields += $this->privateField();
        $fields += $this->passwordField();
        $fields += $this->innerFields();

        return $fields;
    }

    /**
     * For sub-classes to add extra fields or remove fields.
     *
     * @return array
     */
    protected function innerFields()
    {
        $fields = $this->extendedSettingsGroup();
        $fields += $this->roleField();
        $fields += $this->statusField();
        $fields += $this->passwordFields();

        return $fields;
    }

    /**
     * @return array
     */
    protected function firstNameField()
    {
        return [
            'firstname' => [
                'type'  => 'text',
                'label' => 'first_name',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function lastNameField()
    {
        return [
            'lastname' => [
                'type'  => 'text',
                'label' => 'last_name',
            ],
        ];
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
    protected function privateField()
    {
        return [
            'private' => [
                'type'    => 'select',
                'label'   => 'visibility',
                'options' => [
                    UserModel::PRIVATE_YES => trans('tinyissue.private'),
                    UserModel::PRIVATE_NO  => trans('tinyissue.public'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function passwordField()
    {
        $fields = [];

        if (!$this->isEditing()) {
            $fields['password'] = [
                'type'         => 'password',
                'label'        => 'password',
                'autocomplete' => 'off',
            ];
        }

        return $fields;
    }

    /**
     * Return password fields.
     *
     * @return array
     */
    protected function passwordFields()
    {
        $fields = [];

        if ($this->isEditing()) {
            $fields['only_complete_if_changing_password'] = [
                'type' => 'legend',
            ];
            $fields['password'] = [
                'type'         => 'password',
                'label'        => 'new_password',
                'autocomplete' => 'off',
            ];
            $fields['password_confirmation'] = [
                'type'         => 'password',
                'label'        => 'confirm',
                'autocomplete' => 'off',
            ];
        }

        return $fields;
    }

    /**
     * @return array
     */
    protected function extendedSettingsGroup()
    {
        return [
            'extended_user_settings' => [
                'type' => 'legend',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function roleField()
    {
        return [
            'role_id' => [
                'type'    => 'select',
                'label'   => 'role',
                'options' => $this->getRoleNameDropdown(),
            ],
        ];
    }

    /**
     * @return array
     */
    protected function statusField()
    {
        return [
            'status' => [
                'type'    => 'select',
                'label'   => 'Status',
                'options' => UserModel::getStatuses(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'firstname' => 'required|max:50',
            'lastname'  => 'required|max:50',
            'email'     => 'required|email',
        ];

        if ($this->isEditing()) {
            $rules['email'] .= '|unique:users,email,' . $this->getModel()->id;
            $rules['password'] = 'confirmed';
        } else {
            $rules['email'] .= '|unique:users,email';
        }

        return $rules;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        if ($this->isEditing()) {
            return 'administration/users/edit/' . $this->getModel()->id;
        }

        return 'administration/users/add/';
    }
}
