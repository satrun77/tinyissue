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
use Tinyissue\Model\Tag as TagModel;

/**
 * Tag is a class to defines fields & rules for add/edit tag form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Tag extends FormAbstract
{
    use FetchRoleTrait;

    /**
     * @param array $params
     *
     * @return void
     */
    public function setup(array $params)
    {
        if (isset($params['tag'])) {
            $this->setModel($params['tag']);
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
        $fields = $this->nameField();
        $fields += $this->parentField();
        $fields += $this->colorField();
        $fields += $this->roleLimitField();
        $fields += $this->messageLimitField();
        $fields += $this->readonlyField();

        return $fields;
    }

    /**
     * @return array
     */
    protected function nameField()
    {
        return [
            'name' => [
                'type'  => 'text',
                'label' => 'name',
            ],
        ];
    }

    protected function parentField()
    {
        return [
            'parent_id' => [
                'type'    => 'select',
                'label'   => 'group',
                'options' => TagModel::instance()->getGroups()->dropdown(),
            ],
        ];
    }

    /**
     * @return array
     */
    protected function colorField()
    {
        return [
            'bgcolor' => [
                'type'  => 'color',
                'label' => 'bgcolor',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function roleLimitField()
    {
        $roles = $this->getRoleNameDropdown();

        return [
            'role_limit' => [
                'type'    => 'select',
                'label'   => 'limit_access',
                'options' => $roles,
                'help'    => trans('tinyissue.role_limit_help'),
            ],
        ];
    }

    /**
     * @return array
     */
    protected function messageLimitField()
    {
        $roles = $this->getRoleNameDropdown();

        return [
            'message_limit' => [
                'type'    => 'select',
                'label'   => 'limit_message',
                'options' => $roles,
                'help'    => trans('tinyissue.limit_message_help'),
            ],
        ];
    }

    /**
     * @return array
     */
    protected function readonlyField()
    {
        $roles = $this->getRoleNameDropdown();

        return [
            'readonly' => [
                'type'    => 'select',
                'label'   => 'readonly',
                'options' => $roles,
                'help'    => trans('tinyissue.readonly_tag_help'),
            ],
        ];
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
        return $this->getModel()->to($this->isEditing() ? 'edit' : 'new');
    }
}
