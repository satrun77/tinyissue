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

use Former\Form\Fields;

/**
 * FileUpload is a Former field class to generate a file field for Jquery File Upload plugin.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class FileUpload extends Fields\File
{
    /**
     * Render form field.
     *
     * @return string
     */
    public function render()
    {
        $this->setType('file');

        $output = '';
        $output .= '<span class="btn btn-info fileinput-button">';
        $output .= '<i class="glyphicon glyphicon-plus"></i><span>' . trans('tinyissue.add_files') . '</span>';
        $output .= parent::render();
        $output .= '</span>';
        $output .= '<ul role="presentation" id="' . $this->name . '-queue" class="' . $this->name . '-queue"></ul>';
        $output .= '<ul id="' . $this->name . '-template" class="hidden">' . $this->queueTemplate() . '</ul>';

        return $output;
    }

    /**
     * Render the upload queue row.
     *
     * @return string
     */
    protected function queueTemplate()
    {
        $output = '<li class="queue-item">';
        $output .= '<button type="button" class="close" aria-label="' . trans('tinyissue . cancel') . '" data-message="' . trans('tinyissue . delete_upload_file') . '">';
        $output .= '<span aria-hidden="true">&times;</span>';
        $output .= '</button>';
        $output .= '<div class="name"><span></span><strong class="status text-danger"></strong></div>';
        $output .= '<div class="progress progress-striped" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">';
        $output .= '<div class="progress-bar progress-bar-success" style="width:0;"></div>';
        $output .= '</div>';
        $output .= '</li>';

        return $output;
    }
}
