<?php
/**
 * Class GroupsModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Does multi-table database actions to use app/business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2021-11-30 13:45:56
 * @change_log
 * - v1.0.0 - Initial version, split off from GroupsModel
 */
class GroupsComplexModel
{
    /** @var DbModel  */
    protected DbModel $o_db;

    /**
     * GroupsModel constructor.
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->o_db = $o_db;
    }

    /**
     * Deletes related records as well as main group record.
     *
     * @param int $group_id
     * @return bool
     * @throws ModelException
     */
    public function deleteWithRelated(int $group_id = -1):bool
    {
        if ($group_id === -1 || empty($group_id)) {
            $error_code = ExceptionHelper::getCodeNumberModel('delete missing values');
            throw new ModelException('Missing required value(s)', $error_code);
        }
        $o_group  = new GroupsModel($this->o_db);
        $o_ugm    = new PeopleGroupMapModel($this->o_db);
        $o_people = new PeopleModel($this->o_db);
        try {
            $results = $o_ugm->read(['group_id' => $group_id]);
        }
        catch (ModelException $e) {
            throw new ModelException('Could not read the map records.', $e->getCode(), $e);
        }
        $transaction = false;
        if (!empty($results)) {
            try {
                $this->o_db->startTransaction();
                $transaction = true;
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to start the transaction.', 12, $e);
            }
            foreach ($results as $a_record) {
                try {
                    $o_people->delete($a_record['people_id']);
                }
                catch (ModelException $e) {
                    $this->o_db->rollbackTransaction();
                    throw new ModelException('Could not delete the people records.', $e->getCode(), $e);
                }
                try {
                    $o_ugm->delete($a_record['pgm_id']);
                }
                catch (ModelException $e) {
                    $this->o_db->rollbackTransaction();
                    throw new ModelException('Could not delete the people group map records.', $e->getCode(), $e);
                }
            }
        }
        try {
            $o_group->delete($group_id);
            if ($transaction) {
                try {
                    $this->o_db->commitTransaction();
                }
                catch (ModelException) {
                    $this->o_db->rollbackTransaction();
                    $error_message = $this->o_db->getSqlErrorMessage();
                    throw new ModelException($error_message, 410);
                }
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
        return true;
    }
}
