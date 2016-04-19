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
 * Tag is a class to defines fields & rules for add/edit tag form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Tag extends FormAbstract
{
    /**
     * @param array $params
     *
     * @return void
     */
    public function setup(array $params)
    {
        if (isset($params['tag'])) {
            $this->editingModel($params['tag']);
        }
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'submit' => $this->isEditing() ? 'update_tag' : 'create_tag',
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $tag    = new Model\Tag();
        $fields = [
            'name' => [
                'type'  => 'text',
                'label' => 'name',
            ],
            'parent_id' => [
                'type'    => 'select',
                'label'   => 'group',
                'options' => $tag->getGroups()->lists('name', 'id')->all(),
            ],
            'bgcolor' => [
                'type'  => 'color',
                'label' => 'bgcolor',
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
            'name'      => 'required|max:200',
            'parent_id' => 'required',
            'bgcolor'   => 'required',
        ];

        return $rules;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        if ($this->isEditing()) {
            return $this->getModel()->to('edit');
        }

        $tag = new Model\Tag();

        return $tag->to('new');
    }
}
