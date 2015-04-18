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

use GrahamCampbell\Markdown\Facades\Markdown;

/**
 * HtmlBuilder is a class to extend Laravel HtmlBuilder to add extra view macro
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class HtmlBuilder extends \Illuminate\Html\HtmlBuilder
{
    /**
     * Render tab header from an array
     *
     * @param array       $tabs
     * @param string      $active
     * @param string|null $areaFor
     *
     * @return string
     */
    public function tab(array $tabs, $active, $areaFor = null)
    {
        $output = '<ul class="nav nav-tabs">';
        foreach ($tabs as $tab) {
            $output .= '<li role="presentation" ' . ($active == $tab['page'] ? 'class="active"' : '') . '>';

            $title = '';
            if (isset($tab['count'])) {
                $title .= $tab['count'] === 1 ? 1 : (int) $tab['count'];
                $title .= ' ';
            }
            $title .= trans('tinyissue.' . $tab['page']);
            $title .= $areaFor == 'project' ? trans('tinyissue.project') : '';

            $output .= $this->link($tab['url'], $title);
        }
        $output .= '</ul>';

        return $output;
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
        $output = '<div ' . $this->attributes($attrs) . '><div class="inside-pad">';

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
     * Format a date
     *
     * @param int|string $date
     * @param string     $format
     *
     * @return string
     */
    public function date($date, $format = 'F jS \a\t g:i A')
    {
        $dateObject = new \DateTime($date);

        return $dateObject->format($format);
    }

    /**
     * Limit the number of characters in a string, & remove <p> tag
     *
     * @param string $content
     * @param int    $size
     *
     * @return mixed
     */
    public function trim($content, $size = 60)
    {
        return str_replace(['<p>', '</p>'], '', str_limit($content, $size));
    }

    /**
     * Format Markdown to Html
     *
     * @param string $content
     *
     * @return string
     */
    public function format($content)
    {
        return Markdown::convertToHtml($this->filterIssueNo($content));
    }

    /**
     * Format issue number in string into a url to the issue
     *
     * @param string $content
     *
     * @return string
     */
    public function filterIssueNo($content)
    {
        $link = $this->link('/project/issue/$3', '$1 #$3', ['title' => '$1 #$3', 'class' => 'issue-link']);

        return preg_replace('/((?:' . trans('tinyissue.issue') . ')?)(\s*)#(\d+)/i', $link, $content);
    }

    /**
     * Displays the timestamp's age in human readable format
     *
     * @param int $timestamp
     *
     * @return string
     */
    public function age($timestamp)
    {
        if (!$timestamp instanceof \DateTime) {
            $timestamp = new \DateTime($timestamp);
        }

        $timestamp = $timestamp->getTimestamp();
        $difference = time() - $timestamp;
        $periods = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade'];
        $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];
        for ($j = 0; $difference >= $lengths[$j]; $j++) {
            $difference /= $lengths[$j];
        }
        $difference = round($difference);
        if ($difference != 1) {
            $periods[$j] .= 's';
        }

        return $difference . ' ' . $periods[$j] . ' ago';
    }

    /**
     * Convert seconds into time duration format
     *
     * @param int $seconds
     *
     * @return string
     */
    public function duration($seconds)
    {
        $hours   = floor($seconds / 3600);
        $minutes = ($seconds / 60) % 60;
        $seconds = $seconds % 60;

        $output        = '';
        $separatorChar = ', ';
        $separator     = '';
        if ($hours > 0) {
            $output .= $hours . ' ' . trans('tinyissue.short_hours');
            $separator = $separatorChar;
        }
        if ($minutes > 0) {
            $output .= $separator . $minutes . ' ' . trans('tinyissue.short_minutes');
            $separator = $separatorChar;
        }
        if ($seconds > 0) {
            $output .= $separator . $seconds . ' ' . trans('tinyissue.short_seconds');
        }

        return $output;
    }

    /**
     * Render loading Html
     *
     * @param string $message
     *
     * @return string
     */
    public function loader($message)
    {
        return '<div class="loader">'
                . $this->image(\URL::asset('images/icons/loader.gif'))
                . '<span>' . trans('tinyissue.' . $message) . '</span>'
                . '</div>';
    }

    /**
     * Format issue tag in string into Html
     *
     * @param string      $tag
     * @param string|null $group
     *
     * @return string
     */
    public function formatIssueTag($tag, $group = null)
    {
        if (null === $group) {
            list($group, $tag) = explode(':', $tag);
        }

        return '<span class="issue-tag"><span class="group">' . $group . '</span><span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span><span class="title">' . $tag . '</span></span>';
    }
}
