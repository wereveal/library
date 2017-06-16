<?php
/**
 * @brief     Multi-table model manipulations associated with the navgoups.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/NavgroupsComplexModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-06-11 08:20:26
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-11 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\DbException;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NavgroupsComplexModel.
 * @class   NavgroupsComplexModel
 * @package Ritc\Library\Models
 */
class NavgroupsComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /**
     * NavgroupsComplexModel constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        /** @var \Ritc\Library\Services\DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->o_db = $o_db;
    }

    /**
     * Deletes a record based on the id provided.
     * Also delete the relation record(s) in the map table.
     * @param int $ng_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function deleteWithMap($ng_id = -1)
    {
        if ($ng_id == -1) {
            $this->error_message = 'Missing Navgroup id.';
            throw new DbException($this->error_message, 420);
        }
        try {
            $this->o_db->startTransaction();
        }
        catch (DbException $e) {
            $this->error_message = "Could not start transaction.";
            throw new DbException($this->error_message, 30);
        }
        $o_map = new NavNgMapModel($this->o_db);
        $o_ng  = new NavgroupsModel($this->o_db);
        try {
            $o_map->delete($ng_id);
            try {
                $o_ng->delete($ng_id);
                try {
                    $this->o_db->commitTransaction();
                }
                catch (DbException $e) {
                    $this->error_message = 'Unable to commit the transaction.';
                    throw new DbException($this->error_message, 40, $e);
                }
            }
            catch (DbException $e) {
                $this->error_message = 'Unable to delete the map record: ' . $o_ng->getErrorMessage();
                try {
                    $this->o_db->rollbackTransaction();
                    throw new DbException($this->error_message, 400, $e);
                }
                catch (DbException $e) {
                    $this->error_message .= 'Unable to rollback the transaction.';
                    throw new DbException($this->error_message, 45, $e);
                }
            }
        }
        catch (DbException $e) {
            $this->error_message = 'Unable to delete the map record: ' . $o_map->getErrorMessage();
            try {
                $this->o_db->rollbackTransaction();
                throw new DbException($this->error_message, 400, $e);
            }
            catch (DbException $e) {
                $this->error_message .= 'Unable to rollback the transaction.';
                throw new DbException($this->error_message, 45, $e);
            }
        }
        return true;
    }
}