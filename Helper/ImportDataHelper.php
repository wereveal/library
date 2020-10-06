<?php
namespace Ritc\Library\Helper;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate as PS_Coord;
use PhpOffice\PhpSpreadsheet\Exception as PS_Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as PSR_Exception;

class ImportDataHelper
{
    /** @var array */
    private $a_keys;
    /** @var string */
    private $delimiter;
    /** @var string */
    private $enclosure;
    /** @var string */
    private $import_path;

    /**
     * ImportDataHelper constructor.
     *
     * @param array $a_config requires ['import_path', 'delimiter', 'enclosure', 'a_keys' => 'array of keys']
     */
    public function __construct(array $a_config)
    {
        $this->setupHelper($a_config);
    }

    /**
     * Reads the file into an array.
     * Needs to be one of a select number of file types
     * as readable by a PhpOffice\Spreadsheet reader:
     * Xls, Xlsx, Csv, Html, Ods, Slk, Xml
     *
     * @param string $file_name
     * @param bool   $firstLineKeys
     * @return array
     */
    public function readFile(string $file_name, bool $firstLineKeys): array
    {
        $file_w_path = $this->import_path . '/' . $file_name;
        $a_file_name_parts = explode('.', $file_name);
        $ext = $a_file_name_parts[\count($a_file_name_parts) - 1];
        $reader_type = ucwords($ext);
        try {
            $o_reader = IOFactory::createReader($reader_type);
        }
        catch (PSR_Exception $e) {
            return [];
        }
        if ($ext === 'xslx' || $ext === 'xsl') {
            $o_reader->setReadDataOnly(false);
        }
        if ($ext === 'csv') {
            $o_reader->setDelimiter($this->delimiter);
            $o_reader->setEnclosure($this->enclosure);
        }
        try {
            $o_file = $o_reader->load($file_w_path);
        }
        catch (PSR_Exception $e) {
            return [];
        }
        try {
            $o_work = $o_file->getActiveSheet();
        }
        catch (PS_Exception $e) {
            return [];
        }
        // $the_string = '';
        $highest_row = $o_work->getHighestRow();
        $highest_col = $o_work->getHighestColumn();
        try {
            $highest_col_index = PS_Coord::columnIndexFromString($highest_col);
        }
        catch (PS_Exception $e) {
            return [];
        }
        $a_db_values = [];
        for ($row = 1; $row <= $highest_row; ++$row) {
            if ($firstLineKeys && $row === 1 && $highest_row > 1) {
                $row++;
            }
            $a_columns = [];
            for ($col = 1; $col <= $highest_col_index; ++$col) {
                $a_columns[$this->a_keys[$col]] = $o_work->getCellByColumnAndRow($col, $row)->getValue();
            }
            $a_db_values[$row] = $a_columns;
        }
        return $a_db_values;
    }

    /**
     * @param array $a_config
     */
    protected function setupHelper(array $a_config): void
    {
        $this->delimiter   = $a_config['delimiter'] ?? ',';
        $this->enclosure   = $a_config['enclosure'] ?? '"';
        $this->import_path = $a_config['import_path'] ?? PUBLIC_PATH . '/assets/files';
        $this->a_keys      = $a_config['a_keys'] ?? [];
    }

    /**
     * @param array $a_keys
     */
    public function setKeys(array $a_keys): void
    {
        $this->a_keys = $a_keys ?? [];
    }

    /**
     * Standard SETter for class property delimiter.
     *
     * @param string $delimiter
     */
    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter ?? '';
    }

    /**
     * Standard SETter for class property enclosure.
     *
     * @param string $enclosure
     */
    public function setEnclosure(string $enclosure): void
    {
        $this->enclosure = $enclosure ?? '';
    }

    /**
     * Standard SETter.
     * Verifies the path exists before setting.
     *
     * @param string $import_path
     */
    public function setImportPath(string $import_path): void
    {
        if ($import_path !== '') {
            if (file_exists($import_path)) {
                $this->import_path = $import_path;
            }
            elseif (file_exists(PUBLIC_PATH . $import_path)) {
                $this->import_path = PUBLIC_PATH . $import_path;
            }
            else {
                $this->import_path = '';
            }
        }
        else {
            $this->import_path = '';
        }
    }
}