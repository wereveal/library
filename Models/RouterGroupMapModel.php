<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file RouterGroupMapModel.php
 *  @ingroup ritc_library models
 * @namespace Ritc\Library\Models
 *  @class RouterGroupMapModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-08-01 14:01:09
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0ß1 - Initial version                                               - 08/01/2015 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class RouterGroupMapModel implements ModelInterface
{
    use LogitTraits;

    private $db_prefix;
    private $db_type;
    private $error_message;
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $o_db->getDbType();
        $this->db_prefix = $o_db->getDbPrefix();
    }

    ### Basic CRUD commands, required by interface ###
    /**
     *  Creates a new group_role map record in the routes_group_map table.
     *  @param array $a_values required
     *  @return int|bool
    **/
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = [
            'route_id',
            'group_id'
        ];
        $a_values = Arrays::removeUndesiredPairs($a_values, $a_required_keys);
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}routes_group_map (route_id, group_id)
            VALUES (:route_id, :group_id)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}routes_group_map")) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        }
        else {
            return false;
        }

    }
    /**
     * @param array $a_search_values ['rgm_id', 'group_id', 'route_id']
     * @return mixed
     */
    public function read(array $a_search_values = array())
    {
        $where = '';
        if ($a_search_values != array()) {
            $a_search_params = array('order_by' => 'route_id');
            $a_allowed_keys = array(
                'group_id',
                'route_id',
                'rgm_id'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        $sql = "
            SELECT *
            FROM {$this->db_prefix}routes_group_map
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
            FROM {$this->db_prefix}routes_group_map
            ORDER BY route_id
        ";
        return $this->o_db->search($sql);
    }
    /**
     *  Updates the record, NOT! Well, sort of.
     *  Method is required by interface.
     *      Update should never happen!
     *      Reasoning. The group_id and route_id form a unique index. As such
     *      they should not be modified. The record should always be deleted and
     *      a new one added. That is what this function actually does.
     *  @param array $a_values
     *  @return bool
     */
    public function update(array $a_values = array())
    {
        $a_required_keys = array(
            'rgm_id',
            'group_id',
            'route_id'
        );
        $a_values = Arrays::removeUndesiredPairs($a_values, $a_required_keys);
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            return false;
        }
        if ($this->o_db->startTransaction()) {
            if ($this->delete($a_values['rgm_id'])) {
                if ($this->create(['group_id' => $a_values['group_id'], 'route_id' => $a_values['route_id']])) {
                    if ($this->o_db->commitTransaction()) {
                        return true;
                    }
                }
            }
            $this->o_db->rollbackTransaction();
        }
        $this->error_message = $this->o_db->getSqlErrorMessage();
        return false;
    }
    /**
     * Deletes a record by rgm_id.
     * @param string $rgm_id
     * @return bool
     */
    public function delete($rgm_id = '')
    {
        if ($rgm_id == '') { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}routes_group_map
            WHERE rgm_id = :rgm_id
        ";
        return $this->o_db->delete($sql, array(':rgm_id' => $rgm_id), true);
    }
    /**
     * Deletes record(s) by Route ID.
     * @param int $route_id
     * @return bool
     */
    public function deleteByRouteId($route_id = -1)
    {
        if ($route_id == -1) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}routes_group_map
            WHERE route_id = :route_id
        ";
        return $this->o_db->delete($sql, array(':route_id' => $route_id), true);
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
            DELETE FROM {$this->db_prefix}routes_group_map
            WHERE group_id = :group_id
        ";
        return $this->o_db->delete($sql, array(':group_id' => $group_id), true);
    }

    ### Required by Interface ###
    public function getErrorMessage()
    {
        return $this->error_message;
    }
}
