<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file UserRoleMapModel.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Models
 *  @class UserRoleMapModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2014-01-18 15:08:49
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - Initial version 01/18/2014 wer
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
     *  Creates a new user record in the user table.
     *  @param array $a_values required
     *  @return int|bool
    **/
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'username',
            'real_name',
            'short_name',
            'password',
            'is_default'
        );
        if (!$this->o_arrays->hasRequiredKeys($a_required_keys, $a_values)) {
            return false;
        }
        $sql = "
            INSERT INTO library_users (username, real_name, short_name, password, is_default)
            VALUES (:username, :real_name, :short_name, :password, :is_default)
        ";
        if ($this->o_db->insert($sql, $a_values, 'library_users')) {
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
            $a_search_params = array('order_by', 'username');
            $a_allowed_keys = array(
                'username',
                'real_name',
                'short_name'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        $sql = "
            SELECT *
            FROM library_users
            {$where}
        ";
        $this->o_elog->write($sql, LOG_ON, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     *  Updates the record, NOT!
     *  This should never happen! Always return false.
     *      Reasoning. The group_id and user_id form a unique index. As such
     *      they should not be modified. The record should always be deleted and
     *      a new one added.
     *      Method is required by interface.
     *  @param array $a_values
     *  @return bool
     */
    public function update(array $a_values = array())
    {
        return false;
    }
    public function delete($id = '')
    {
        if ($id == '') { return false; }
        $sql = "
            DELETE FROM library_users
            WHERE id = :id
        ";
        return $this->o_db->delete($sql, array(':id' => $id), true);
    }
}
