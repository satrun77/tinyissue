<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Extensions\Html;

use Former;
use Former\Traits\Field;
use Request;
use Tinyissue\Contracts\Form\FormInterface;

/**
 * FormBuilder is a class to extend Laravel FormBuilder to add extra view macro.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class FormBuilder extends \Collective\Html\FormBuilder
{
    /**
     * Render Form object into Html form with Former.
     *
     * @param FormInterface $form
     * @param array         $attributes
     *
     * @return string
     */
    public function form(FormInterface $form, array $attributes = [])
    {
        /** @var \Former\Form\Form $former Start former instance */
        $former = call_user_func(['\Former', $form->openType()]);
        $this->setupForm($former, $attributes, $form->rules());

        // Render open form
        $html = (string) $former;

        // Render fields
        $html .= $this->renderFields($form->fields(), is_null($form->getModel()));

        // Render actions
        $html .= $this->actions($form);

        // Close the opened form
        $html .= (string) Former::close();

        return $html;
    }

    /**
     * Generate Former field.
     *
     * @param string $name
     * @param array  $field
     *
     * @return Field
     */
    public function element($name, array $field)
    {
        $filterKeys = ['type'];
        $attributes = array_diff_key($field, array_flip($filterKeys));

        // Create field with name
        $element = call_user_func(['\Former', $field['type']], $name);

        // Create field attributes
        $this->setAttributes($attributes, $element);

        return $element;
    }

    /**
     * Render form actions.
     *
     * @param FormInterface $form
     *
     * @return string
     */
    public function actions(FormInterface $form)
    {
        $output = '';
        $buttons = $form->actions();
        if (!empty($buttons)) {
            $actions = Former::actions()->addClass('form-actions');
            foreach ($buttons as $options) {
                if (is_array($options)) {
                    $actions->{$options['type']}($options['label'], $options);
                } else {
                    $actions->primary_submit(trans('tinyissue.' . $options));
                }
            }
            $output .= (string) $actions;
        }

        return $output;
    }

    /**
     * @param       $former
     * @param array $attributes
     * @param array $rules
     *
     * @return void
     */
    protected function setupForm($former, array $attributes, array $rules)
    {
        $this->setAttributes($attributes, $former);

        $former->rules($rules);
    }

    /**
     * @param array  $attributes
     * @param object $object
     *
     * @return void
     */
    protected function setAttributes(array $attributes, $object)
    {
        array_walk($attributes, function ($value, $attr) use ($object) {
            if ($value === null) {
                $object->$attr();
            } else {
                $object->$attr($value);
            }
        });
    }

    /**
     * @param array $fields
     * @param bool  $populate
     *
     * @return string
     */
    protected function renderFields(array $fields, $populate = false)
    {
        $output = '';
        foreach ($fields as $name => $field) {
            $element = $this->element($name, $field);

            if ($element instanceof Field && $populate) {
                $element->value = Request::input($name);
            }

            $output .= (string) $element;
        }

        return $output;
    }
}
