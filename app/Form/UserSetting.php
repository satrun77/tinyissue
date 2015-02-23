<?php

namespace Tinyissue\Form;

use Illuminate\Contracts\Auth\Guard;

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
