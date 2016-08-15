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
use Tinyissue\Model\Role;

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
        $roles  = Role::dropdown()->prepend('Disabled');
        $tag    = new Model\Tag();
        $fields = [
            'name' => [
                'type'  => 'text',
                'label' => 'name',
            ],
            'parent_id' => [
                'type'    => 'select',
                'label'   => 'group',
                'options' => $tag->getGroups()->pluck('name', 'id')->all(),
            ],
            'bgcolor' => [
                'type'  => 'color',
                'label' => 'bgcolor',
            ],
            'role_limit' => [
                'type'    => 'select',
                'label'   => 'limit_access',
                'options' => $roles,
                'help'    => trans('tinyissue.role_limit_help'),
            ],
            'message_limit' => [
                'type'    => 'select',
                'label'   => 'limit_message',
                'options' => $roles,
                'help'    => trans('tinyissue.limit_message_help'),
            ],
            'readonly' => [
                'type'    => 'select',
                'label'   => 'readonly',
                'options' => $roles,
                'help'    => trans('tinyissue.readonly_tag_help'),
            ],
        ];

        return $fields;
    }

    /**
     * @return array
     */
    public function rules()
    {
        // Tag to exclude in unique test while editing
        $excludeTag = $this->isEditing() ? ',' . $this->getModel()->id : '';

        $rules = [
            'name'      => 'required|max:200|unique:tags,name' . $excludeTag,
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

        return (new Model\Tag())->to('new');
    }
}
