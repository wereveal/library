<?php
/**
 * @brief     Handles all the CRUD for the urls table.
 * @ingroup   ritc_models
 * @file      UrlsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-beta.1
 * @date      2017-01-27 09:07:46
 * @note Change Log
 * - v1.0.0-beta.1  - Bug fix caused by changes elsewhere   - 2017-01-27 wer
 * - v1.0.0-beta.0  - Initial working version               - 2016-04-13 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2016-04-10 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class UrlsModel.
 * @class   UrlsModel
 * @package Ritc\Library\Models
 */
class UrlsModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'urls', 'lib');
    }

    /**
     * Create a record using the values provided.
     * @param array $a_values
     * @return bool|int
     */
    public function create(array $a_values = [])
    {
        $meth = __METHOD__ . '.';
        $a_required_keys = [
            'url_text'
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
        $log_message = 'Values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        return $this->genericCreate($a_values, $a_params);
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_for    key pairs of field name => field value
     * @param array $a_search_params \ref searchparams \ref readparams
     * @return array
     */
    public function read(array $a_search_for = [], array $a_search_params = [])
    {
        $meth = __METHOD__ . '.';
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_for,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'url_text ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        $log_message = 'Parameters ' . var_export($a_parameters, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        return $this->genericRead($a_parameters);
    }

    /**
     * Update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values = [])
    {
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
     * Checks to see if there are any other tables with relations.
     * @param int $id
     * @return bool
     */
    public function delete($id = -1)
    {
        if ($id == -1) { return false; }
        $a_search_for = [$this->primary_index_name => $id];
        $a_search_params = [
            'order_by' => 'url_id',
            'a_fields' => ['url_immutable']
        ];
        $search_results = $this->read($a_search_for, $a_search_params);
        // error_log(var_export($search_results, true));
        if (isset($search_results[0]) && $search_results[0]['url_immutable'] == 1) {
            $this->error_message = 'Sorry, that url can not be deleted.';
            return false;
        }
        $a_search_for = ['url_id' => $id];
        $o_routes = new RoutesModel($this->o_db);
        $search_results = $o_routes->read($a_search_for);
        if (isset($search_results[0])) {
            $this->error_message = 'Please change/delete the route that refers to this url first.';
            return false;
        }
        $o_pages  = new PageModel($this->o_db);
        $search_results = $o_pages->read($a_search_for);
        if (isset($search_results[0])) {
            $this->error_message = 'Please change/delete the Page that refers to this url first.';
            return false;
        }
        $o_nav    = new NavigationModel($this->o_db);
        $search_results = $o_nav->read($a_search_for);
        if (isset($search_results[0])) {
            $this->error_message = 'Please change/delete the Navigation record that refers to this url first.';
            return false;
        }
        $results = $this->genericDelete($id);
        if ($results === false) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
        }
        return $results;
    }
}
