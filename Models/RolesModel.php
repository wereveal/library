<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file RolesModel.php
 *  @inrole library models
 *  @namespace Ritc/Library/Models
 *  @class RolesModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.3β
 *  @date 2014-11-17 14:27:18
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.3ß - reverted to injecting the DbModel           - 11/17/2014 wer
 *      v1.0.2ß - changed to use DI/IOC                       - 11/15/2014 wer
 *      v1.0.1β - extends the Base class, injects the DbModel - 09/23/2014 wer
 *      v1.0.0β - First live version                          - 09/15/2014 wer
 *      v0.1.0β - Initial version                             - 01/18/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;

class RolesModel extends Base implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $o_db->getDbType();
        $this->db_prefix = $o_db->getDbPrefix();
    }

    ### BASE CRUD ###
    /**
     * Creates a new record
     * @param array $a_values
     * @return int positive numbers are the ids, negative numbers are error codes.
     *             -1 = array was empty
     *             -2 = array does not have required keys
     *             -3 = role level is not allowed
     *             -4 = database operation was not successful
     */
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return -1; }
        $a_required_keys = array(
            'role_name',
            'role_description',
            'role_level'
        );
        if (!Arrays::hasRequiredKeys($a_required_keys, $a_values)) {
            return -2;
        }
        if ($a_values['role_level'] <= 2) {
            return -3;
        }
        $a_values['role_name'] = strtolower(Strings::makeAlpha($a_values['role_name']));

        $sql = "
            INSERT INTO {$this->db_prefix}roles
                (role_name, role_description, role_level)
            VALUES
                (:role_name, :role_description, :role_level)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}roles")) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        }
        else {
            return -4;
        }
    }
    /**
     * Returns the records for the search values.
     * @param array $a_search_values
     * @param array $a_search_params
     * @return mixed results of the datase operation.
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? array('order_by' => 'role_name')
                : $a_search_params;
            $a_allowed_keys = array(
                'role_id',
                'role_name',
                'role_level'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY 'role_name'";
        }
        $sql = "
            SELECT role_id, role_name, role_description, role_level
            FROM {$this->db_prefix}roles
            {$where}
        ";
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     * Update the role record.
     * @param array $a_values
     * @return int  1 = successful database operation
     *             -1 = unsuccessful database operation
     *             -2 = role_id was not set
     */
    public function update(array $a_values = array())
    {
        if (   !isset($a_values['role_id'])
            || $a_values['role_id'] == ''
            || !ctype_digit($a_values['role_id'])
        ) {
            return -2;
        }
        $a_allowed_keys = ['role_id', 'role_name', 'role_description', 'role_level'];
        $a_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_values);

        if (isset($a_values['role_name']) && $a_values['role_name'] != '') {
            $a_values['role_name'] = strtolower(Strings::makeAlpha($a_values['role_name']));
        }

        $set_sql = $this->o_db->buildSqlSet($a_values, ['role_id']);
        $sql = "
            UPDATE {$this->db_prefix}roles
            {$set_sql}
            WHERE role_id = :role_id
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->update($sql, $a_values, true);
        if ($results) {
            return 1;
        }
        else {
            return -1;
        }
    }
    /**
     * Deletes a role record.
     * @param string $role_id
     * @return bool
     */
    public function delete($role_id = '')
    {
        if ($role_id == -1) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}roles
            WHERE role_id = :role_id
        ";
        if ($this->o_db->delete($sql, array(':role_id' => $role_id), true)) {
            if ($this->o_db->getAffectedRows() === 0) {
                $a_results = [
                    'message' => 'The role was not deleted.',
                    'type'    => 'failure'
                ];
            }
            else {
                $a_results = [
                    'message' => 'Success!',
                    'type'    => 'success'
                ];
            }
        }
        else {
            $a_results = [
                'message' => 'A problem occurred and the role was not deleted.',
                'type'    => 'failure'
            ];
        }
        return $a_results;
    }

    ### Specialized CRUD ###
    /**
     *  Selects a role record by the id.
     *  @param int $role_id
     *  @return array
    **/
    public function readById($role_id = -1)
    {
        if ($role_id == -1) { return array(); }
        if (!ctype_digit($role_id)) { return false; }
        $results = $this->read(array('role_id' => $role_id));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return array();
    }
    /**
     *  Returns a record of the role specified by name.
     *  @param string $role_name
     *  @return array()
     */
    public function readyByName($role_name = '')
    {
        if ($role_name == '') { return array(); }
        $results = $this->read(array('role_name' => $role_name));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return array();
    }

    ### Validators ###
    /**
     *  Checks to see if the id is a valid role id.
     *  @param int $role_id
     *  @return bool true or false
     **/
    public function isValidId($role_id = -1)
    {
        if ($role_id == -1) { return false; }
        $role_id = (int) $role_id;
        if (is_array($this->read(array('role_id' => $role_id)))) {
            return true;
        }
        return false;
    }
}
