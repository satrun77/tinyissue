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

use Illuminate\Contracts\Auth\Guard;

/**
 * UserSetting is a class to defines fields & rules for add/edit user settings form
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class UserSetting extends User
{
    public function __construct(Guard $model)
    {
        $this->editingModel($model->user());
    }

    public function actions()
    {
        return [
            'submit' => 'update',
        ];
    }

    protected function innerFields()
    {
        $fields = [
            'language' => [
                'type'    => 'select',
                'label'   => 'language',
                'options' => $this->getModel()->getLanguages(),
            ],
        ];
        $fields += $this->passwordFields();

        return $fields;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules['password'] = 'confirmed';
        $rules['language'] = 'required';

        return $rules;
    }
}
