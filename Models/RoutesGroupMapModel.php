<?php
/**
 * Class RoutesGroupMapModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Does all the Model expected operations, database CRUD and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 3.0.0
 * @date    2021-11-30 15:10:49
 * @change_log
 * - v3.0.0         - Updated for php8 only                     - 2021-11-30 wer
 * - v2.0.0         - Refactored to extend ModelAbstract        - 2018-06-12 wer
 * - v1.0.0         - Initial production version                - 2017-12-12 wer
 * - v1.0.0-alpha.4 - Refactored to use ModelException          - 2017-06-18 wer
 * - v1.0.0-alpha.3 - DbUtilityTraits change reflected here     - 2017-05-09 wer
 * - v1.0.0-alpha.0 - Initial version                           - 08/01/2015 wer
 */
class RoutesGroupMapModel extends ModelAbstract
{
    /**
     * RoutesGroupMapModel constructor.
     *
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'routes_group_map');
        $this->setRequiredKeys(['route_id', 'group_id']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    ### Basic CRUD commands, overrides Abtract ###
    /**
     * Updates the record, NOT! Well, sort of.
     * Method overrides the ModelAbstract definition.
     *   Update should never happen!
     *   Reasoning. The group_id and route_id form a unique index. As such
     *   they should not be modified. The record should always be deleted and
     *   a new one added. That is what this function actually does.
     *
     * @param array  $a_values   Required ['rgm_id, group_id, route_id] all three
     * @param array  $a_immutable not used. required by abstract.
     * @return bool
     * @throws ModelException
     */
    public function update(array $a_values = [], array $a_immutable = []):bool
    {
        $a_required_keys = [
            'rgm_id',
            'group_id',
            'route_id'
        ];
        $a_delete_ids = [];
        $create_values = [];
        $error_code = 0;
        $error_message = '';
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
                foreach ($a_delete_ids as $delete_id) {
                    $this->delete($delete_id);
                }
                try {
                    $this->create($create_values);
                    try {
                        $this->o_db->commitTransaction();
                    }
                    catch (ModelException $e) {
                        $error_message = $e->errorMessage();
                        $error_code = $e->getCode();
                    }
                }
                catch (ModelException $e) {
                    $error_message = 'Unable to save the new map record.';
                    $error_code = $e->getCode();
                }
            }
            catch (ModelException $e) {
                $error_message = 'Unable to delete the old map record.';
                $error_code = $e->getCode();
            }
        }
        catch (ModelException $e) {
            $error_message = $e->errorMessage();
            $error_code = $e->getCode();
        }
        if (empty($error_message)) {
            $this->error_message = '';
            return true;
        }

        $this->error_message = $error_message;
        throw new ModelException($error_message, $error_code);
    }

    ### Additional methods ###
    /**
     * Returns all records in the table.
     * Legacy method.
     *
     * @return array|bool
     * @throws ModelException
     */
    public function readAll(): bool|array
    {
        return $this->read();
    }

    /**
     * Returns the records which have the route id.
     *
     * @param int $route_id
     * @return array
     * @throws ModelException
     */
    public function readByRouteId(int $route_id = -1): array
    {
        $a_search_for = ['route_id' => $route_id];
        if ($route_id < 1) {
            $a_search_for = [];
        }
        try {
            return $this->read($a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes record(s) by Route ID.
     *
     * @param int $route_id Required
     * @return bool
     * @throws ModelException
     */
    public function deleteByRouteId(int $route_id = -1): bool
    {
        if ($route_id === -1) {
            throw new ModelException('Missing required value for route_id.', 420);
        }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE route_id = :route_id
        ";
        try {
            return $this->o_db->delete($sql, ['route_id' => $route_id]);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes record(s) by Group ID.
     *
     * @param int $group_id Required
     * @return bool
     * @throws ModelException
     */
    public function deleteByGroupId(int $group_id = -1): bool
    {
        if ($group_id === -1) {
            throw new ModelException('Missing required value for route_id.', 420);
        }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE group_id = :group_id
        ";
        try {
            return $this->o_db->delete($sql, [':group_id' => $group_id]);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes a record based on the values.
     *
     * @param int $route_id Required
     * @param int $group_id Required
     * @return bool
     * @throws ModelException
     */
    public function deleteByRouteGroup(int $route_id = -1, int $group_id = -1): bool
    {
        if ($route_id < 1 || $group_id < 1) {
            $err_code = ExceptionHelper::getCodeNumberModel('delete missing value');
            $msg = 'Missing';
            $msg .= $route_id < 1 ? ' route id' : '';
            $msg .= $group_id < 1 ? ' group id' : '';
            throw new ModelException($msg, $err_code);
        }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE group_id = :group_id
            AND route_id = :route_id
        ";
        try {
            $a_values = [
                ':group_id' => $group_id,
                ':route_id' => $route_id
            ];
            return $this->o_db->delete($sql, $a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }
}
