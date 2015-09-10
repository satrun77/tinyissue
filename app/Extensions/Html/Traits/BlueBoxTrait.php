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

use Illuminate\Html\HtmlBuilder;

/**
 * BlueBoxTrait is trait class for adding methods to generate the html code of the blue box
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method HtmlBuilder attributes($relations)
 */
trait BlueBoxTrait
{
    /**
     * Short cut method to call startBox() & endBox()
     *
     * @param string      $content
     * @param string      $style
     * @param string|null $title
     * @param string|null $moreLink
     * @param string|null $moreTitle
     *
     * @return string
     */
    public function box($content, $style = 'blue-box', $title = null, $moreLink = null, $moreTitle = null)
    {
        return $this->startBox($style, $title) . $content . $this->endBox($moreLink, $moreTitle);
    }

    /**
     * Render the open tags for box (e.g. blue box)
     *
     * @param string      $style
     * @param string|null $title
     * @param array       $attrs
     *
     * @return string
     */
    public function startBox($style = 'blue-box', $title = null, array $attrs = [])
    {
        $attrs['class'] = isset($attrs['class']) ? $attrs['class'] . ' ' . $style : $style;
        $output         = '<div ' . $this->attributes($attrs) . '><div class="inside-pad">';

        if (!empty($title)) {
            if (is_array($title)) {
                $title = '<a href="' . $title[1] . '">' . $title[0] . '</a>';
            }
            $output .= '<h4>' . $title . '</h4>';
        }

        $output .= '<div class="content">';

        return $output;
    }

    /**
     * Render the closing tags for the box (e.g. blue box)
     *
     * @param string|null $moreLink
     * @param string|null $moreTitle
     *
     * @return string
     */
    public function endBox($moreLink = null, $moreTitle = null)
    {
        $output = '</div>';

        if (!empty($moreLink)) {
            $moreTitle = empty($moreTitle) ? $moreTitle : $moreTitle;
            $output .= '<a href="' . $moreLink . '" class="view">' . $moreTitle . '</a>';
        }

        $output .= '</div></div>';

        return $output;
    }
}
