<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Services;

use Maatwebsite\Excel\Exceptions\LaravelExcelException;
use Maatwebsite\Excel\Files\NewExcelFile;

/**
 * Exporter is class for initialising the exporter, process data, and export the generated file
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method $this sheet($sheetID, $callback = null)
 * @method $this store($ext = 'xls', $path = false, $returnInfo = false)
 * @method $this setFileName($fileName)
 */
class Exporter extends NewExcelFile
{
    /** Current supported files type */
    const TYPE_CSV  = 'csv';
    const TYPE_XLS  = 'xls';
    const TYPE_XLSX = 'xlsx';

    /**
     * Parameters
     *
     * @var array
     */
    protected $params = [];

    /**
     * Export file format
     *
     * @var string
     */
    protected $format = self::TYPE_CSV;

    /**
     * Type of data to export
     *
     * @var string
     */
    protected $type = '';

    /**
     * Returns the parameters
     *
     * @param string $key
     *
     * @return string|\Illuminate\Database\Eloquent\Model|null
     */
    public function getParams($key = null)
    {
        if (null === $key) {
            return $this->params;
        }

        return array_get($this->params, $key);
    }

    /**
     * Start importing
     *
     * @param string $className
     * @param string $format
     * @param array  $params
     *
     * @return array['full', 'path', 'file', 'title', 'ext']
     */
    public function exportFile($className, $format = self::TYPE_CSV, array $params = [])
    {
        $params['route'] = $this->app->request->route()->parameters();
        $this->format = $format;
        $this->params = $params;
        $this->type = $className;

        // Update file name
        $this->setFileName($this->getFilename());

        // Start exporting
        $this->handle($className);

        // Store file and return info
        return $this->store($format, false, true);
    }

    /**
     * Returns export file name
     *
     * @return string
     */
    public function getFilename()
    {
        return 'export_' . str_replace('\\', '_', strtolower($this->type)) . '_' . time();
    }

    /**
     * Construct the export class full name
     *
     * @param string $type
     *
     * @return string
     *
     * @throws LaravelExcelException
     */
    protected function getHandlerClassName($type)
    {
        $handler = '\\Tinyissue\Export\\' . $type . '\\' . ucfirst(substr($this->format, 0, 3)) . 'Handler';

        // Check if the handler exists
        if (!class_exists($handler)) {
            throw new LaravelExcelException("$type handler [$handler] does not exist.");
        }

        return $handler;
    }
}
