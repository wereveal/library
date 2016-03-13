<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/RoutesModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.2.0
 * @date      2016-03-11 10:49:34
 * @note <b>Change Log</b>
 * - v1.2.0   - Database structure change reflected here.              - 2016-03-11 wer
 *              Required new method to duplicate old functionality.
 * - v1.1.0   - refactoring to provide better postgresql compatibility - 11/22/2015 wer
 * - v1.0.2   - Database structure change reflected here.              - 09/03/2015 wer
 * - v1.0.1   - Refactoring elsewhere necessitated changes here        - 07/31/2015 wer
 * - v1.0.0   - first working version                                  - 01/28/2015 wer
 * - v1.0.0β2 - Changed to match some namespace changes, and bug fix   - 11/15/2014 wer
 * - v1.0.0β1 - First live version                                     - 11/11/2014 wer
 * @todo Ritc/Library/Models/RoutesModel.php - this needs testing and a lot of code elsewhere has to be changed.
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class RoutesModel.
 * @class   RoutesModel
 * @package Ritc\Library\Models
 */
class RoutesModel implements ModelInterface
{
    use LogitTraits;

    /** @var array */
    private $db_fields;
    /** @var string */
    private $db_prefix;
    /** @var string */
    private $db_table;
    /** @var string */
    private $db_type;
    /** @var \Ritc\Library\Services\DbModel */
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
        $this->db_table  = $this->db_prefix . "routes";
        $this->db_fields = $o_db->selectDbColumns($this->db_table);
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
            'url_id',
            'route_class',
            'route_method'
        ];
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            return false;
        }
        $insert = $this->o_db->buildSqlInsert($a_values, $this->db_fields);
        $sql = "
            INSERT INTO {$this->db_table}
                (
                  {$insert}
                )
        ";
        $a_table_info = [
            'table_name'  => "{$this->db_table}",
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
        $meth = __METHOD__ . '.';
        $select_me = $this->o_db->buildSqlSelectFields($this->db_fields);
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? ['order_by' => 'route_class']
                : $a_search_params;
            $a_allowed_keys = [
                'route_id',
                'url_id',
                'route_class'
            ];
            $log_message = 'Search Values ' . var_export($a_search_values, TRUE);
            $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params, $a_allowed_keys);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY route_class";
        }
        $sql = "
            SELECT {$select_me}
            FROM {$this->db_table}
            {$where}
        ";
        $this->logIt($sql, LOG_OFF, $meth . __LINE__);
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
        $a_allowed_keys = $this->db_fields;
        $a_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_values);
        $set_sql = $this->o_db->buildSqlSet($a_values, ['route_id']);
        $sql = "
            UPDATE {$this->db_table}
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
        $search_sql = "SELECT route_immutable FROM {$this->db_table} WHERE route_id = :route_id";
        $search_results = $this->o_db->search($search_sql, array(':route_id' => $route_id));
        if ($search_results[0]['route_immutable'] == 1) {
            return ['message' => 'Sorry, that route can not be deleted.', 'type' => 'failure'];
        }
        $sql = "
            DELETE FROM {$this->db_table}
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
     * Reads the route with the url.
     * @param array $a_search_values
     * @param array $a_search_params see \ref searchparams
     * @return mixed
     */
    public function readWithPath(array $a_search_values = array(), array $a_search_params = array())
    {
        $meth = __METHOD__ . '.';
        $select = '';
        foreach ($this->db_fields as $field) {
            $select .= $select == ''
                ? "r." . $field
                : ', r.' . $field;
        }
        $a_url_fields = $this->o_db->selectDbColumns($this->db_prefix . 'urls');
        foreach ($a_url_fields as $field) {
            $select .= ', u.' . $field;
        }

        $where = '';
        if (!isset($a_search_params['order_by'])) {
            $a_search_params['order_by'] = 'u.url_text';
        }
        $a_search_params['where_exists'] = true;

        if (count($a_search_values) > 0) {
            $a_allowed_keys = [
                'route_id'        => 'r.route_id',
                'url_id'          => 'u.url_id',
                'url_text'        => 'u.url_text',
                'url_type'        => 'u.url_type',
                'route_class'     => 'r.route_class',
                'route_immutable' => 'r.route_immutable'
            ];
            $log_message = 'Search Values ' . var_export($a_search_values, TRUE);
            $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
            $a_new_search_for = [];
            foreach ($a_search_values as $search_key => $search_value) {
                if (array_key_exists($search_key, $a_allowed_keys)) {
                    $a_new_search_for[$a_allowed_keys[$search_key]] = $search_value;
                }
            }
            $where .= $this->o_db->buildSqlWhere($a_new_search_for, $a_search_params);
        }
        else {
            $where .= $this->o_db->buildSqlWhere([], $a_search_params);
        }
        $sql =<<<EOT

SELECT {$select}
FROM {$this->db_table} as r, {$this->db_prefix}urls as u
WHERE r.url_id = u.url_id
{$where}

EOT;
        $this->logIt("sql: " . $sql, LOG_ON, $meth . __LINE__);
        return $this->o_db->search($sql, $a_search_values);
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
