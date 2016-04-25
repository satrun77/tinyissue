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

use Former\Form\Fields\Checkbox;

/**
 * CheckboxButton is a Former field class for converting checkboxes to buttons with custom colors for each.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class CheckboxButton extends Checkbox
{
    /**
     * Render radio buttons.
     *
     * @return string
     */
    public function render()
    {
        return '<div class="btn-toolbar checkbox-btn" data-toggle="buttons">' . parent::render() . '</div>';
    }

    /**
     * Render checkable radio button.
     *
     * @param array|string $item
     * @param int          $fallbackValue
     *
     * @return string
     */
    protected function createCheckable($item, $fallbackValue = 1)
    {
        // Make sure the parent class will create inline radios
        $this->inline = true;
        $this->grouped();
//        $this=
        // Extract the color for this button/radio & unset it to prevent creating attribute
        $color = $item['attributes']['color'];
        unset($item['attributes']['color']);

        // Color for selected button/radio
        $selectedColor = ';color:' . $color . ';';

        // Data attribute for the button color
        $item['attributes']['data-color'] = $color;

        // Generate the radio button
        $item = parent::createCheckable($item, $fallbackValue);

        // If property checked found, then make the button selected with styles
        if (strpos($item, 'checked') !== false) {
            $selectedColor = ';background:' . $color . ';color:white;';
        }

        // Add bootstrap classes for styling the buttons
        $item = str_replace('inline', 'btn btn-default', $item);

        // Add styles to the wrapper label tag
        $style = 'border-color: ' . $color . $selectedColor;
        $item  = str_replace('<label', '<label style="' . $style . '"', $item);

        return $item;
    }
}
