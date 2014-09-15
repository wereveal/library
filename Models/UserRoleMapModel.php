<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file UserRoleMapModel.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Models
 *  @class UserRoleMapModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-09-15 14:55:03
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - First live version - 09/15/2014 wer
 *      v0.1.0 - Initial version    - 01/18/2014 wer
 *  </pre>
 *  @todo change all methods to be for the proper database table
**/
namespace Ritc\Library\Models;

use Ritc\Library\Core\Arrays;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\Elog;
use Ritc\Library\Interfaces\ModelInterface;

class UserRoleMapModel implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_arrays;
    private $o_db;
    private $o_elog;

    public function __construct(DbModel $o_db)
    {
        $this->o_elog    = Elog::start();
        $this->o_db      = $o_db;
        $this->o_arrays  = new Arrays();
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }

    ### Basic CRUD commands, required by interface ###
    /**
     *  Creates a new user_role map record in the user_role_map table.
     *  @param array $a_values required
     *  @return int|bool
    **/
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'user_id',
            'role_id'
        );
        if (!$this->o_arrays->hasRequiredKeys($a_required_keys, $a_values)) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}user_role_map (user_id, role_id)
            VALUES (:user_id, :role_id)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}user_role_map")) {
            $ids = $this->o_db->getNewIds();
            $this->o_elog->write("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        } else {
            return false;
        }

    }
    public function read(array $a_search_values = array())
    {
        $where = '';
        if ($a_search_values != array()) {
            $a_search_params = array('order_by' => 'user_id');
            $a_allowed_keys = array(
                'role_id',
                'user_id',
                'urm_id'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        $sql = "
            SELECT *
            FROM {$this->db_prefix}user_role_map
            {$where}
        ";
        $this->o_elog->write($sql, LOG_ON, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     *  Updates the record, NOT!
     *  Method is required by interface.
     *      Update should never happen! Always return false.
     *      Reasoning. The role_id and user_id form a unique index. As such
     *      they should not be modified. The record should always be deleted and
     *      a new one added.
     *  @param array $a_values
     *  @return bool
     */
    public function update(array $a_values = array())
    {
        return false;
    }
    public function delete($urm_id = '')
    {
        if ($urm_id == '') { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}user_role_map
            WHERE urm_id = :urm_id
        ";
        return $this->o_db->delete($sql, array(':urm_id' => $urm_id), true);
    }
}
