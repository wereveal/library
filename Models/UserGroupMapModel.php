<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file UserGroupMapModel.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Models
 *  @class UserGroupMapModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-09-15 14:55:45
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - First Live version - 09/15/2014 wer
 *      v0.1.0 - Initial version    - 01/18/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Core\Arrays;
use Ritc\Library\Core\DbFactory;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\Elog;
use Ritc\Library\Interfaces\ModelInterface;

class UserGroupMapModel implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_arrays;
    private $o_db;
    private $o_elog;

    public function __construct($db_config = 'db_config.php')
    {
        $this->o_elog = Elog::start();
        $o_dbf = DbFactory::start($db_config, 'rw');
        $o_pdo = $o_dbf->connect();
        if ($o_pdo !== false) {
            $this->o_db = new DbModel($o_pdo);
        }
        else {
            $this->o_elog->write('Could not connect to the database', LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
        $this->o_arrays = new Arrays;
        $this->db_type = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }

    ### Basic CRUD commands, required by interface ###
    /**
     *  Creates a new user group map record in the user_group_map table.
     *  @param array $a_values required
     *  @return int|bool
    **/
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'user_id',
            'group_id'
        );
        if (!$this->o_arrays->hasRequiredKeys($a_required_keys, $a_values)) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}user_group_map (user_id, group_id)
            VALUES (:user_id, :group_id)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}user_group_map")) {
            $ids = $this->o_db->getNewIds();
            $this->o_elog->write("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        } else {
            return false;
        }
    }

    /**
     * Returns record(s) from the library_user_group_map table
     * @param array $a_search_values
     * @return mixed
     */
    public function read(array $a_search_values = array())
    {
        $where = '';
        if ($a_search_values != array()) {
            $a_search_params = array('order_by' => 'user_id');
            $a_allowed_keys = array(
                'group_id',
                'user_id',
                'ugm_id'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        $sql = "
            SELECT *
            FROM {$this->db_prefix}user_group_map
            {$where}
        ";
        $this->o_elog->write($sql, LOG_ON, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql, $a_search_values);
    }

    /**
     *  Updates the record, NOT!
     *  Method is required by interface.
     *  Update is not allowed! Always return false.
     *      Reasoning. The group_id and user_id form a unique index. As such,
     *      they should not be modified. The record should always be deleted and
     *      a new one added.
     *  @param array $a_values
     *  @return bool
     */
    public function update(array $a_values = array())
    {
        return false;
    }

    /**
     * Deletes the record.
     * @param string $ugm_id required
     * @return bool
     */
    public function delete($ugm_id = '')
    {
        if ($ugm_id == '') { return false; }
        if (!ctype_digit($ugm_id)) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}user_group_map
            WHERE ugm_id = :ugm_id
        ";
        return $this->o_db->delete($sql, array(':ugm_id' => $ugm_id), true);
    }
}