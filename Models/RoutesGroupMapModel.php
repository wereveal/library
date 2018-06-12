<?php
/**
 * Class RoutesGroupMapModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the Model expected operations, database CRUD and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2018-06-12 08:10:59
 * @change_log
 * - v2.0.0         - Refactored to extend ModelAbstract        - 2018-06-12 wer
 * - v1.0.0         - Initial production version                - 2017-12-12 wer
 * - v1.0.0-alpha.4 - Refactored to use ModelException          - 2017-06-18 wer
 * - v1.0.0-alpha.3 - DbUtilityTraits change reflected here     - 2017-05-09 wer
 * - v1.0.0-alpha.0 - Initial version                           - 08/01/2015 wer
 */
class RoutesGroupMapModel extends ModelAbstract
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

    ### Basic CRUD commands, overrides Abtract ###
    /**
     * Returns all records in the table.
     * Legacy method.
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readAll()
    {
        return $this->read();
    }

    /**
     * Updates the record, NOT! Well, sort of.
     * Method is required by interface.
     *     Update should never happen!
     *     Reasoning. The group_id and route_id form a unique index. As such
     *     they should not be modified. The record should always be deleted and
     *     a new one added. That is what this function actually does.
     *
     * @param array  $a_values   Required ['rgm_id, group_id, route_id] all three
     * @param string $not_used   As named, not used. required by abstract.
     * @param array  $a_not_used As named, not used. required by abstract.
     * @return bool
     * @throws ModelException
     */
    public function update(array $a_values = array(), $not_used = '', array $a_not_used = [])
    {
        $a_required_keys = array(
            'rgm_id',
            'group_id',
            'route_id'
        );
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
                $this->delete($a_delete_ids);
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
        else {
            $this->error_message = $error_message;
            throw new ModelException($error_message, $error_code);
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
