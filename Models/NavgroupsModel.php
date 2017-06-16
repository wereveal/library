<?php
/**
 * @brief     Does all the database CRUD stuff for the navigation groups.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/NavgroupsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.4
 * @date      2017-06-15 16:04:24
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.4 - Refactored to use DbException                 - 2017-06-15 wer
 * - v1.0.0-alpha.3 - Refactoring of DbUtilityTraits reflected here - 2017-01-27 wer
 * - v1.0.0-alpha.2 - Added two methods to get default ng           - 2016-04-18 wer
 * - v1.0.0-alpha.1 - Updated to use DbUtilityTraits                - 2016-03-31 wer
 * - v1.0.0-alpha.0 - Initial version                               - 2016-02-25 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\DbException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NavgroupsModel.
 * @class   NavgroupsModel
 * @package Ritc\Library\Models
 */
class NavgroupsModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * NavgroupsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'navgroups');
    }

    /**
     * Generic create record(s) using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function create(array $a_values = [])
    {
        if ($a_values == []) {
            throw new DbException('Values must be there to create a record', 130);
        }
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                if (empty($a_record['ng_name'])) {
                    throw new DbException('The navgroup requires a name.', 130);
                }
                else {
                    if ($this->hasRecords(['ng_name' => $a_record['ng_name']])) {
                        throw new DbException("The record already exists for {$a_record['ng_name']}", 110);
                    }
                }
            }
        }
        else {
            if (empty($a_values['ng_name'])) {
                throw new DbException('The navgroup requires a name.', 130);
            }
            else {
                if ($this->hasRecords(['ng_name' => $a_values['ng_name']])) {
                    throw new DbException("The record already exists for {$a_values['ng_name']}", 110);
                }
            }
        }

        $a_parameters = [
            'a_required_keys' => ['ng_name'],
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => [
                'table_name'  => $this->db_table,
                'column_name' => $this->primary_index_name
            ]
        ];
        try {
            $a_resulting_ids = $this->genericCreate($a_values, $a_parameters);
        }
        catch (DbException $exception) {
            $message = $exception->errorMessage();
            throw new DbException($message, $exception->getCode());
        }
        return $a_resulting_ids;
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'ng_name ASC']
     * @return array
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        $a_parameters = [
            'table_name' => $this->db_table,
            'a_search_for' => $a_search_values
        ];
        if ($a_search_params == [] || !isset($a_search_params['order_by'])) {
            $a_parameters['order_by'] = 'ng_name ASC';
        }
        else {
            $a_parameters = array_merge($a_parameters, $a_search_params);
        }
        try {
            $results = $this->genericRead($a_parameters);
        }
        catch (DbException $e) {
            $this->error_message = 'Unable to read the record.';
            throw new DbException($this->error_message, 200, $e);
        }
        return $results;
    }

    /**
     * Generic update for a record using the values provided.
     * Only the name and active setting may be changed.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function update(array $a_values = [])
    {
        if (!isset($a_values['ng_id'])
            || $a_values['ng_id'] == ''
            || (is_string($a_values['ng_id']) && !is_numeric($a_values['ng_id']))
        ) {
            $this->error_message = 'The Navgroup id was not supplied.';
            throw new DbException($this->error_message, 320);
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (DbException $e) {
            $this->error_message = 'Unable to update the record.';
            throw new DbException($this->error_message, 300, $e);
        }
    }

    /**
     * Generic deletes a record based on the id provided.
     * Checks to see if a map record exists, if so, returns false;
     * @param int $ng_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function delete($ng_id = -1)
    {
        if ($ng_id == -1) {
            throw new DbException('The navgroup id was not provided');
        }
        $o_map = new NavNgMapModel($this->o_db);
        $results = $o_map->read(['ng_id' => $ng_id]);
        if (!empty($results)) {
            $this->error_message = 'The nav_ng_map record(s) must be deleted first.';
            throw new DbException($this->error_message, 430);
        }
        try {
            $results = $this->retrieveDefaultId();
        }
        catch (DbException $e) {
            $this->error_message = 'Unable to retrieve the default record.';
            throw new DbException($this->error_message, 200, $e);
        }
        if ($results == $ng_id) {
            $this->error_message = 'This is the default navgroup. Change a different record to be default and try again.';
            throw new DbException($this->error_message, 440);
        }
        try {
            $this->genericDelete($ng_id);
        }
        catch (DbException $e) {
            $message = $this->o_db->getSqlErrorMessage();
            throw new DbException($message, 400, $e);
        }
        return true;
    }

    /**
     * Returns a record based on the record id.
     * @param int $id
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function readById($id = -1)
    {
        if ($id < 1) {
            throw new DbException('A record id must be provided.', 220);
        }
        $a_search_values = ['ng_id' => $id];
        try {
            return $this->read($a_search_values);
        }
        catch (DbException $e) {
            throw new DbException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the whole record base on name.
     * @param string $name
     * @return array
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function readByName($name = '')
    {
        if ($name == '') {
            throw new DbException('A record name must be provided.', 220);
        }
        $a_search_values = ['ng_name' => $name];
        try {
            return $this->read($a_search_values);
        }
        catch (DbException $e) {
            throw new DbException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the navgroup id based on navgroup name.
     * @param string $name
     * @return int
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function readIdByName($name = '')
    {
        $a_values = [':ng_name' => $name];
        $a_search_parms = [
            'order_by' => 'ng_id',
            'a_fields' => ['ng_id']
        ];
        try {
            $results = $this->read($a_values, $a_search_parms);
            if (!empty($results[0])) {
                return $results[0]['ng_id'];
            }
            else {
                throw new DbException('No record found', 210);
            }
        }
        catch (DbException $e) {
            throw new DbException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Gets the default navgroup by id.
     * @return int
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function retrieveDefaultId()
    {
        $a_search_for = [':ng_default' => 1];
        $a_search_parms = [
            'order_by' => 'ng_id',
            'a_fields' => ['ng_id']
        ];
        try {
            $results = $this->read($a_search_for, $a_search_parms);
            if (!empty($results[0])) {
                return $results[0]['ng_id'];
            }
            else {
                throw new DbException('No record found', 210);
            }
        }
        catch (DbException $e) {
            throw new DbException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Returns the default navgroup by name.
     * @return string
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function retrieveDefaultName()
    {
        $a_search_for = [':ng_default' => 1];
        $a_search_parms = [
            'order_by' => 'ng_name',
            'a_fields' => ['ng_name']
        ];
        try {
            $results = $this->read($a_search_for, $a_search_parms);
            if (!empty($results[0])) {
                return $results[0]['ng_name'];
            }
            else {
                throw new DbException('No record found', 210);
            }
        }
        catch (DbException $e) {
            throw new DbException($e->errorMessage(), $e->getCode(), $e);
        }
    }

}
