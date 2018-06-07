<?php
/**
 * Class GroupsModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the database CRUD stuff for the page table plus other app/business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.1
 * @date    2017-12-12 11:42:21
 * @change_log
 * - v2.0.1   - ModelException changes reflected here                           - 2017-12-12 wer
 * - v2.0.0   - Refactored to use ModelException and DbUtilityTraits            - 2017-06-10 wer
 * - v1.1.2   - DbUtilityTraits change reflected here                           - 2017-05-09 wer
 * - v1.0.0   - First working version                                           - 11/27/2015 wer
 * - v1.0.0β5 - refactoring to provide postgresql compatibility                 - 11/22/2015 wer
 * - v1.0.0β4 - added group_immutable field in db and changed code to match     - 10/08/2015 wer
 * - v1.0.0ß3 - removed abstract class Base, used LogitTraits                   - 09/01/2015 wer
 * - v1.0.0β1 - extends the Base class, injects the DbModel, clean up           - 09/23/2014 wer
 * - v1.0.0β0 - First live version                                              - 09/15/2014 wer
 * - v0.1.0β  - Initial version                                                 - 01/18/2014 wer
 */
class GroupsModel extends ModelAbstract
{
    use LogitTraits, DbUtilityTraits;

    /**
     * GroupsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'groups');
    }

    /**
     * in ModelAbstract
     * create
     * read
     * update
     * delete
     */

    /**
     * Deletes related records as well as main group record.
     * @param int|array $group_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function deleteWithRelated($group_id = -1)
    {
        if ($group_id == -1 || empty($group_id)) {
            $error_code = ExceptionHelper::getCodeNumberModel('delete missing values');
            throw new ModelException('Missing required value(s)', $error_code);
        }
        $o_ugm = new PeopleGroupMapModel($this->o_db);
        $o_people = new PeopleModel($this->o_db);
        if (is_array($group_id)) {
            try {
                $results = $o_ugm->read($group_id);
            }
            catch (ModelException $e) {
                throw new ModelException('Could not read the map records', $e->getCode(), $e);
            }
        }
        else {
            try {
                $results = $o_ugm->read(['group_id' => $group_id]);
            }
            catch (ModelException $e) {
                throw new ModelException('Could not read the map records.', $e->getCode(), $e);
            }
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
            $a_people_ids = [];
            $a_map_ids = [];
            foreach ($results as $a_record) {
                $a_people_ids[] = $a_record['people_id'];
                $a_map_ids[] = $a_record['pgm_id'];
            }
            try {
                $o_people->delete($a_people_ids);
            }
            catch (ModelException $e) {
                throw new ModelException('Could not delete the people records.', $e->getCode(), $e);
            }
            try {
                $o_ugm->delete($a_map_ids);
            }
            catch (ModelException $e) {
                throw new ModelException('Could not delete the people group map records.', $e->getCode(), $e);
            }
        }
        try {
            $this->delete($group_id);
            if ($transaction) {
                try {
                    $this->o_db->commitTransaction();
                }
                catch (ModelException $e) {
                    $this->o_db->rollbackTransaction();
                    $this->error_message = $this->o_db->getSqlErrorMessage();
                    throw new ModelException($this->error_message, 410);
                }
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
        return true;
    }

    ### Shortcuts ###
    /**
     * Returns a record of the group specified by id.
     * @param int $group_id
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readById($group_id = -1)
    {
        if (is_numeric($group_id) && $group_id > 0) {
            try {
                $results = $this->read(array('group_id' => $group_id));
                return $results[0];
            }
            catch (ModelException $e) {
                $this->error_message = "Unable to find a group with the group id {$group_id}";
                throw new ModelException($this->error_message,210, $e);
            }
        }
        else {
            throw new ModelException('Missing group id', 220);
        }
    }

    /**
     * Returns a record of the group specified by name.
     * @param string $group_name
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readByName($group_name = '')
    {
        if ($group_name == '') {
            throw new ModelException('Missing group name', 220);
        }
        try {
            $results = $this->read(array('group_name' => $group_name));
            if (!empty($results[0])) {
                return $results[0];
            }
            $this->error_message = 'Unable to read the group by ' . $group_name;
            throw new ModelException($this->error_message, 210);
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to read the group by ' . $group_name;
            throw new ModelException($this->error_message, 210);
        }

    }

    /**
     * Checks to see if the id is a valid group id.
     * @param int $group_id required
     * @return bool
     */
    public function isValidGroupId($group_id = -1)
    {
        if (is_numeric($group_id) && $group_id > 0) {
            try {
                $a_results = $this->read(array('group_id' => $group_id));
                if (!empty($a_results)) {
                    return true;
                }
            }
            catch (ModelException $e) {
                $this->error_message = "Could not do the read operation.";
            }
        }
        return false;
    }

}
