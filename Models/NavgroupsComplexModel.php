<?php
/**
 * Class NavgroupsComplexModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;

/**
 * Class NavgroupsComplexModel - Multi-table model manipulations associated with the navgoups.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-11-30 13:53:16
 * @change_log
 * - v2.0.0         - updated for php8                          - 2021-11-30 wer
 * - v1.0.0         - taking it into production                 - 2017-12-12 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-06-11 wer
 */
class NavgroupsComplexModel
{
    use DbUtilityTraits;

    /**
     * NavgroupsComplexModel constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        /** @var DbModel $o_db */
    }

    /**
     * Deletes a record based on the id provided.
     * Also delete the relation record(s) in the map table.
     *
     * @param int $ng_id
     * @return bool
     * @throws ModelException
     */
    public function deleteWithMap(int $ng_id = -1):bool
    {
        if ($ng_id === -1) {
            $this->error_message = 'Missing Navgroup id.';
            throw new ModelException($this->error_message, 420);
        }
        try {
            $this->o_db->startTransaction();
        }
        catch (ModelException) {
            $this->error_message = 'Could not start transaction.';
            throw new ModelException($this->error_message, 12);
        }
        $o_map = new NavNgMapModel($this->o_db);
        $o_ng  = new NavgroupsModel($this->o_db);
        try {
            $o_map->deleteWith($ng_id);
            try {
                $o_ng->delete($ng_id);
                try {
                    $this->o_db->commitTransaction();
                }
                catch (ModelException $e) {
                    $this->error_message = 'Unable to commit the transaction.';
                    throw new ModelException($this->error_message, 13, $e);
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to delete the map record: ' . $o_ng->getErrorMessage();
                try {
                    $this->o_db->rollbackTransaction();
                    throw new ModelException($this->error_message, 410, $e);
                }
                catch (ModelException $e) {
                    $this->error_message .= 'Unable to rollback the transaction.';
                    throw new ModelException($this->error_message, 14, $e);
                }
            }
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to delete the map record: ' . $o_map->getErrorMessage();
            try {
                $this->o_db->rollbackTransaction();
                throw new ModelException($this->error_message, 410, $e);
            }
            catch (ModelException $e) {
                $this->error_message .= 'Unable to rollback the transaction.';
                throw new ModelException($this->error_message, 14, $e);
            }
        }
        return true;
    }
}
