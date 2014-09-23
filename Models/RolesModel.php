<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file RolesModel.php
 *  @inrole library models
 *  @namespace Ritc/Library/Models
 *  @class RolesModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1β
 *  @date 2014-09-23 13:10:31
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1β - extends the Base class, injects the DbModel, clean up - 09/23/2014 wer
 *      v1.0.0β - First live version - 09/15/2014 wer
 *      v0.1.0β - Initial version    - 01/18/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Core\Arrays;
use Ritc\Library\Core\Base;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Interfaces\ModelInterface;

class RolesModel extends Base implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_arrays;
    private $o_db;
    protected $o_elog;

    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->o_arrays  = new Arrays;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }

    ### BASE CRUD ###
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'role_name',
            'role_description',
            'role_level'
        );
        if (!$this->o_arrays->hasRequiredKeys($a_required_keys, $a_values)) {
            return false;
        }
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
            return false;
        }
    }
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
    public function update(array $a_values = array())
    {
        if (   !isset($a_values['role_id'])
            || $a_values['role_id'] == ''
            || !ctype_digit($a_values['role_id'])
        ) {
            return false;
        }
        $a_allowed_keys = ['role_id', 'role_name', 'role_description', 'role_level'];
        $a_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_values);
        $set_sql = $this->o_db->buildSqlSet($a_values, ['role_id']);
        $sql = "
            UPDATE {$this->db_prefix}roles
            {$set_sql}
            WHERE role_id = :role_id
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->update($sql, $a_values, true);
    }
    public function delete($role_id = '')
    {
        if ($role_id == -1) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}roles
            WHERE role_id = :role_id
        ";
        return $this->o_db->delete($sql, array(':role_id' => $role_id), true);
    }

    ### Specialized CRUD ###
    /**
     *  Selects a role record by the id.
     *  @param int $role_id
     *  @return array
    **/
    public function readById($role_id = -1)
    {
        if ($role_id == -1) { return false; }
        if (!ctype_digit($role_id)) { return false; }
        $results = $this->read(array('role_id' => $role_id));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }
    /**
     *  Returns a record of the role specified by name.
     *  @param string $role_name
     *  @return array()
     */
    public function readyByName($role_name = '')
    {
        if ($role_name == '') { return false; }
        $results = $this->read(array('role_name' => $role_name));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
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
