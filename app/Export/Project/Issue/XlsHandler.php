<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Export\Project\Issue;

use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\CellWriter;
use Tinyissue\Model\Project;
use Tinyissue\Services\Exporter;

/**
 * XlsHandler is an export class for exporting a project issues into a Xls or Xlsx file
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class XlsHandler
{
    /**
     * Instance of the project
     *
     * @var Project
     */
    protected $project;

    /**
     * Collection of project issues
     *
     * @var Project\Issue[]
     */
    protected $issues;

    /**
     * Columns to export from the issues table
     *
     * @var array
     */
    protected $columns = [
        'id'         => '',
        'title'      => '',
        'time_quote' => '',
        'created_at' => '',
        'updated_at' => '',
        'closed_at'  => '',
        'status'     => '',
    ];

    /**
     * Columns in the output file
     *
     * @var array
     */
    protected $header = [
        'tinyissue.id',
        'tinyissue.title',
        'tinyissue.time_quote',
        'tinyissue.label_created',
        'tinyissue.updated',
        'tinyissue.label_closed',
        'tinyissue.status',
    ];

    public function handle(Exporter $exporter)
    {
        /* @var Project $project */
        $this->project = $exporter->getParams('route.project');
        $query = $this->project->issues()->select(array_keys($this->columns));
        // Filter issues
        $this->project->filterIssues($query, $exporter->getParams());
        // Fetch issues
        $this->issues = $query->get();

        // Create CSV file
        $exporter->sheet($this->project->name, function (LaravelExcelWorksheet $sheet) {

            // Global sheet styles
            $this->globalStyle($sheet);

            // Title
            $sheet->mergeCells('A1:G1');
            $sheet->setHeight(1, 50);
            $sheet->cell('A1', function (CellWriter $cell) {
                $this->sheetTitle($cell);
            });

            // Header
            $sheet->row(2, array_map('trans', $this->header));

            // Rows
            $index = 3;
            foreach ($this->issues as $issue) {
                $this->sheetRow($sheet, $index, $issue);
                $index++;
            }
        });
    }

    /**
     * Setup sheet global styles
     *
     * @param LaravelExcelWorksheet $sheet
     *
     * @return void
     */
    protected function globalStyle(LaravelExcelWorksheet $sheet)
    {
        // Font size & family
        $sheet->setFontSize(15);
        $sheet->setFontFamily('Calibri');
    }

    /**
     * Sheet title in the first row
     *
     * @param CellWriter $cell
     *
     * @return void
     */
    public function sheetTitle(CellWriter $cell)
    {
        $cell->setFontWeight('bold');
        $cell->setBorder('none', 'none', 'none', 'thin');
        $title = trans('tinyissue.export_xls_title', ['name' => $this->project->name, 'count' => count($this->issues)]);
        $cell->setValue($title);
    }

    /**
     * Sheet data row
     *
     * @param LaravelExcelWorksheet $sheet
     * @param int                   $index
     * @param Project\Issue         $issue
     *
     * @return void
     */
    protected function sheetRow(LaravelExcelWorksheet $sheet, $index, Project\Issue $issue)
    {
        // Setup row data
        array_walk($this->columns, function (&$column, $key, Project\Issue $issue) {
            $column = (string) $issue->$key;
            if ($key === 'status') {
                $column = (int) $issue->status === Project\Issue::STATUS_OPEN ? 'open' : 'closed';
                $column = trans('tinyissue.' . $column);
            }
        }, $issue);

        // Write row
        $sheet->row($index, $this->columns);

        // Format last cell
        $sheet->cell('G' . $index, function (CellWriter $cell) use ($issue) {
            $color = (int) $issue->status === Project\Issue::STATUS_CLOSED ? '#FF0000' : '#00FF00';
            $cell->setBackground($color);
        });
    }
}
