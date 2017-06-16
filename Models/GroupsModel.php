<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/GroupsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.0
 * @date      2017-06-10 17:42:55
 * @note <b>Change Log</b>
 * - v2.0.0   - Refactored to use ModelException and DbUtilityTraits            - 2017-06-10 wer
 * - v1.1.2   - DbUtilityTraits change reflected here                           - 2017-05-09 wer
 * - v1.1.1   - Bug fix caused by slight change elsewhere                       - 2017-01-27 wer
 * - v1.1.0   - Bug fix and changes due to refactoring of DbModel               - 2016-03-19 wer
 * - v1.0.0   - First working version                                           - 11/27/2015 wer
 * - v1.0.0β5 - refactoring to provide postgresql compatibility                 - 11/22/2015 wer
 * - v1.0.0β4 - added group_immutable field in db and changed code to match     - 10/08/2015 wer
 * - v1.0.0ß3 - removed abstract class Base, used LogitTraits                   - 09/01/2015 wer
 * - v1.0.0ß2 - changed to use IOC (Inversion of Control)                       - 11/15/2014 wer
 * - v1.0.0β1 - extends the Base class, injects the DbModel, clean up           - 09/23/2014 wer
 * - v1.0.0β0 - First live version                                              - 09/15/2014 wer
 * - v0.1.0β  - Initial version                                                 - 01/18/2014 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class GroupsModel.
 * @class   GroupsModel
 * @package Ritc\Library\Models
 */
class GroupsModel implements ModelInterface
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
     * Generic create function to create a single record.
     * @param array $a_values required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = array())
    {
        $a_required_keys = [
            'group_name',
            'group_description',
            'group_auth_level',
            'group_immutable'
        ];
        $a_psql = [
            'table_name'  => $this->db_table,
            'column_name' => $this->primary_index_name
        ];
        $a_params = [
            'a_required_keys' => $a_required_keys,
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => $a_psql
        ];
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new ModelException($message, $code);
        }
    }

    /**
     * @param array $a_search_for
     * @param array $a_search_params
     * @return mixed
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function read(array $a_search_for = array(), array $a_search_params = array())
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_for,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'group_name ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new ModelException($message, $code);
        }
    }

    /**
     * Updates the group record
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values = array())
    {
        $a_values = $this->fixUpdateValues($a_values, 'group_immutable', ['group_name']);
        if ($a_values === false) {
            throw new ModelException('Missing Values', 320);
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new ModelException($message, $code);
        }
    }

    /**
     * Deletes the specific record.
     * NOTE: this could leave orphaned records in the user_group_map table and group_role_map table
     * if the database isn't set up for relations. If not sure, or want more control, use the
     * deleteWithRelated method.
     * @param int|array $group_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($group_id = -1)
    {
        if ($group_id == -1) {
            throw new ModelException('Missing required value.', 420);
        }
        if (is_array($group_id)) {
            $a_ids = $group_id;
        }
        else {
            $a_ids = [$group_id];
        }
        try {
            return $this->genericDeleteMultiple($a_ids);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to delete the record', 400);
        }
    }

    /**
     * Deletes related records as well as main group record.
     * @param int|array $group_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function deleteWithRelated($group_id = -1)
    {
        if ($group_id == -1 || empty($group_id)) {
            throw new ModelException('Missing required value(s)', 420);
        }
        try {
            $this->o_db->startTransaction();
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to start the transaction.', 30, $e);
        }
        $o_ugm = new PeopleGroupMapModel($this->o_db);
        $o_people = new PeopleModel($this->o_db);
        if (is_array($group_id)) {
            try {
                $results = $o_ugm->read($group_id);
            }
            catch (ModelException $e) {
                throw new ModelException('Could not read the map records', 400);
            }
        }
        else {
            try {
                $results = $o_ugm->read(['group_id' => $group_id]);
            }
            catch (ModelException $e) {
                throw new ModelException('Could not read the map records.', 400);
            }
        }
        $a_people_ids = [];
        $a_map_ids = [];
        foreach ($results as $a_record) {
            $a_people_ids[] = $a_record['people_id'];
            $a_map_ids[] = $a_record['pgm_id'];
        }
        try {
            $results = $o_people->delete($a_people_ids);
            if (!$results) {
                throw new ModelException('Could not delete the people records.', 400);
            }
            try {
                $results = $o_ugm->delete($a_map_ids);
                if (!$results) {
                    throw new ModelException('Could not delete the people group map records.', 400);
                }
                try {
                    $results = $this->delete($group_id);
                    if ($results) {
                        try {
                            $this->o_db->commitTransaction();
                        }
                        catch (ModelException $e) {
                            $this->o_db->rollbackTransaction();
                            $this->error_message = $this->o_db->getSqlErrorMessage();
                            throw new ModelException($this->error_message, 400);
                        }
                    }
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                }
            }
            catch (ModelException $e) {
                throw new ModelException($e->errorMessage(), $e->getCode(), $e);
            }
        }
        catch (ModelException $e) {
            $this->o_db->rollbackTransaction();
            $this->error_message = $this->o_db->getSqlErrorMessage();
            throw new ModelException($this->error_message, 400);
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
            throw new ModelException($this->error_message, 200);
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to read the group by ' . $group_name;
            throw new ModelException($this->error_message, 200);
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
