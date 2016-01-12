<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Extensions\Html\Traits;

/**
 * TabTrait is trait class for adding methods to generate the html code of the bootstrap tab
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method string link($url, $title = null, $attributes = array(), $secure = null)
 */
trait TabTrait
{
    /**
     * Render tab header from an array
     *
     * @param array  $tabs
     * @param string $active
     *
     * @return string
     */
    public function tab(array $tabs, $active)
    {
        $defaultTab = [
            'title' => '',
            'prefix' => '',
            'active' => false,
            'url' => '',
        ];

        $output = '<ul class="nav nav-tabs">';
        foreach ($tabs as $tab) {
            $tab = array_replace($defaultTab, $tab);

            $output .= '<li role="presentation" ' . $this->tabElementActive($tab, $active) . '>';
            $output .= $this->tabElementTitle($tab);
            $output .= '</li>';
        }
        $output .= '</ul>';

        return $output;
    }

    /**
     * Whether or not the tab element is active
     *
     * @param array  $tab
     * @param string $active
     *
     * @return string
     */
    protected function tabElementActive($tab, $active)
    {
        return $active == $tab['page'] ? 'class="active"' : '';
    }

    /**
     * Generate the content of a tab element
     *
     * @param array $tab
     *
     * @return string
     */
    protected function tabElementTitle($tab)
    {
        $title = trim($tab['prefix'] . ' ' . trans('tinyissue.' . $tab['page']));

        return $this->link($tab['url'], $title);
    }
}
