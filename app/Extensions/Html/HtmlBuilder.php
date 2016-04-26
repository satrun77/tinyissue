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
 * HtmlBuilder is a class to extend Laravel HtmlBuilder to add extra view macro.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class HtmlBuilder extends \Illuminate\Html\HtmlBuilder
{
    use Traits\BlueBoxTrait,
        Traits\TabTrait,
        Traits\DateTimeTrait;

    /**
     * Limit the number of characters in a string, & remove <p> tag.
     *
     * @param string $content
     * @param int    $size
     *
     * @return string
     */
    public function trim($content, $size = 60)
    {
        return str_replace(['<p>', '</p>'], '', str_limit($content, $size));
    }

    /**
     * Format Markdown to Html.
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
     * Format issue number in string into a url to the issue.
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
     * Render loading Html.
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
     * Format issue tag in string into Html.
     *
     * @param string      $tag
     * @param string|null $group
     *
     * @return string
     */
    public function formatIssueTag($tag, $group = null)
    {
        if (null === $group && strpos($tag, ':') !== false) {
            list(, $tag) = explode(':', $tag);
        }

        return '<span class="issue-tag"><span class="title">' . $tag . '</span></span>';
    }
}
