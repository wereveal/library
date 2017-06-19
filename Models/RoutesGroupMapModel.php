<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/RoutesGroupMapModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.4
 * @date      2017-06-18 14:46:29
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.4 - Refactored to use ModelException                              - 2017-06-18 wer
 * - v1.0.0-alpha.3 - DbUtilityTraits change reflected here                         - 2017-05-09 wer
 * - v1.0.0-alpha.2 - Bug fix                                                       - 2017-01-27 wer
 * - v1.0.0-alpha.1 - Bug Fix to read method to match interface                     - 2016-03-24 wer
 * - v1.0.0-alpha.0 - Initial version                                               - 08/01/2015 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
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
     * @return bool|int
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = array())
    {
        $a_required_keys = [
            'route_id',
            'group_id'
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
            throw new ModelException('Could not create new record(s)', 100);
        }
    }

    /**
     * @param array $a_search_values     ['rgm_id', 'group_id', 'route_id']
     * @param array $a_search_parameters \ref searchparams \ref readparams
     * @return mixed
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function read(array $a_search_values = [], array $a_search_parameters = [])
    {
        $a_search_parameters['a_search_for'] = $a_search_values;
        $a_search_parameters['table_name']   = $this->db_table;
        if (!isset($a_search_parameters['order_by'])) {
            $a_search_parameters['order_by'] = 'group_id ASC, route_id ASC';
        }
        if (!isset($a_search_parameters['a_allowed_keys'])) {
            $a_search_parameters['a_allowed_keys'] = $this->a_db_fields;
        }
        try {
            return $this->genericRead($a_search_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Returns all records in the table.
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readAll()
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => [],
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'group_id ASC, route_id ASC'
        ];
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
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
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values = array())
    {
        $a_required_keys = array(
            'rgm_id',
            'group_id',
            'route_id'
        );
        $a_delete_ids = [];
        $create_values = [];
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $a_record) {
                $a_record = Arrays::removeUndesiredPairs($a_record, $a_required_keys);
                if (!Arrays::hasRequiredKeys($a_record, $a_required_keys)) {
                    throw new ModelException('Missing required values.', 320);
                }
                $a_delete_ids[] = $a_record['rgm_id'];
                $create_values[] = [
                    'group_id' => $a_record['group_id'],
                    'route_id' => $a_record['route_id']
                ];
            }
        }
        else {
            $a_values = Arrays::removeUndesiredPairs($a_values, $a_required_keys);
            if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
                throw new ModelException('Missing required values.', 320);
            }
            $a_delete_ids = [$a_values['rgm_id']];
            $create_values = [
                'group_id' => $a_values['group_id'],
                'route_id' => $a_values['route_id']
            ];
        }
        try {
            $this->o_db->startTransaction();
            try {
                $this->delete($a_delete_ids);
                try {
                    $this->create($create_values);
                    try {
                        $this->o_db->commitTransaction();
                        return true;
                    }
                    catch (ModelException $e) {
                        $this->error_message = $e->errorMessage();
                        $error_code = $e->getCode();
                    }
                }
                catch (ModelException $e) {
                    $this->error_message = 'Unable to save the new map record.';
                    $error_code = $e->getCode();
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to delete the old map record.';
                $error_code = $e->getCode();
            }
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            $error_code = $e->getCode();
        }
        $this->error_message .= $this->o_db->getSqlErrorMessage();
        throw new ModelException($this->error_message, $error_code);
    }

    /**
     * Deletes a record by rgm_id.
     * @param string|array $rgm_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($rgm_id = '')
    {
        if (empty($rgm_id)) {
            throw new ModelException('Missing required values.', 420);
        }
        try {
            return $this->genericDelete($rgm_id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes record(s) by Route ID.
     * @param int $route_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function deleteByRouteId($route_id = -1)
    {
        if ($route_id == -1) {
            throw new ModelException('Missing required value for route_id.', 420);
        }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE route_id = :route_id
        ";
        try {
            return $this->o_db->delete($sql, ['route_id' => $route_id], true);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes record(s) by Group ID.
     * @param int $group_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function deleteByGroupId($group_id = -1)
    {
        if ($group_id == -1) {
            throw new ModelException('Missing required value for route_id.', 420);
        }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE group_id = :group_id
        ";
        try {
            return $this->o_db->delete($sql, [':group_id' => $group_id], true);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

}
