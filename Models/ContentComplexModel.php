<?php
/**
 * Class ContentComplexModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Multi-table database operations and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-02 14:12:27
 * @change_log
 * - v1.0.0 - Initial version                                           - 2018-06-02 wer
 */
class ContentComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /** @var string $select_sql */
    private $select_sql;
    /** @var BlocksModel $o_blocks */
    private $o_blocks;
    /** @var ContentModel $o_content */
    private $o_content;
    /** @var PageModel $o_page */
    private $o_page;

    /**
     * ContentComplexModel constructor.
     *
     * @param \Ritc\Library\Services\Di $o_di
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function __construct(Di $o_di)
    {
        $o_db = $o_di->get('db');
        if (! $o_db instanceof DbModel) {
            $message = 'Unable to get instanceof DbModel';
            $error_code = ExceptionHelper::getCodeNumber('instance');
            throw new ModelException($message, $error_code);
        }
        $this->a_object_names = ['o_blocks', 'o_content', 'o_page'];
        $this->o_blocks       = new BlocksModel($o_db);
        $this->o_content      = new ContentModel($o_db);
        $this->o_page         = new PageModel($o_db);
        $this->setupElog($o_di);
        $this->setupProperties($o_db);
        $this->buildBasicSelectSql();
    }

    /**
     * Deletes all content records for a specified page.
     *
     * @param int    $page_id  Required
     * @param string $block    Optional, defaults to deleting all content assigned to all blocks for page.
     * @param bool   $old_only Optional, defaults to false and deletes all records.
     *                         If true, deletes only non-current records.
     * @return bool
     * @throws ModelException
     */
    public function deleteAllByPage($page_id = -1, $block = '', $old_only = false):bool
    {
        if ($page_id === -1) {
            $message = 'Missing Required Values';
            $err_code = ExceptionHelper::getCodeNumberModel('missing_values');
            throw new ModelException($message, $err_code);
        }
        $block_sql = '';
        $current_sql = '';
        if ($block !== '') {
            $block_sql =  ' AND pbm_block_id = :block_id';
        }
        if ($old_only) {
            $current_sql = ' AND c_current = :c_current';
        }
        $lib_prefix = $this->lib_prefix;
        /** @noinspection SqlResolve */
        $sql = "SELECT c_id
            FROM {$lib_prefix}content 
            WHERE c_pbm_id IN (
              SELECT pbm_id 
              FROM {$lib_prefix}page_blocks_map 
              WHERE pbm_page_id = :page_id{$block_sql})
            {$current_sql}";
        $a_values = [
            ':page_id' => $page_id
        ];
        try {
            $a_content_ids = $this->o_db->search($sql, $a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        $delete_sql = "
            DELETE FROM {$lib_prefix}content
            WHERE c_id = :c_id
        ";
        try {
            $this->o_db->delete($delete_sql, $a_content_ids, false);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Reads all versions of content for a particular page and block that are current.
     *
     * @param int    $page_id Required
     * @param string $block   Optional, defaults to '' returning all blocks for a page.
     * @param string $current Optional, defaults to true returning only current content.
     * @return array          Records from the database table.
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readAllByPage($page_id = -1, $block = '', $current = 'true'):array
    {
        $meth = __METHOD__ . '.';
        if ($page_id < 1) {
            $message = 'Missing the required page id.';
            $error_code = ExceptionHelper::getCodeNumberModel('missing value');
            throw new ModelException($message, $error_code);
        }
        $sql_block = '';
        $where = '';
        $a_search_for = [':page_id' => $page_id];
        $page_current = ' AND p.page_id = :page_id';
        if (!empty($block)) {
            $a_search_for[':b_name'] = $block;
            $sql_block = ' 
                AND b.b_name = :b_name';
        }
        if ($current === 'true') {
            $a_search_for[':c_current'] = $current;
            $where = ' 
                WHERE c.c_current = :c_current';
        }
        $sql = str_replace(
            ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'],
            ['', $sql_block, $page_current],
            $this->select_sql
        );
        $sql .= $where;
          $this->logIt('SQL: ' . $sql, LOG_OFF, $meth . __LINE__);
          $log_message = 'a search for  ' . var_export($a_search_for, true);
          $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        try {
            $a_results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        $log_message = 'results ' . var_export($a_results, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        return $a_results;
    }

    /**
     * Returns all records that current.
     *
     * @return array
     * @throws ModelException
     */
    public function readAllCurrent():array
    {
        $meth  = __METHOD__ . '.';
        $where = '
            WHERE c_current = :c_current
        ';
        $sql   = str_replace(
            ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'],
            '',
            $this->select_sql
        );
        $sql   .= $where;
        $this->logIt('SQL: ' . $sql, LOG_ON, $meth . __LINE__);
        $a_search_for = [':c_current' => 'true'];
        try {
            $a_results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results;
    }

    /**
     * Returns current featured content.
     *
     * @return array
     * @throws ModelException
     */
    public function readAllFeatured():array
    {
        $meth = __METHOD__ . '.';
        $where = '
            WHERE c.c_current = :c_current 
            AND c.c_featured = :c_featured';
        $page_extra = ' 
                AND p.page_id = :page_id';
        $sql = str_replace(
            ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'],
            ['', '', $page_extra],
            $this->select_sql
        ) . $where;
        $this->logIt('SQL: ' . $sql, LOG_ON, $meth . __LINE__);
        $a_search_for = [
            ':c_current'  => 'true',
            ':c_featured' => 'true',
            ':page_id'    => ''
        ];

        try {
            $a_results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results;
    }

    /**
     * Reads the content for a page by the content id.
     *
     * @param int $id
     * @return array
     * @throws ModelException
     */
    public function readByContentId($id = -1):array
    {
        $pin = $this->o_content->getPrimaryIndexName();
        $sql = str_replace(
            ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'],
            '',
            $this->select_sql
        ) . ' WHERE ' . $pin . ' = :' . $pin;
        try {
            $a_results = $this->o_db->search($sql, [':' . $pin => $id]);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results[0];
    }

    /**
     * Reads the current version of content for a page.
     *
     * @param int    $page_id Required
     * @param string $block   Optional, returns all blocks if empty.
     * @return array Record(s).
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readCurrent($page_id = -1, $block = ''):array
    {
        if ($page_id < 1) {
            $message    = 'Missing the required page id.';
            $error_code = ExceptionHelper::getCodeNumberModel('missing value');
            throw new ModelException($message, $error_code);
        }
        return $this->readAllByPage($page_id, $block, 'true');
    }

    /**
     * Returns the values of shared content.
     *
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readCurrentShared():array
    {
        $meth = __METHOD__ . '.';
        $blocks_extra = ' AND b.b_active = :b_active AND b.b_type = :b_type';
        $where = '
            WHERE c.c_current = :current';
        $sql = str_replace($this->select_sql, $blocks_extra, $this->select_sql) . $where;
        $a_search_for = [
            ':b_active' => 'true',
            ':b_type'   => 'shared',
            ':current'  => 'true'
        ];
        $this->logIt('SQL: ' . $sql, LOG_ON, $meth . __LINE__);
        $log_message = 'Search For:  ' . var_export($a_search_for, true);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);
        
        try {
            $results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $results;
    }

    /**
     * Saves a new content record.
     *
     * @param array $a_values
     * @throws ModelException
     */
    public function saveNew(array $a_values = [])
    {
        if (empty($a_values)) {
            $err_code = ExceptionHelper::getCodeNumberModel('create missing values');
            throw new ModelException('No values provided to save', $err_code);
        }
    }
    ### Utility Methods

    /**
     * Creates the common sql used throughout the class.
     *
     * Sets the class property $select_sql.
     */
    private function buildBasicSelectSql():void
    {
        $prefix = $this->lib_prefix;
        $a_content_fields = $this->o_content->getDbFields();
        $a_page_fields    = $this->o_page->getDbFields();
        $a_blocks_fields  = $this->o_blocks->getDbFields();
        $a_select_fields = [
            'c' => $a_content_fields,
            'p' => $a_page_fields,
            'b' => $a_blocks_fields
        ];
        $select_fields = $this->mergeAndBuildSqlSelect($a_select_fields, true);
        $this->select_sql = /** @lang text */
            "
            SELECT {$select_fields}
            FROM {$prefix}content as c
            JOIN {$prefix}page_blocks_map as pbm
              ON c.c_pbm_id = pbm.pbm_id{{pbm_extra}}
            JOIN {$prefix}blocks as b
              ON b.b_id = pbm.pbm_block_id{{blocks_extra}}
            JOIN {$prefix}page as p
              ON p.page_id = pbm.pbm_page_id{{page_extra}}
        ";
    }
}
