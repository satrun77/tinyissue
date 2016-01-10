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
 * Settings is a class to defines fields & rules for editing system settings
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Settings extends FormAbstract
{
    /**
     * An instance of project model
     *
     * @var Model\Settings
     */
    protected $settings;

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'submit' => 'save',
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = [];

        $settings = Model\Settings::all();
        foreach ($settings as $setting) {
            $method = camel_case('field_' . $setting->key);
            $fields[$setting->key] = $this->{$method}($setting);
        }

        return $fields;
    }

    /**
     * Select enable/disable for public projects
     *
     * @param Model\Settings $setting
     * @return array
     */
    protected function fieldEnablePublicProjects(Model\Settings $setting)
    {
        return [
            'type'    => 'select',
            'label'   => $setting->name,
            'value'   => $setting->value,
            'options' => [Model\Settings::ENABLE => trans('tinyissue.enable'), Model\Settings::DISABLE => trans('tinyissue.disable')],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
        ];

        return $rules;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return 'administration/settings';
    }
}
