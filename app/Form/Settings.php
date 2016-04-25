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
 * Settings is a class to defines fields & rules for editing system settings.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Settings extends FormAbstract
{
    /**
     * An instance of project model.
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

        $settingsManager = app('tinyissue.settings');
        foreach ($settingsManager as $setting) {
            $method                = camel_case('field_' . $setting->key);
            $fields[$setting->key] = $this->{$method}($setting);
        }

        return $fields;
    }

    /**
     * Select enable/disable for public projects.
     *
     * @param Model\Setting $setting
     *
     * @return array
     */
    protected function fieldEnablePublicProjects(Model\Setting $setting)
    {
        return [
            'type'    => 'select',
            'label'   => $setting->name,
            'value'   => $setting->value,
            'options' => [Model\Setting::ENABLE => trans('tinyissue.enable'), Model\Setting::DISABLE => trans('tinyissue.disable')],
        ];
    }

    /**
     * Select enable/disable for public projects.
     *
     * @param Model\Setting $setting
     *
     * @return array
     */
    protected function fieldDateFormat(Model\Setting $setting)
    {
        $today = new \DateTime();
        $today = $today->format('Y-m-d H:i:s');

        return [
            'type'        => 'select',
            'label'       => 'date_format',
            'value'       => $setting->value,
            'options'     => [
                'D, d M Y H:i:s' => \Html::date($today, 'D, d M Y H:i:s'),
                'F jS, Y, g:i a' => \Html::date($today, 'F jS, Y, g:i a'),
                'd/F/Y g:i A'    => \Html::date($today, 'd/F/Y g:i A'),
                'd. F Y H:i'     => \Html::date($today, 'd. F Y H:i'),
                'Y-m-d H:i'      => \Html::date($today, 'Y-m-d H:i'),
                'd.m.y H:i'      => \Html::date($today, 'd.m.y H:i'),
            ],
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
