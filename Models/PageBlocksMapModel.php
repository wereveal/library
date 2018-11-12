<?php
/**
 * Class PageBlocksMapModel.
 *
 * @package Ritc_Library
 */

namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Basic model class based on the page_blocks_map table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-02 14:36:41
 * @change_log
 * - v1.0.0 - Initial version                                       - 2018-06-02 wer
 */
class PageBlocksMapModel extends ModelAbstract
{
    /**
     * PageBlocksMapModel constructor.
     *
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'page_blocks_map');
        $this->setRequiredKeys(['pbm_page_id', 'pbm_block_id']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    /**
     * Deletes all records based on page id.
     *
     * @param int $page_id
     * @return bool
     * @throws ModelException
     */
    public function deleteAllByPageId($page_id = -1):bool
    {
        if ($page_id === -1) {
            $err_num = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException('Missing required values', $err_num);
        }
        $sql = 'DELETE FROM ' . $this->db_table . ' WHERE pbm_page_id = :page_id';
        try {
            $this->o_db->delete($sql, [':page_id' => $page_id], false);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Returns all records for a page.
     *
     * @param int $page_id
     * @return array
     * @throws ModelException
     */
    public function readByPageId($page_id = -1):array
    {
        if ($page_id === -1) {
            $err_num = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException('Missing required values', $err_num);
        }
        $a_search_for = [
            'pbm_page_id' => $page_id
        ];
        try {
            return $this->read($a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the values for a specific page block map record.
     *
     * @param int $pbm_id
     * @return array
     * @throws ModelException
     */
    public function readByPbmId($pbm_id = -1):array
    {
        if ($pbm_id === -1) {
            $err_num = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException('Missing required values', $err_num);
        }
        $a_values = ['pbm_id' => $pbm_id];
        try {
            $a_results = $this->read($a_values);
            if (!empty($a_results[0])) {
                return $a_results[0];
            }
            $err_code = ExceptionHelper::getCodeNumberModel('read no records');
            throw new ModelException('No Records Available', $err_code);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the values for the page/block groups which have no content assigned to it.
     *
     * @return array
     * @throws ModelException
     */
    public function readPbmWithoutContent():array
    {
        $prefix = $this->lib_prefix;
        $a_values = ['c_current' => 'true'];
        $sql = "SELECT pbm.*, p.*, b.*
            FROM {$prefix}page_blocks_map as pbm
            JOIN {$prefix}page as p 
              ON pbm.pbm_page_id = p.page_id
            JOIN {$prefix}blocks as b
              ON pbm.pbm_block_id = b.b_id
            WHERE pbm.pbm_id NOT IN (
              SELECT c_pbm_id 
              FROM {$prefix}content 
              WHERE c_current = :c_current
            )";
        try {
            return $this->o_db->search($sql, $a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Creates multiple records for a page id.
     *
     * @param int   $page_id  Required
     * @param array $a_blocks Required. Example, ['1', '2']
     * @return bool
     * @throws ModelException
     */
    public function createByPageId($page_id = -1, array $a_blocks = []):bool
    {
        if ($page_id === -1 || empty($a_blocks)) {
            $err_num = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException('Missing required values', $err_num);
        }
        $a_save_values = [];
        foreach ($a_blocks as $block_id) {
            $a_save_values[] = [
                'pbm_page_id' => $page_id,
                'pbm_block_id' => $block_id
            ];
        }
        try {
            $this->create($a_save_values, false);
        }
        catch (ModelException $e) {
            $this->o_db->rollbackTransaction();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }
}