<?php
/**
 *  @brief     Does all the database CRUD stuff.
 *  @ingroup   ritc_library models
 *  @file      RoutesModel.php
 *  @namespace Ritc\Library\Models
 *  @class     RoutesModel
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.1.0
 *  @date      2015-11-22 18:02:52
 *  @note <pre><b>Change Log</b>
 *      v1.1.0   - refactoring to provide better postgresql compatibility - 11/22/2015 wer
 *      v1.0.2   - Database structure change reflected here.              - 09/03/2015 wer
 *      v1.0.1   - Refactoring elsewhere necessitated changes here        - 07/31/2015 wer
 *      v1.0.0   - first working version                                  - 01/28/2015 wer
 *      v1.0.0β2 - Changed to match some namespace changes, and bug fix   - 11/15/2014 wer
 *      v1.0.0β1 - First live version                                     - 11/11/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class RoutesModel implements ModelInterface
{
    use LogitTraits;

    /**
     * @var string
     */
    private $db_prefix;
    /**
     * @var string
     */
    private $db_type;
    /**
     * @var \Ritc\Library\Services\DbModel
     */
    private $o_db;

    /**
     * RoutesModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }

    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function create(array $a_values)
    {
        if ($a_values == array()) { return false; }
        $this->logIt(var_export($a_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $a_required_keys = [
            'route_path',
            'route_class',
            'route_method',
            'route_action'
        ];
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}routes
                (route_path, route_class, route_method, route_action)
            VALUES
                (:route_path, :route_class, :route_method, :route_action)
        ";
        $a_table_info = [
            'table_name'  => "{$this->db_prefix}routes",
            'column_name' => 'route_id'
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
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'route_path']
     * @return array
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? ['order_by' => 'route_path']
                : $a_search_params;
            $a_allowed_keys = [
                'route_id',
                'route_path',
                'route_class'
            ];
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY route_path";
        }
        $sql = "
            SELECT route_id, route_path, route_class, route_method, route_action, route_immutable
            FROM {$this->db_prefix}routes
            {$where}
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__);
        $results = $this->o_db->search($sql, $a_search_values);
        return $results;
    }
    /**
     * Generic update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values)
    {
        if (!isset($a_values['route_id'])
            || $a_values['route_id'] == ''
            || (is_string($a_values['route_id']) && !ctype_digit($a_values['route_id']))
        ) {
            return false;
        }
        $a_allowed_keys = [
            'route_id',
            'route_path',
            'route_class',
            'route_method',
            'route_action'
        ];
        $a_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_values);
        $set_sql = $this->o_db->buildSqlSet($a_values, ['route_id']);
        $sql = "
            UPDATE {$this->db_prefix}routes
            {$set_sql}
            WHERE route_id = :route_id
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     * Generic deletes a record based on the id provided.
     * @param int $route_id
     * @return array
     */
    public function delete($route_id = -1)
    {
        if ($route_id == -1) { return false; }
        $search_sql = "SELECT route_immutable FROM {$this->db_prefix}routes WHERE route_id = :route_id";
        $search_results = $this->o_db->search($search_sql, array(':route_id' => $route_id));
        if ($search_results[0]['route_immutable'] == 1) {
            return ['message' => 'Sorry, that route can not be deleted.', 'type' => 'failure'];
        }
        $sql = "
            DELETE FROM {$this->db_prefix}routes
            WHERE route_id = :route_id
        ";
        $results = $this->o_db->delete($sql, array(':route_id' => $route_id), true);
        $this->logIt(var_export($results, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($results) {
            $a_results = [
                'message' => 'Success!',
                'type'    => 'success'
            ];
        }
        else {
            $message = $this->o_db->getSqlErrorMessage();
            $a_results = [
                'message' => $message,
                'type'    => 'failure'
            ];
        }
        return $a_results;
    }

    /**
     * Implements the ModelInterface method, getErrorMessage.
     * return string
     */
    public function getErrorMessage()
    {
        return $this->o_db->getSqlErrorMessage();
    }
}
