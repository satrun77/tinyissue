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
use Tinyissue\Model\Project;
use Tinyissue\Services\Exporter;

/**
 * CsvHandler is an export class for exporting a project issues into a csv file.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class CsvHandler
{
    /**
     * CSV columns.
     *
     * @var array
     */
    protected $columns = [
        'tinyissue.id'            => 'id',
        'tinyissue.project'       => 'project',
        'tinyissue.title'         => 'title',
        'tinyissue.time_quote'    => 'time_quote',
        'tinyissue.label_created' => 'created_at',
        'tinyissue.updated'       => 'updated_at',
        'tinyissue.label_closed'  => 'closed_at',
        'tinyissue.status'        => 'status',
    ];

    /**
     * @param Exporter $exporter
     *
     * @return void
     */
    public function handle(Exporter $exporter)
    {
        // Translate export columns
        $this->columns = array_combine(array_map('trans', array_keys($this->columns)), $this->columns);

        // Export parameters
        $params = $exporter->getParams();

        /** @var Project $project */
        $project = $params['route']['project'];

        // Query issues select specific columns
        $query = $project->issues()->select(array_filter($this->columns, function ($column) {
            return $column !== 'project';
        }));

        // Filter issues
        $query
            ->assignedTo((int) array_get($params, 'assignto'))
            ->whereTags(array_get($params, 'tag_status'), array_get($params, 'tag_type'))
            ->searchContent(array_get($params, 'keyword'));

        // Fetch issues
        $issues = $query->get()->map(function (Project\Issue $issue) use ($project) {
            return array_map(function ($column) use ($issue, $project) {

                if ($column == 'project') {
                    return $project->name;
                }

                return (string) $issue->$column;
            }, $this->columns);
        });

        // Create CSV file
        $exporter->sheet('issues', function (LaravelExcelWorksheet $sheet) use ($issues) {
            $sheet->fromArray($issues);
        });
    }
}
