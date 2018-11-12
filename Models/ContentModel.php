<?php
/**
 * Class ContentModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Does all the database operations and business logic for content.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-15 10:40:35
 * @change_log
 * - v1.0.0 - Initial version                                   - 2018-06-15 wer
 */
class ContentModel extends ModelAbstract
{
    /**
     * ContentModel constructor.
     *
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'content');
        $this->setRequiredKeys(['c_pbm_id']);
    }

    /**
     * Reads the record for a specific page block mapping.
     *
     * @param int $pbm_id
     * @return array
     * @throws ModelException
     */
    public function readByPbm($pbm_id = -1):array
    {
        $a_find = [
            'c_pbm_id' => $pbm_id
        ];
        $a_with = [
            'order_by' => 'c_current DESC'
        ];
        try {
            return $this->read($a_find, $a_with);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Reads the current content for the record or all records.
     * 
     * @param int $record_id
     * @return array
     * @throws ModelException
     */
    public function readCurrent($record_id = -1):array
    {
        $a_find = [
            'c_current' => 'true'
        ];
        if ($record_id > 0) {
            $a_find['c_id'] = $record_id;
        }
        try {
            return $this->read($a_find);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Sets all records to not current for a particular pbm_id.
     *
     * @param int $pbm_id
     * @return bool
     * @throws ModelException
     */
    public function setCurrentFalse($pbm_id = -1):bool
    {
        if ($pbm_id <= 0) {
            $err_code = ExceptionHelper::getCodeNumberModel('update missing value');
            throw new ModelException('Missing Required Value.', $err_code);
        }
        $sql = 'UPDATE ' . $this->db_table . " SET c_current = 'false' WHERE c_pbm_id = :c_pbm_id";
        $a_values = ['c_pbm_id' => $pbm_id];
        try {
            return $this->o_db->update($sql, $a_values, false);
        }
        catch (ModelException $e) {
            $msg = ' - Unable to change current status.';
            throw new ModelException($e->getMessage() . $msg, $e->getCode(), $e);
        }
    }
}
