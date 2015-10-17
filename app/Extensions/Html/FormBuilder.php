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
use Illuminate\Database\Eloquent\Model;
use Request;
use Tinyissue\Form\FormInterface;

/**
 * FormBuilder is a class to extend Laravel FormBuilder to add extra view macro
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class FormBuilder extends \Illuminate\Html\FormBuilder
{
    /**
     * Render Form object into Html form with Former
     *
     * @param FormInterface $form
     * @param array         $attrs
     *
     * @return string
     */
    public function form(FormInterface $form, array $attrs = [])
    {
        // Populate form from edited model
        $model = $form->getModel();
        if ($model instanceof Model) {
            Former::populate($model);
        }

        // Start a form and add rules
        $formType = $form->openType();
        $former = Former::$formType();
        array_walk($attrs, function ($value, $attr) use ($former) {
            if ($value === null) {
                $former->$attr();
            } else {
                $former->$attr($value);
            }
        });
        $former->rules($form->rules());

        // Generate form fields
        $output = $former;
        $fields = $form->fields();
        foreach ($fields as $name => $field) {
            $element = $this->element($name, $field);

            if ($element instanceof Field) {
                if (null === $model) {
                    $element->value = Request::input($name);
                }
            }

            $output .= $element;
        }

        // Generate form actions
        $output .= $this->actions($form);

        // Close the opened form
        $output .= Former::close();

        return $output;
    }

    /**
     * Generate Former field
     *
     * @param string $name
     * @param array  $field
     *
     * @return Field
     */
    public function element($name, array $field)
    {
        $filterKeys = ['type'];
        $attrs = array_diff_key($field, array_flip($filterKeys));

        // Create field with name
        $element = Former::$field['type']($name);

        // Create field attributes
        array_walk($attrs, function ($value, $attr) use ($element) {
            if ($value === null) {
                $element->$attr();
            } else {
                $element->$attr($value);
            }
        });

        return $element;
    }

    /**
     * Render form actions
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
            $output .= $actions;
        }

        return $output;
    }
}
