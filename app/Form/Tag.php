<?php

namespace Tinyissue\Form;

use Tinyissue\Model;

class Tag extends FormAbstract
{
    public function setup($params)
    {
        if (isset($params['tag'])) {
            $this->editingModel($params['tag']);
        }
    }

    public function actions()
    {
        return [
            'submit' => $this->isEditing() ? 'update_tag' : 'create_tag',
        ];
    }

    public function fields()
    {
        $tag = new Model\Tag();
        $fields = [
            'name' => [
                'type'  => 'text',
                'label' => 'name',
            ],
            'group'  => [
                'type'  => 'select',
                'label' => 'group',
                'options' => [0 => ''] + $tag->getGroups()->lists('name', 'id'),
            ],
            'bgcolor'  => [
                'type'  => 'color',
                'label' => 'bgcolor',
            ],
        ];

        return $fields;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|max:200',
            'bgcolor'  => 'required',
        ];

        return $rules;
    }

    public function getRedirectUrl()
    {
        if ($this->isEditing()) {
            return $this->getModel()->to('edit');
        }

        $tag = new Model\Tag();
        return $tag->to('new');
    }
}
