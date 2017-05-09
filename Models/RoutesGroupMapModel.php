<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/RoutesGroupMapModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.3
 * @date      2017-05-09 17:37:59
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.3 - DbUtilityTraits change reflected here                         - 2017-05-09 wer
 * - v1.0.0-alpha.2 - Bug fix                                                       - 2017-01-27 wer
 * - v1.0.0-alpha.1 - Bug Fix to read method to match interface                     - 2016-03-24 wer
 * - v1.0.0-alpha.0 - Initial version                                               - 08/01/2015 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class RoutesGroupMapModel.
 * @class   RoutesGroupMapModel
 * @package Ritc\Library\Models
 */
class RoutesGroupMapModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * RoutesGroupMapModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'routes_group_map');
    }

    ### Basic CRUD commands, required by interface ###
    /**
     * Creates a new group_role map record in the routes_group_map table.
     * @param array $a_values required
     * @return int|bool
     */
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
            INSERT INTO {$this->db_table} (route_id, group_id)
            VALUES (:route_id, :group_id)
        ";
        $a_table_info = [
            'table_name'  => "{$this->db_table}",
            'column_name' => 'rgm_id'
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
     * @param array $a_search_values     ['rgm_id', 'group_id', 'route_id']
     * @param array $a_search_parameters \ref searchparams \ref readparams
     * @return mixed
     */
    public function read(array $a_search_values = [], array $a_search_parameters = [])
    {
        $a_search_parameters['a_search_for'] = $a_search_values;
        $a_search_parameters['table_name']   = $this->db_table;
        if (!isset($a_search_parameters['order_by'])) {
            $a_search_parameters['order_by'] = 'group_id ASC, route_id ASC';
        }
        return $this->genericRead($a_search_parameters);
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
            FROM {$this->db_table}
            ORDER BY route_id
        ";
        return $this->o_db->search($sql);
    }

    /**
     * Updates the record, NOT! Well, sort of.
     * Method is required by interface.
     *     Update should never happen!
     *     Reasoning. The group_id and route_id form a unique index. As such
     *     they should not be modified. The record should always be deleted and
     *     a new one added. That is what this function actually does.
     * @param array $a_values
     * @return bool
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
            DELETE FROM {$this->db_table}
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
            DELETE FROM {$this->db_table}
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
            DELETE FROM {$this->db_table}
            WHERE group_id = :group_id
        ";
        return $this->o_db->delete($sql, array(':group_id' => $group_id), true);
    }

}
