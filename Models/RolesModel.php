<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file RolesModel.php
 *  @inrole ritc_library models
 *  @namespace Ritc/Library/Models
 *  @class RolesModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.3
 *  @date 2015-10-05 14:45:43
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.3   - new method, bug fix, db table update        - 10/05/2015 wer
 *      v1.0.2   - bug fixes                                   - 09/24/2015 wer
 *      v1.0.1   - refactoring elsewhere changes here to match - 07/31/2015 wer
 *      v1.0.0   - First working version                       - 01/28/2015 wer
 *      v1.0.0ß5 - reverted to injecting the DbModel           - 11/17/2014 wer
 *      v1.0.0ß4 - changed to use DI/IOC                       - 11/15/2014 wer
 *      v1.0.0β3 - extends the Base class, injects the DbModel - 09/23/2014 wer
 *      v1.0.0β2 - First live version                          - 09/15/2014 wer
 *      v1.0.0β1 - Initial version                             - 01/18/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class RolesModel implements ModelInterface
{
    use LogitTraits;

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
     *             see method getErrorMessage for values
     */
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return -1; }
        $a_required_keys = array(
            'role_name',
            'role_description',
            'role_level',
            'role_immutable'
        );
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            return -2;
        }
        if ($a_values['role_level'] <= 2) {
            return -3;
        }
        $a_values['role_name'] = strtolower(Strings::makeAlpha($a_values['role_name']));

        $a_results = $this->readyByName($a_values['role_name']);
        if ($a_results !== false && isset($a_results['role_id'])) {
            return -5;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}roles
                (role_name, role_description, role_level, role_immutable)
            VALUES
                (:role_name, :role_description, :role_level, :role_immutable)
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
                'role_level',
                'role_immutable'
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
            SELECT role_id, role_name, role_description, role_level, role_immutable
            FROM {$this->db_prefix}roles
            {$where}
        ";
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     * Update the role record.
     * @param array $a_values
     * @return int  1 = successful database operation, negative for errors
     *              see method getErrorMessage for error values
     */
    public function update(array $a_values = array())
    {
        if (   !isset($a_values['role_id'])
            || $a_values['role_id'] == ''
            || !ctype_digit($a_values['role_id'])
        ) {
            return -2;
        }
        $a_allowed_keys = ['role_id', 'role_name', 'role_description', 'role_level', 'role_immutable'];
        $a_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_values);

        if (isset($a_values['role_level']) && $a_values['role_level'] <= 2) {
            return -3;
        }
        if (isset($a_values['role_name']) && $a_values['role_name'] != '') {
            $a_values['role_name'] = strtolower(Strings::makeAlpha($a_values['role_name']));
            $a_role = $this->readyByName($a_values['role_name']);
            if (isset($a_role['role_id']) && $a_role['role_id'] != $a_values['role_id']) {
                return -5;
            }
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
            return -4;
        }
    }
    /**
     * Deletes a role record.
     * @param string $role_id
     * @return array
     */
    public function delete($role_id = -1)
    {
        if ($role_id == -1) {
            return -2;
        }
        $sql = "
            DELETE FROM {$this->db_prefix}roles
            WHERE role_id = :role_id
        ";
        if ($this->o_db->delete($sql, array(':role_id' => $role_id), true)) {
            if ($this->o_db->getAffectedRows() === 0) {
                $results = -4;
            }
            else {
                $results = 1;
            }
        }
        else {
            $results = -4;
        }
        return $results;
    }

    ### Specialized CRUD ###
    /**
     *  Selects a role record by the id.
     *  @param int $role_id
     *  @return array
    **/
    public function readById($role_id = -1)
    {
        if ($role_id == -1) { return -2; }
        if (!ctype_digit($role_id)) { return -2; }
        $results = $this->read(['role_id' => $role_id]);
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return -4;
    }
    /**
     *  Returns a record of the role specified by name.
     *  @param string $role_name
     *  @return array|int
     */
    public function readyByName($role_name = '')
    {
        if ($role_name == '') {
            return -1;
        }
        $results = $this->read(array('role_name' => $role_name));
        if (isset($results[0])) {
            return $results[0];
        }
        return -1;
    }
    /**
     *  Selects the roles that are mapped to a group.
     *  @param int $group_id
     *  return array
     */
    public function readRolesForGroup($group_id = -1)
    {
        $sql = "
            SELECT r.role_id, r.role_name, r.role_description, r.role_level, r.role_immutable
            FROM {$this->db_prefix}roles as r
            JOIN {$this->db_prefix}group_role_map as grm
                WHERE grm.role_id = r.role_id
                AND grm.group_id = :group_id
        ";
        $a_search_values = [':group_id' => $group_id];
        $results = $this->o_db->search($sql, $a_search_values);
        if ($results === false) {
            return -4;
        }
        return $results;
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

    ### Other ###
    /**
     *  Returns text for an error message.
     *  @param  int    $error_id
     *  @return string
     */
    public function getErrorMessage($error_id = 0)
    {
        switch ($error_id) {
            case -1:
                return "Array was empty";
            case -2:
                return "Role ID was not set";
            case -3:
                return "Role Level is not allowed";
            case -4:
                return "Database operation was not successful";
            case -5:
                return "Role name already exists";
            default:
                return "Unknown Error";
        }
    }
}
