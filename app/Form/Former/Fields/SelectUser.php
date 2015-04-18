<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Form\Former\Fields;

use Former\Traits\Field;

/**
 * SelectUser is a Former field class for selecting project users
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class SelectUser extends Field
{
    /**
     * Properties to be injected as attributes
     *
     * @var array
     */
    protected $injectedProperties = [];

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

        // Render list of selected users
        $input .= $this->createDataList('datalist_' . $name);

        return $input;
    }

    /**
     * Returns Html of selected users
     *
     * @param string $id
     *
     * @return string
     */
    private function createDataList($id)
    {
        $dataList = '<ul id="' . $id . '" class="datalist ' . $id . '">';
        if (is_array($this->value)) {
            foreach ($this->value as $key => $value) {
                $dataList .= $this->formatSelected($key, $value);
            }
        }
        $dataList .= '</ul>';

        return $dataList;
    }

    /**
     * Returns Html of a selected user row
     *
     * @param int    $id
     * @param string $name
     *
     * @return string
     */
    protected function formatSelected($id, $name)
    {
        return '<li class="project-user' . $id . '">'
        . '<a href="" class="delete">' . trans('tinyissue.remove') . '</a>'
        . $name
        . '<input type="hidden" name="user[' . $id . ']" value="' . $name . '" />'
        . '</li>';
    }

    /**
     * Returns an array of selected users
     *
     * @return array
     */
    public function getValue()
    {
        if (!is_array($this->value)) {
            return [];
        }

        return array_keys($this->value);
    }
}
