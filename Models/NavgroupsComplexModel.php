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
 * @todo Ritc/Library/Models/NavgroupsComplexModel.php - Everything
 */
namespace Ritc\Library\Models;

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
     */
    public function deleteWithMap($ng_id = -1)
    {
        if ($ng_id == -1) {
            $this->error_message = 'Missing Navgroup id.';
            return false;
        }
        if ($this->o_db->startTransaction()) {
            $o_map = new NavNgMapModel($this->o_db);
            $o_ng  = new NavgroupsModel($this->o_db);
            $results = $o_map->delete($ng_id);
            if (!$results) {
                $this->error_message = $o_map->getErrorMessage();
                $this->o_db->rollbackTransaction();
                return false;
            }
            else {
                $results = $o_ng->delete($ng_id);
                if ($results) {
                    return $this->o_db->commitTransaction();
                }
                else {
                    $this->error_message = $this->o_db->getSqlErrorMessage();
                    $this->o_db->rollbackTransaction();
                    return false;
                }
            }
        }
        else {
            $this->error_message = "Could not start transaction.";
            return false;
        }
    }
}