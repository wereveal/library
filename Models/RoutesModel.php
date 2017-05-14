<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/RoutesModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.4.2
 * @date      2017-05-09 17:37:16
 * @note <b>Change Log</b>
 * - v1.4.2   - DbUtilityTraits change reflected here                  - 2017-05-09 wer
 * - v1.4.1   - Bug fix caused by change elsewhere                     - 2017-01-27 wer
 * - v1.4.0   - Refactored readWithRequestUri to readByRequestUri      - 2016-04-10 wer
 *              Added readWithUrl to return list of routes with url.
 * - v1.3.0   - updated to use more of the DbUtilityTraits             - 2016-04-01 wer
 * - v1.2.0   - Database structure change reflected here.              - 2016-03-11 wer
 *              Required new method to duplicate old functionality.
 * - v1.1.0   - refactoring to provide better postgresql compatibility - 11/22/2015 wer
 * - v1.0.2   - Database structure change reflected here.              - 09/03/2015 wer
 * - v1.0.1   - Refactoring elsewhere necessitated changes here        - 07/31/2015 wer
 * - v1.0.0   - first working version                                  - 01/28/2015 wer
 * - v1.0.0β2 - Changed to match some namespace changes, and bug fix   - 11/15/2014 wer
 * - v1.0.0β1 - First live version                                     - 11/11/2014 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class RoutesModel.
 * @class   RoutesModel
 * @package Ritc\Library\Models
 */
class RoutesModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * RoutesModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'routes');
    }

    /**
     * Create a record using the values provided.
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
        $a_psql = [
            'table_name'  => $this->db_table,
            'column_name' => $this->primary_index_name
        ];
        $a_params = [
            'a_required_keys' => $a_required_keys,
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => $a_psql
        ];
        return $this->genericCreate($a_values, $a_params);
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'route_path']
     * @return array
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_values,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => $this->primary_index_name . ' ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        return $this->genericRead($a_parameters);
    }

    /**
     * Update a record using the values provided.
     * @param array $a_values Required.
     * @return bool
     */
    public function update(array $a_values = [])
    {
        $meth = __METHOD__ . '.';
        $log_message = 'Values Passed In:  ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        if (!isset($a_values[$this->primary_index_name])
            || $a_values[$this->primary_index_name] == ''
            || (!is_numeric($a_values[$this->primary_index_name]))
        ) {

            return false;
        }
        return $this->genericUpdate($a_values);
    }

    /**
     * Deletes a record based on the id provided.
     * @param int $route_id
     * @return bool
     */
    public function delete($route_id = -1)
    {
        if ($route_id == -1) { return false; }
        $search_results = $this->read([$this->primary_index_name => $route_id], ['a_fields' => ['route_immutable']]);
        if (isset($search_results[0]) && $search_results[0]['route_immutable'] == 1) {
            $this->error_message = 'Sorry, that route can not be deleted.';
            return false;
        }
        $results = $this->genericDelete($route_id);
        if ($results === false) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
        }
        return $results;
    }

    /**
     * Reads the route with the request uri.
     * @param string $request_uri normally obtained from $_SERVER['REQUEST_URI']
     * @return mixed
     */
    public function readByRequestUri($request_uri = '')
    {
        $meth = __METHOD__ . '.';
        if ($request_uri == '') {
            return false;
        }
        $a_search_params = [':url_text' => $request_uri];
        $sql =<<<EOT

SELECT r.route_id, r.route_class, r.route_method, r.route_action, r.route_immutable,
       u.url_id, u.url_text, u.url_scheme
FROM {$this->db_prefix}routes as r, {$this->db_prefix}urls as u
WHERE r.url_id = u.url_id
AND u.url_text = :url_text
ORDER BY u.url_text

EOT;
        $this->logIt("sql: " . $sql, LOG_OFF, $meth . __LINE__);
        $log_message = 'values: ' . var_export($a_search_params, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        return $this->o_db->search($sql, $a_search_params);
    }

    /**
     * Returns the list of all the routes with the url.
     * A join between routes and urls tables.
     * @return bool|array
     */
    public function readAllWithUrl()
    {
        $sql =<<<EOT

SELECT r.route_id, r.route_class, r.route_method, r.route_action, r.route_immutable,
       u.url_id, u.url_text, u.url_scheme
FROM {$this->db_prefix}routes as r, {$this->db_prefix}urls as u
WHERE r.url_id = u.url_id
ORDER BY r.route_immutable DESC, u.url_text

EOT;
        return $this->o_db->search($sql);
    }

    /**
     * Returns the sql error message.
     * Overrides method in DbUtilityTraits.
     * return string
     */
    public function getErrorMessage()
    {
        return $this->o_db->retrieveFormatedSqlErrorMessage();
    }
}
