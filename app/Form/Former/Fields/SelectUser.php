<?php

namespace Tinyissue\Form\Former\Fields;

use Former\Helpers;
use Former\Traits\Field;
use Illuminate\Container\Container;

/**
 * Renders all basic input types
 */
class SelectUser extends Field
{
    /**
     * Properties to be injected as attributes
     *
     * @var array
     */
    protected $injectedProperties = array();

    /**
     * Prints out the current tag
     *
     * @return string An input tag
     */
    public function render()
    {
        $this->setAttribute('type', 'text');
        $this->addClass('search-query');
        $this->setId();
        $this->removeAttribute('name');

        $name = $this->name;

        // Render main input
        $input = parent::render();

        $input .= $this->createDatalist('datalist_' . $name);

        return $input;
    }

    /**
     * Renders a datalist
     *
     * @param string $id     The datalist's id attribute
     * @param array  $values Its values
     *
     * @return string A <datalist> tag
     */
    private function createDatalist($id)
    {
        $datalist = '<ul id="' . $id . '" class="datalist ' . $id . '">';
        if (is_array($this->value)) {
            foreach ($this->value as $key => $value) {
                $datalist .= $this->formatSelected($key, $value);
            }
        }
        $datalist .= '</ul>';

        return $datalist;
    }

    protected function formatSelected($id, $name)
    {
        return '<li class="project-user' . $id . '">'
                . '<a href="javascript:void(0);" onclick="$(\'.project-user' . $id . '\').remove();" class="delete">' . trans('tinyissue.remove') . '</a>'
                . $name
                . '<input type="hidden" name="user[' . $id . ']" value="' . $name . '" />'
                . '</li>';
    }

    public function getValue()
    {
        if (!is_array($this->value)) {
            return [];
        }
        return array_keys($this->value);
    }
}
