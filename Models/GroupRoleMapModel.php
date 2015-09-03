<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file GroupRoleMapModel.php
 *  @ingroup ritc_library models
 *  @namespace Ritc/Library/Models
 *  @class GroupRoleMapModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β6
 *  @date 2015-09-03 12:23:20
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β6 - Removed abstract class Base, use LogitTraits                  - 09/03/2015 wer
 *      v1.0.0β5 - Changed name to match DB change                               - 01/19/2015 wer
 *      v1.0.0ß4 - reverted back to injecting DbModel                            - 11/17/2014 wer
 *      v1.0.0ß3 - changed to use DI/IOC                                         - 11/15/2014 wer
 *      v1.0.0ß2 - extends the Base class, injects the DbModel, clean up         - 09/23/2014 wer
 *      v1.0.0ß1 - First live version                                            - 09/15/2014 wer
 *      v0.1.0ß1 - Initial version                                               - 01/18/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class GroupRoleMapModel implements ModelInterface
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

    ### Basic CRUD commands, required by interface ###
    /**
     *  Creates a new group_role map record in the group_role_map table.
     *  @param array $a_values required
     *  @return int|bool
    **/
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = [
            'group_id',
            'role_id'
        ];
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}group_role_map (group_id, role_id)
            VALUES (:group_id, :role_id)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}group_role_map")) {
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
     * @return mixed
     */
    public function read(array $a_search_values = array())
    {
        $where = '';
        if ($a_search_values != array()) {
            $a_search_params = array('order_by' => 'group_id');
            $a_allowed_keys = array(
                'role_id',
                'group_id',
                'grm_id'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        $sql = "
            SELECT *
            FROM {$this->db_prefix}group_role_map
            {$where}
        ";
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     * Returns all records in the table.
     * @param none
     * @return array | bool
     */
    public function readAll()
    {
        $sql = "
            SELECT *
            FROM {$this->db_prefix}group_role_map
            ORDER BY group_id
        ";
        return $this->o_db->search($sql);
    }
    /**
     *  Updates the record, NOT!
     *  Method is required by interface.
     *      Update should never happen! Always return false.
     *      Reasoning. The role_id and group_id form a unique index. As such
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
     * Deletes a record by grm ID.
     * @param string $grm_id
     * @return bool
     */
    public function delete($grm_id = '')
    {
        if ($grm_id == '') { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}group_role_map
            WHERE grm_id = :grm_id
        ";
        return $this->o_db->delete($sql, array(':grm_id' => $grm_id), true);
    }
    /**
     * Deletes record(s) by Group ID.
     * @param int $group_id
     * @return bool
     */
    public function deleteByGroupId($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}group_role_map
            WHERE group_id = :group_id
        ";
        return $this->o_db->delete($sql, array(':group_id' => $group_id), true);
    }
    /**
     * Deletes record(s) by Role ID.
     * @param int $role_id
     * @return bool
     */
    public function deleteByRoleId($role_id = -1)
    {
        if ($role_id == -1) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}group_role_map
            WHERE role_id = :role_id
        ";
        return $this->o_db->delete($sql, array(':role_id' => $role_id), true);
    }

    ### Required by Interface ###
    public function getErrorMessage()
    {
        $this->o_db->getSqlErrorMessage();
    }
}
