<?php
/**
 * Class PeopleGroupMapModel
 *
 * @package Ritc_Library
 */

namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;

/**
 * Does all the Model expected operations, database CRUD and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 3.0.0
 * @date    2021-11-30 14:58:07
 * @change_log
 * - v3.0.0   - Updated to use php 8 only                               - 2021-11-30 wer
 * - v2.1.0   - Refactored to extend ModelAbstract                      - 2018-06-15 wer
 * - v2.0.0   - Refactored to use ModelException                        - 2017-06-17 wer
 * - v1.1.3   - DbUtilityTraits change reflected here                   - 2017-05-09 wer
 * - v1.1.2   - refactoring of trait reflected here                     - 2017-01-27 wer
 * - v1.1.0   - Refactoring of DbModel reflected here                   - 2016-03-18 wer
 * - v1.0.0   - take out of beta                                        - 11/27/2015 wer
 * - v1.0.0β7 - refactoring fix for postgres compatibility              - 11/22/2015 wer
 * - v1.0.0β6 - removed abstract Base, implemented LogitTraits          - 09/03/2015 wer
 * - v1.0.0β4 - refactored user to people                               - 01/26/2015 wer
 * - v1.0.0β3 - extends the Base class, injects the DbModel, clean up   - 09/23/2014 wer
 * - v1.0.0β2 - First Live version                                      - 09/15/2014 wer
 * - v1.0.0β1 - Initial version                                         - 01/18/2014 wer
 */
class PeopleGroupMapModel extends ModelAbstract
{
    /**
     * PeopleGroupMapModel constructor.
     *
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'people_group_map');
        $this->setRequiredKeys(['people_id', 'group_id']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    ### Overrides Abstract Methods ###
    /**
     * Updates the record, NOT!
     * Method is required by interface.
     * Update is not allowed! Always return false.
     *     Reasoning. The group_id and people_id form a unique index. As such,
     *     they should not be modified. The record should always be deleted and
     *     a new one added.
     *
     * @param array $a_values
     * @param array $a_immutable
     * @return void
     * @throws ModelException
     * @todo Change to do the delete create workflow
     * @noinspection PhpHierarchyChecksInspection
     */
    public function update(array $a_values = [], array $a_immutable = []):void
    {
        throw new ModelException('Operation not permitted', 350);
    }

    ### Specialized Methods ###

    /**
     * Deletes the record(s) in table based on group id(s).
     *
     * @param int|array $group_id either '1' or ['1', '2', '3']
     * @return bool
     * @throws ModelException
     */
    public function deleteByGroupId(int|array $group_id = -1):bool
    {
        $sql      = "
            DELETE FROM {$this->db_table}
            WHERE group_id = :group_id
        ";
        $a_values = array();
        if (is_array($group_id)) {
            foreach ($group_id as $id) {
                $a_values[] = [':group_id' => $id];
            }
        }
        else {
            $a_values = [':group_id' => $group_id];
        }
        try {
            return $this->o_db->delete($sql, $a_values, false);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to delete the pgm record(s) by group id.', 300, $e);
        }
    }

    /**
     * Deletes the records based on people id.
     *
     * @param int|array $people_id required either '1' or ['1', '2', '3']
     * @return bool
     * @throws ModelException
     */
    public function deleteByPeopleId(int|array $people_id = -1):bool
    {
        $sql      = "
            DELETE FROM {$this->db_table}
            WHERE people_id = :people_id
        ";
        $a_values = [];
        if (is_array($people_id)) {
            foreach ($people_id as $id) {
                $a_values[] = [':people_id' => $id];
            }
        }
        else {
            $a_values = [':people_id' => $people_id];
        }
        try {
            return $this->o_db->delete($sql, $a_values, false);
        }
        catch (ModelException $e) {
            throw new ModelException('Could not delete the pgm record(s) by people id.', 410, $e);
        }
    }

    /**
     * Reads the record(s) by group id.
     *
     * @param int $group_id
     * @return array
     * @throws ModelException
     */
    public function readByGroupId(int $group_id = -1): array
    {
        if ($group_id === -1) {
            throw new ModelException('Missing required value.', 220);
        }
        $a_search_for = ['group_id' => $group_id];
        try {
            return $this->read($a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to read the records by group id.', 200, $e);
        }
    }

    /**
     * Reads the record(s) by people id.
     *
     * @param int $people_id
     * @return array
     * @throws ModelException
     */
    public function readByPeopleId(int $people_id = -1): array
    {
        if ($people_id === -1) {
            throw new ModelException('Missing required value.', 220);
        }
        $a_search_for = ['people_id' => $people_id];
        try {
            return $this->read($a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to read the records by people id.', 200, $e);
        }
    }
}
