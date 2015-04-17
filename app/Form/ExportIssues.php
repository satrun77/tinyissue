<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tinyissue\Form;

use Tinyissue\Model;
use Tinyissue\Services\Exporter;

/**
 * ExportIssues is a class to defines fields & rules for project issues export form
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ExportIssues extends FilterIssue
{
    public function actions()
    {
        return [
            'export-issue' => [
                'name'  => 'export-issue',
                'label' => 'export',
                'type'  => 'info_submit',
            ]
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        // Remove sort fields
        unset($fields['sort']);

        // Remove extra group class
        $fields = array_map(function ($field) {
            unset($field['onGroupAddClass']);

            return $field;
        }, $fields);

        // Add extra fields
        $fields['format'] = [
            'type'        => 'select',
            'placeholder' => trans('tinyissue.export_format'),
            'options'     => [
                Exporter::TYPE_Xls => trans('tinyissue.xls'),
                Exporter::TYPE_Xlsx => trans('tinyissue.xlsx'),
                Exporter::TYPE_CSV => trans('tinyissue.csv'),
            ]
        ];

        return $fields;
    }

    public function openType()
    {
        return 'open';
    }
}
