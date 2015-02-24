<?php

namespace Tinyissue\Form\Former\Fields;

use Former\Traits\Field;
use Illuminate\Container\Container;

class GroupText extends Field
{
    /**
     * A list of class properties to be added to attributes
     *
     * @var array
     */
    protected $injectedProperties = [];

    protected $element = 'div';

    protected $fields;

    public function fields($fields)
    {
        array_walk($fields, function (&$field, $name) {
            $field = \Form::element($this->name . '[' . $name . ']', $field);
            $field->setAttribute('id', $this->name . '-' . $name);
        });
        $this->fields = $fields;

        return $this;
    }

    public function render()
    {
        try {
            $this->addClass('group-text');
            $this->setId();

            return $this->open() . $this->getContent() . $this->close();
        }catch (\Exception $e) {
            echo $e;
        }
    }

    public function getContent()
    {
        $output = '';
        foreach ($this->fields as $field) {
            $output .= $field->__toString();
        }

        return $output;
    }

    public function getValue()
    {
        if (!is_array($this->fields)) {
            return [];
        }

        return array_map(function ($field) {
            return $field->getValue();
        }, $this->fields);
    }
}
