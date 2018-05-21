<?php
/**
 * Class RoutesModel
 * @package RITC_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the Model expected operations, database CRUD and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.1
 * @date    2017-12-12 11:37:32
 * ## Change Log
 * - v2.0.1   - ModelException changes reflected here                   - 2017-12-12 wer
 * - v2.0.0   - Refactored to use ModelException and moved a couple     - 2017-06-18 wer
 *              methods to RoutesComplexModel.
 * - v1.5.0   - DbUtilityTraits change reflected here                   - 2017-05-09 wer
 * - v1.4.0   - Refactored readWithRequestUri to readByRequestUri       - 2016-04-10 wer
 *              Added readWithUrl to return list of routes with url.
 * - v1.3.0   - updated to use more of the DbUtilityTraits              - 2016-04-01 wer
 * - v1.2.0   - Database structure change reflected here.               - 2016-03-11 wer
 *              Required new method to duplicate old functionality.
 * - v1.1.0   - refactoring to provide better postgresql compatibility  - 11/22/2015 wer
 * - v1.0.2   - Database structure change reflected here.               - 09/03/2015 wer
 * - v1.0.1   - Refactoring elsewhere necessitated changes here         - 07/31/2015 wer
 * - v1.0.0   - first working version                                   - 01/28/2015 wer
 * - v1.0.0β2 - Changed to match some namespace changes, and bug fix    - 11/15/2014 wer
 * - v1.0.0β1 - First live version                                      - 11/11/2014 wer
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
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values)
    {
        if ($a_values == array()) {
            throw new ModelException('Missing required values.', 120);
        }
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
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'route_path']
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_values,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => $this->primary_index_name . ' ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Update a record using the values provided.
     * @param array $a_values Required.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values = [])
    {
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes a record based on the id provided.
     * @param int|array $route_id required may be a single id or list array of ids.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($route_id = -1)
    {
        if ($route_id == -1 || empty($route_id)) {
            throw new ModelException('Missing required value(s), route id', 420);
        }
        if (Arrays::isArrayOfAssocArrays($route_id)) {
            $a_ids = [];
            foreach ($route_id as $id) {
                $a_ids[] =  [$this->primary_index_name => $id];
            }
        }
        else {
            $a_ids[] =  [$this->primary_index_name => $route_id];
        }
        try {
            $search_results = $this->read($a_ids, ['a_fields' => ['route_immutable']]);
            foreach ($search_results as $a_result) {
                if (isset($a_result['route_immutable']) && $a_result['route_immutable'] == 'true') {
                    $this->error_message = 'Sorry, that route can not be deleted.';
                    throw new ModelException($this->error_message, 450);
                }
            }
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to determine if the record(s) is immutable.', 445);
        }
        try {
            return $this->genericDelete($route_id);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, 410);
        }
    }
}
