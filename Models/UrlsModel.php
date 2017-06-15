<?php
/**
 * @brief     Handles all the CRUD for the urls table.
 * @ingroup   ritc_models
 * @file      UrlsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2017-06-03 17:18:29
 * @note Change Log
 * - v1.0.0         - Out of beta                           - 2017-06-03 wer
 * - v1.0.0-beta.2  - Refactoring from DbUtilityTraits      - 2017-05-09 wer
 * - v1.0.0-beta.1  - Bug fix caused by changes elsewhere   - 2017-01-27 wer
 * - v1.0.0-beta.0  - Initial working version               - 2016-04-13 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2016-04-10 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\DbException;
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

    /**
     * UrlsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'urls');
    }

    /**
     * Create a record using the values provided.
     * @param array $a_values
     * @return int
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function create(array $a_values = [])
    {
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
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (DbException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new DbException($message, $code);
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_for    key pairs of field name => field value
     * @param array $a_search_params \ref searchparams \ref readparams
     * @return array
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function read(array $a_search_for = [], array $a_search_params = [])
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_for,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'url_text ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (DbException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new DbException($message, $code);
        }
    }

    /**
     * Update for a record using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function update(array $a_values = [])
    {
        if (!isset($a_values[$this->primary_index_name])
            || $a_values[$this->primary_index_name] == ''
            || (!is_numeric($a_values[$this->primary_index_name]))
        ) {
            throw new DbException('Missing required values.', 320);
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (DbException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new DbException($message, $code);
        }
    }

    /**
     * Deletes a record based on the id provided.
     * Checks to see if there are any other tables with relations.
     * @param int $id
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function delete($id = -1)
    {
        if ($id == -1) {
            throw new DbException('Missing the id of the record to delete.', 420);
        }
        $a_search_for = [$this->primary_index_name => $id];
        $a_search_params = [
            'order_by' => 'url_id',
            'a_fields' => ['url_immutable']
        ];
        try {
            $search_results = $this->read($a_search_for, $a_search_params);
        }
        catch (DbException $e) {
            $message = 'Can not determine if the record is deletable.';
            throw new DbException($message, 420);
        }
        if (isset($search_results[0]) && $search_results[0]['url_immutable'] == 1) {
            $this->error_message = 'Sorry, that url can not be deleted.';
            throw new DbException($this->error_message, 440);
        }
        $a_search_for = ['url_id' => $id];
        try {
            $o_routes = new RoutesModel($this->o_db);
            try {
                $search_results = $o_routes->read($a_search_for);
                if (isset($search_results[0])) {
                    $this->error_message = 'Please change/delete the route that refers to this url first.';
                    throw new DbException($this->error_message, 440);
                }
            }
            catch (DbException $e) {
                $this->error_message = 'Please change/delete the route that refers to this url first.';
                throw new DbException($this->error_message, 440);
            }
        }
        catch (DbException $e) {
            $message = $e->errorMessage();
            throw new DbException($message, 400);
        }
        try {
            $o_pages = new PageModel($this->o_db);
            try {
                $search_results = $o_pages->read($a_search_for);
                if (isset($search_results[0])) {
                    $this->error_message = 'Please change/delete the Page that refers to this url first.';
                    throw new DbException($this->error_message, 440);
                }
            }
            catch (DbException $e) {
                $message = $e->errorMessage();
                throw new DbException($message, 400);
            }
        }
        catch (DbException $e) {
            $message = $e->errorMessage();
            throw new DbException($message, 400);
        }
        try {
            $o_nav = new NavigationModel($this->o_db);
            try {
                $search_results = $o_nav->read($a_search_for);
                if (isset($search_results[0])) {
                    $this->error_message = 'Please change/delete the Navigation record that refers to this url first.';
                    throw new DbException($this->error_message, 440);
                }
            }
            catch (DbException $e) {
                $message = $e->errorMessage();
                throw new DbException($message, 400);
            }
        }
        catch (DbException $e) {
            $message = $e->errorMessage();
            throw new DbException($message, 400);
        }
        try {
            $results = $this->genericDelete($id);
            return $results;
        }
        catch (DbException $e) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            throw new DbException($this->error_message, 400);
        }
    }
}
