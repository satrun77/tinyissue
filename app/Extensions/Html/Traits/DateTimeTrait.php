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
 * DateTimeTrait is trait class for adding methods to generate the html code for date and time display
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait DateTimeTrait
{
    /**
     * Format a date
     *
     * @param int|string $date
     * @param string     $format
     *
     * @return string
     */
    public function date($date, $format = null)
    {
        $dateObject = new \DateTime($date);

        if (null === $format) {
            $format = app('tinyissue.settings')->getDateFormat();
        }

        return $dateObject->format($format);
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
        for ($j = 0; $difference >= $lengths[$j]; ++$j) {
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
        $hours = floor($seconds / 3600);
        $minutes = ($seconds / 60) % 60;
        $seconds = $seconds % 60;

        $output = '';
        $separatorChar = ', ';
        $separator = '';
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
}
