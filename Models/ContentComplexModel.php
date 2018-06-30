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

    /** @var BlocksModel $o_blocks */
    private $o_blocks;
    /** @var ContentModel $o_content */
    private $o_content;
    /** @var PageBlocksMapModel $o_pbm */
    private $o_pbm;

    /**
     * ContentComplexModel constructor.
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
        $this->a_object_names = ['o_blocks', 'o_pbm', 'o_content'];
        $this->o_blocks = new BlocksModel($o_db);
        $this->o_content = new ContentModel($o_db);
        $this->o_pbm = new PageBlocksMapModel($o_db);
        $this->setupElog($o_di);
        $this->setupProperties($o_db);
    }

    /**
     * Reads all versions of content for a particular page and block that are current.
     * @param int    $page_id Required
     * @param string $block   Optional, defaults to '' returning all blocks for a page.
     * @param string $current Optional, defaults to true returning only current content.
     * @return array          Records from the database table.
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readAllByPage($page_id = -1, $block = '', $current = ''):array
    {
        $meth = __METHOD__ . '.';
        if ($page_id < 1) {
            $message = 'Missing the required page id.';
            $error_code = ExceptionHelper::getCodeNumberModel('missing value');
            throw new ModelException($message, $error_code);
        }
        $sql_block = '';
        $sql_current = '';
        $a_search_for = [':page_id' => $page_id];
        if (!empty($block)) {
            $a_search_for[':b_name'] = $block;
            $sql_block = ' AND b.b_name = :b_name';
        }
        if (!empty($current)) {
            $a_search_for[':c_current'] = $current;
            $sql_current = ' AND c.c_current = :c_current';
        }
        $sql = "
            SELECT
              p.page_id, p.page_title, p.page_description,
              b.b_name, b.b_type,
              c.c_content, c.c_short_content, c.c_type
            FROM lib_page as p
            JOIN lib_page_blocks_map as pbm
              ON p.page_id = pbm.pbm_page_id
            JOIN  lib_blocks as b
              ON b.b_id = pbm.pbm_block_id{$sql_block}
            JOIN lib_content as c
              ON c.c_pbm_id = pbm.pbm_id{$sql_current}
            WHERE p.page_id = :page_id
        ";
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
     * Returns current featured content.
     * @return mixed
     * @throws ModelException
     */
    public function readAllFeatured()
    {
        $sql = '
            SELECT
              p.page_id, p.page_title, p.page_description,
              b.b_name, b.b_type,
              c.c_content, c.c_short_content, c.c_type
            FROM lib_page as p
            JOIN lib_page_blocks_map as pbm
              ON p.page_id = pbm.pbm_page_id
            JOIN  lib_blocks as b
              ON b.b_id = pbm.pbm_block_id
            JOIN lib_content as c
              ON c.c_pbm_id = pbm.pbm_id
                AND c.c_current = :c_current
                AND c.c_location = :c_location
            WHERE p.page_id = :page_id
        ';
        $a_search_for = [
            ':c_current'  => 'true',
            ':c_location' => 'featured'
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
     * Reads the current version of content for a page.
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
     * @return mixed
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readCurrentShared()
    {
        $prefix = $this->lib_prefix;
        $sql = "
            SELECT c.*, b.b_name
            FROM {$prefix}content as c
            JOIN {$prefix}page_blocks_map as pbm
              ON c.c_pbm_id = pbm.pbm_id
            JOIN {$prefix}blocks as b
              ON b.b_id = pbm.pbm_block_id
                AND b.b_active = :b_active
                AND b.b_type = :b_type
            WHERE c.c_current = :current;
        ";
        $a_search_for = [
            ':b_active' => 'true',
            ':b_type'   => 'shared',
            ':current'  => 'true'
        ];
        try {
            $results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $results;
    }
}
