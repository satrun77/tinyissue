<?php

namespace Tinyissue\Extensions\Html;

use GrahamCampbell\Markdown\Facades\Markdown;

class HtmlBuilder extends \Illuminate\Html\HtmlBuilder
{
    public function toolbar($type, array $data = [])
    {
        $link = '';
        $title = '';
        $subTitle = '';
        extract($data);

        if ($type == 'issue') {
            $link = $this->link($project->to('issue/new'), trans('tinyissue.new_issue'), ['class' => 'newissue']);
            $title = $this->link($issue->to(), $issue->title);
            if (\Auth::user()->permission('issue-modify')) {
                $title = $this->link($issue->to('edit'), $issue->title, ['class' => 'edit-issue']);
            }
            $subTitle = trans('tinyissue.on_project') . ' ' . $this->link($project->to(), $project->name);
        } elseif ($type == 'add_issue') {
            $title = trans('tinyissue.create_a_new_issue');
            $subTitle = trans('tinyissue.create_a_new_issue_in') . ' ' . $this->link($project->to(), $project->name);
        } elseif ($type == 'edit_issue') {
            $title = trans('tinyissue.edit_issue');
            $subTitle = trans('tinyissue.edit_issue_in') . ' ' . $this->link($project->to(), $project->name);
        } elseif ($type == 'title') {
            $title = trans('tinyissue.' . $title);
            $subTitle = trans('tinyissue.' . $subTitle);
        } elseif ($type == 'project') {
            $title = $this->link($project->to(), $project->name);
            $subTitle = trans('tinyissue.project_overview');
            $link = $this->link($project->to('issue/new'), trans('tinyissue.new_issue'), ['class' => 'newissue']);
        } elseif ($type == 'add_user') {
            $title = trans('tinyissue.' . $title);
            $subTitle = trans('tinyissue.' . $subTitle);
            $link = $this->link('administration/users/add', trans('tinyissue.add_new_user'), ['class' => 'addnewuser']);
        } elseif ($type == 'edit_project') {
            $title = trans('tinyissue.update') . ' ' . $this->link($project->to(), $project->name);
            $subTitle = trans('tinyissue.update_project_description');
            $link = $this->link($project->to('issue/new'), trans('tinyissue.new_issue'), ['class' => 'newissue']);
        }

        $output = '<h3>';
        $output .= $link;
        $output .= $title ? '<span class="title">' . $title . '</span>' : '';
        $output .= $subTitle ? '<span class="subtitle">' . $subTitle . '</span>' : '';
        $output .= '</h3>';

        return $output;
    }

    public function tab($tabs, $active, $areaFor = null)
    {
        $output = '<ul class="tabs">';
        foreach ($tabs as $tab) {
            $output .= '<li ' . ($active == $tab['page'] ? 'class="active"' : '') . '>';
            $output .= '<a href="' . $tab['url'] . '">';
            if (isset($tab['count'])) {
                $output .= $tab['count'] === 1 ? 1: (int) $tab['count'];
                $output .= ' ';
            }
            $output .= trans('tinyissue.' . $tab['page']);
            $output .= $areaFor == 'project' ? trans('tinyissue.project') : '';
            $output .= '</a>';
        }
        $output .= '</ul>';

        return $output;
    }

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

    public function box($content, $style = 'blue-box', $title = null, $moreLink = null, $moreTitle = null)
    {
        return $this->startBox($style, $title) . $content . $this->endBox($moreLink, $moreTitle);
    }

    public function date($date, $format = 'F jS \a\t g:i A')
    {
        $dateObject = new \DateTime($date);
        return $dateObject->format($format);
    }

    public function trim($content, $size = 60)
    {
        return str_replace(array("<p>", "</p>"), "", str_limit($content, $size));
    }

    public function format($content)
    {
        return Markdown::convertToHtml($this->filterIssueNo($content));
    }

    public function filterIssueNo($content)
    {
        $link = $this->link('/project/issue/$3', '$1 #$3', ['title' => '$1 #$3', 'class' => 'issue-link']);
        return preg_replace('/((?:' . trans('tinyissue.issue') . ')?)(\s*)#(\d+)/i', $link, $content);
    }

    /**
     * Displays the timestamp's age in human readable format
     *
     * @param  int $timestamp
     * @return string
     */
    public function age($timestamp)
    {
        if (!$timestamp instanceof \DateTime) {
            $timestamp = new \DateTime($timestamp);
        }

        $timestamp = $timestamp->getTimestamp();
        $difference = time() - $timestamp;
        $periods = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
        $lengths = array('60', '60', '24', '7', '4.35', '12', '10');
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

    public function loader($message)
    {
        return '<div class="loader">'
                . $this->image(\URL::asset('images/icons/loader.gif'))
                . '<span>' . trans('tinyissue.' . $message) . '</span>'
                . '</div>';
    }

    public function formatIssueTag($tag, $group = null)
    {
        if (null === $group) {
            list($group, $tag) = explode(':', $tag);
        }

        return '<span class="issue-tag"><span class="group">' . $group . '</span><span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span><span class="title">' . $tag . '</span></span>';
    }
}
