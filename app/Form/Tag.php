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

use Tinyissue\Model;

/**
 * Tag is a class to defines fields & rules for add/edit tag form
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Tag extends FormAbstract
{
    public function setup(array $params)
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
