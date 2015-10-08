<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file GroupsModel.php
 *  @ingroup ritc_library models
 *  @namespace Ritc/Library/Models
 *  @class GroupsModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β4
 *  @date 2015-10-08 11:20:32
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β4 - added group_immutable field in db and changed code to match  - 10/08/2015 wer
 *      v1.0.0ß3 - removed abstract class Base, used LogitTraits                - 09/01/2015 wer
 *      v1.0.0ß2 - changed to use IOC (Inversion of Control)                    - 11/15/2014 wer
 *      v1.0.0β1 - extends the Base class, injects the DbModel, clean up        - 09/23/2014 wer
 *      v1.0.0β0 - First live version                                           - 09/15/2014 wer
 *      v0.1.0β  - Initial version                                              - 01/18/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class GroupsModel implements ModelInterface
{
    use LogitTraits;

    private $db_prefix;
    private $db_type;
    private $error_message;
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
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
            'group_immutable'
        );
        $a_values = Arrays::createRequiredPairs($a_values, $a_required_keys, true);
        $sql = "
            INSERT INTO {$this->db_prefix}groups
                (group_name, group_description, group_immutable)
            VALUES
                (:group_name, :group_description, :group_immutable)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}groups")) {
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
                'group_immutable'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY 'group_name'";
        }
        $sql = "
            SELECT group_id, group_name, group_description, group_immutable
            FROM {$this->db_prefix}groups
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
        if (   !isset($a_values['group_id'])
            || $a_values['group_id'] == ''
            || !ctype_digit($a_values['group_id'])
        ) {
            return false;
        }
        $a_permitted_keys = ['group_id', 'group_name', 'group_description', 'group_immutable'];
        $a_values = Arrays::removeUndesiredPairs($a_values, $a_permitted_keys);

        $set_sql = $this->o_db->buildSqlSet($a_values, ['group_id']);
        if ($set_sql == '') {
            return true;
        }
        $sql = "
            UPDATE {$this->db_prefix}groups
            {$set_sql}
            WHERE group_id = :group_id
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
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
            DELETE FROM {$this->db_prefix}groups
            WHERE group_id = :group_id
        ";
        return $this->o_db->delete($sql, array(':group_id' => $group_id), true);
    }

    ### Complex with Relations ###
    /**
     * Generic create function to create a single record.
     * @param array $a_values required
     * @return bool
     */
    public function createWithRoles(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'group_name',
            'group_description',
            'group_immutable',
            'roles'
        );
        $a_values = Arrays::createRequiredPairs($a_values, $a_required_keys, true);
        $a_roles = $a_values['roles'];
        unset($a_values['roles']);
        if ($this->o_db->startTransaction()) {
            $new_id = $this->create($a_values);
            if ($new_id !== false) {
                if (count($a_roles) > 0) {
                    $o_grm = new GroupRoleMapModel($this->o_db);
                    $rollback = false;
                    foreach($a_roles as $role_id) {
                        $a_grm = ['group_id' => $new_id, 'role_id' => $role_id];
                        if (!$o_grm->create($a_grm)) {
                            $rollback = true;
                            break;
                        }
                    }
                    if ($rollback === false) {
                        if ($this->o_db->commitTransaction()) {
                            return $new_id;
                        }
                    }
                }
            }
            $this->error_message = $this->o_db->getSqlErrorMessage();
            $this->o_db->rollbackTransaction();
            return false;
        }
        return false;
    }
    /**
     * Updates the group record and group_roles_map records
     * @param array $a_values
     * @return bool
     */
    public function updateWithRoles(array $a_values = array())
    {
        $meth = __METHOD__ . '.';
        if (   !isset($a_values['group_id'])
            || $a_values['group_id'] == ''
            || !ctype_digit($a_values['group_id'])
        ) {
            $this->error_message = "Missing valid group id.";
            return false;
        }
        $a_allowed_keys = ['group_id', 'group_name', 'group_description', 'group_immutable', 'roles'];
        $a_values = Arrays::removeUndesiredPairs($a_values, $a_allowed_keys);

        if (!Arrays::hasRequiredKeys($a_values, ['group_id', 'roles'])) {
            $this->error_message = "Missing required value pairs";
            return false;
        }
        $a_roles = $a_values['roles'];
        unset($a_values['roles']);
        $this->logIt('Roles: ' . var_export($a_roles, true), LOG_OFF, $meth . __LINE__);
        $commit = true;
        if ($this->o_db->startTransaction()) {
            if ($this->update($a_values)) {
                $o_grm = new GroupRoleMapModel($this->o_db);
                foreach($a_roles as $role_id) {
                    $a_new_grm_values = [
                        'group_id' => $a_values['group_id'],
                        'role_id'  => $role_id
                    ];
                    $a_exists = $o_grm->read($a_new_grm_values);
                    if (!isset($a_exists[0]) || $a_exists[0]['role_id'] != $role_id) {
                        $results = $o_grm->create($a_new_grm_values);
                    }
                    else {
                        $results = true;
                    }
                    if (!$results) {
                        $commit = false;
                        $this->logIt("Could not create new group role map!", LOG_OFF, $meth . __LINE__);
                        $this->error_message = $this->o_db->getSqlErrorMessage();
                        break;
                    }
                }
                if ($commit) {
                    if ($this->o_db->commitTransaction()) {
                        return true;
                    }
                }
            }
            else {
                $this->error_message = $this->o_db->getSqlErrorMessage();
            }
            $this->o_db->rollbackTransaction();
        }
        else {
            $this->error_message = "Could not start transaction.";
        }
        return false;
    }
    /**
     * Deletes related records as well as main group record.
     * @param int $group_id
     * @return bool
     */
    public function deleteWithRelated($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        $o_grm = new GroupRoleMapModel($this->o_db);
        $o_ugm = new PeopleGroupMapModel($this->o_db);
        if ($this->o_db->startTransaction()) {
            if ($o_grm->deleteByGroupId($group_id)) {
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
        }
        $this->error_message = $this->o_db->getSqlErrorMessage();
        $this->o_db->rollbackTransaction();
        return false;
    }

    ### Shortcuts ###
    /**
     *  Returns a record of the group specified by id.
     *  @param int $group_id
     *  @return array|bool
     */
    public function readById($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        if (!ctype_digit($group_id)) { return false; }
        $results = $this->read(array('group_id' => $group_id));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }
    /**
     *  Returns a record of the group specified by name.
     *  @param string $group_name
     *  @return array()
     */
    public function readyByName($group_name = '')
    {
        if ($group_name == '') { return false; }
        $results = $this->read(array('group_name' => $group_name));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }
    /**
     *  Checks to see if the id is a valid group id.
     *  @param int $group_id
     *  @return bool true or false
     **/
    public function isValidGroupId($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        if (!ctype_digit($group_id)) { return false; }
        if (is_array($this->read(array('group_id' => $group_id)))) {
            return true;
        }
        return false;
    }
    public function getErrorMessage()
    {
        return $this->error_message;
    }
}
