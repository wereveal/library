<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/GroupsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.1.0+01
 * @date      2016-08-29 10:59:47
 * @note <b>Change Log</b>
 * - v1.1.0   - Bug fix and changes due to refactoring of DbModel            - 2016-03-19 wer
 * - v1.0.0   - First working version                                        - 11/27/2015 wer
 * - v1.0.0β5 - refactoring to provide postgresql compatibility              - 11/22/2015 wer
 * - v1.0.0β4 - added group_immutable field in db and changed code to match  - 10/08/2015 wer
 * - v1.0.0ß3 - removed abstract class Base, used LogitTraits                - 09/01/2015 wer
 * - v1.0.0ß2 - changed to use IOC (Inversion of Control)                    - 11/15/2014 wer
 * - v1.0.0β1 - extends the Base class, injects the DbModel, clean up        - 09/23/2014 wer
 * - v1.0.0β0 - First live version                                           - 09/15/2014 wer
 * - v0.1.0β  - Initial version                                              - 01/18/2014 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
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
     */
    public function create(array $a_values = array())
    {
        $a_required_keys = array(
            'group_name',
            'group_description',
            'group_auth_level',
            'group_immutable'
        );
        $a_values = Arrays::createRequiredPairs($a_values, $a_required_keys, true);
        if (isset($a_values['group_auth_level']) && ($a_values['group_auth_level'] == '' || $a_values['group_auth_level'] > 10)) {
            $a_values['group_auth_level'] = 0;
        }
        if (!isset($a_values['group_immutable']) || $a_values['group_immutable'] == '' || $a_values['group_immutable'] > 1) {
            $a_values['group_immutable'] = 0;
        }
        $a_required_keys = ['group_name', 'group_description'];
        if (Arrays::hasBlankValues($a_values, $a_required_keys)) {
            $missing_info = '';
            foreach ($a_required_keys as $key_name) {
                if ($a_values[$key_name] == '') {
                    switch ($key_name) {
                        case 'group_name':
                            $missing_info .= " Name";
                            break;
                        case 'group_description':
                            $missing_info .= " Description";
                            break;
                        default:
                            $missing_info .= ' Unknown Error';
                    }
                }
            }
            $this->error_message = 'Missing required information:' . $missing_info;
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_table}
                (group_name, group_description, group_auth_level, group_immutable)
            VALUES
                (:group_name, :group_description, :group_auth_level, :group_immutable)
        ";
        $a_table_info = [
            'table_name'  => $this->db_table,
            'column_name' => 'group_id'
        ];
        if ($this->o_db->insert($sql, $a_values, $a_table_info)) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        }
        else {
            return false;
        }
    }

    /**
     * @param array $a_search_values
     * @param array $a_search_params
     * @return mixed
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? array('order_by' => 'group_name')
                : $a_search_params;
            $a_allowed_keys = array(
                'group_id',
                'group_name',
                'group_auth_level',
                'group_immutable'
            );
            $a_search_values = $this->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY group_name";
        }
        $sql = "
            SELECT group_id, group_name, group_description, group_auth_level, group_immutable
            FROM {$this->db_table}
            {$where}
        ";
        return $this->o_db->search($sql, $a_search_values);
    }

    /**
     * Updates the group record
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values = array())
    {
        $a_required_keys = ['group_id'];
        $a_values = Arrays::createRequiredPairs($a_values, $a_required_keys, false);
        if (!isset($a_values['group_immutable']) || $a_values['group_immutable'] == '' || $a_values['group_immutable'] > 1 ) {
            $a_values['group_immutable'] = 0;
        }
        if (Arrays::hasBlankValues($a_values, $a_required_keys)) {
            $missing_info = '';
            foreach ($a_required_keys as $key_name) {
                if ($a_values[$key_name] == '') {
                    $missing_info = ' ' . $key_name;
                }
            }
            $this->error_message = 'Missing required information:' . $missing_info;
            return false;
        }
        if ($a_values['group_name'] == '') {
            unset($a_values['group_name']);
        }
        if ($a_values['group_description'] == '') {
            unset($a_values['group_description']);
        }
        $set_sql = $this->buildSqlSet($a_values, ['group_id']);
        $sql = "
            UPDATE {$this->db_table}
            {$set_sql}
            WHERE group_id = :group_id
        ";
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     * Deletes the specific record.
     * NOTE: this could leave orphaned records in the user_group_map table and group_role_map table
     * if the database isn't set up for relations. If not sure, or want more control, use the
     * deleteWithRelated method.
     * @param int $group_id
     * @return bool
     */
    public function delete($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE group_id = :group_id
        ";
        return $this->o_db->delete($sql, array(':group_id' => $group_id), true);
    }

    /**
     * Deletes related records as well as main group record.
     * @param int $group_id
     * @return bool
     */
    public function deleteWithRelated($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        $o_ugm = new PeopleGroupMapModel($this->o_db);
        if ($this->o_db->startTransaction()) {
            if ($o_ugm->deleteByGroupId($group_id)) {
               if ($this->delete($group_id)) {
                    if ($this->o_db->commitTransaction() === false) {
                        $this->o_db->rollbackTransaction();
                        $this->error_message = $this->o_db->getSqlErrorMessage();
                        return false;
                    }
                    else {
                        return true;
                    }
                }
            }
        }
        $this->error_message = $this->o_db->getSqlErrorMessage();
        $this->o_db->rollbackTransaction();
        return false;
    }

    ### Shortcuts ###
    /**
     * Returns a record of the group specified by id.
     * @param int $group_id
     * @return array|bool
     */
    public function readById($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        if (!is_numeric($group_id)) { return false; }
        $results = $this->read(array('group_id' => $group_id));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }

    /**
     * Returns a record of the group specified by name.
     * @param string $group_name
     * @return array|bool
     */
    public function readByName($group_name = '')
    {
        if ($group_name == '') { return false; }
        $results = $this->read(array('group_name' => $group_name));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }

    /**
     * Checks to see if the id is a valid group id.
     * @param int $group_id
     * @return bool true or false
      */
    public function isValidGroupId($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        if (!is_numeric($group_id)) { return false; }
        if (is_array($this->read(array('group_id' => $group_id)))) {
            return true;
        }
        return false;
    }

}
