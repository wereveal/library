<?php
/**
 * @brief     Does all the database CRUD stuff for the navigation groups.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/NavgroupsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.1+2
 * @date      2016-04-01 07:39:07
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.1 - Updated to use DbUtilityTraits               - 2016-03-31 wer
 * - v1.0.0-alpha.0 - Initial version                              - 2016-02-25 wer
 */
namespace Ritc\Library\Models;

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

    /** @var string */
    private $a_field_names;

    /**
     * NavgroupsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'navgroups');
        $this->setFieldNames();
    }

    /**
     * Generic create record(s) using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function create(array $a_values)
    {
        $error_message = '';
        if ($a_values == []) {
            $error_message .= "Values must be there to create a record\n";
        }
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                if ($this->hasRecords(['ng_name' => $a_record['ng_name']])) {
                    $error_message .= "The record already exists for {$a_record['ng_name']}";
                }
            }
        }
        else {
            if ($this->hasRecords(['ng_name' => $a_values['ng_name']])) {
                $error_message .= "The record already exists for {$a_values['ng_name']}";
            }
        }

        $a_resulting_ids = [];
        if ($error_message == '') {
            $a_parameters = [
                'a_required_keys' => ['ng_name'],
                'a_field_names'   => $this->a_db_fields,
                'a_psql'          => [
                    'table_name'  => $this->db_table,
                    'column_name' => $this->primary_index_name
                ]
            ];
            $a_resulting_ids = $this->genericCreate($a_values, $a_parameters);
            if ($a_resulting_ids === false) {
                $error_message .= "The navigation group record could not be saved.";
            }
        }
        if ($error_message == '') {
            return $a_resulting_ids;
        }
        else {
            $this->error_message .= "\n" . $error_message;
            return false;
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'ng_name ASC']
     * @return array
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        $meth = __METHOD__ . '.';
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
        $results = $this->genericRead($a_parameters);
        $log_message = 'Return Values ' . var_export($results, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        return $results;
    }

    /**
     * Generic update for a record using the values provided.
     * Only the name and active setting may be changed.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values = [])
    {
        if (!isset($a_values['ng_id'])
            || $a_values['ng_id'] == ''
            || (is_string($a_values['ng_id']) && !is_numeric($a_values['ng_id']))
        ) {
            $this->error_message = 'The Navgroup id was not supplied.';
            return false;
        }
        return $this->genericUpdate($a_values);
    }

    /**
     * Generic deletes a record based on the id provided.
     * Also delete the relation record(s) in the map table.
     * @param int $ng_id
     * @return bool
     */
    public function delete($ng_id = -1)
    {
        if ($ng_id == -1) { return false; }
        if ($this->o_db->startTransaction()) {
            $o_map = new NavNgMapModel($this->o_db);
            $results = $o_map->delete($ng_id);
            if (!$results) {
                $this->error_message = $o_map->getErrorMessage();
                $this->o_db->rollbackTransaction();
                return false;
            }
            else {
                $results = $this->genericDelete($ng_id);
                $this->logIt(var_export($results, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                if ($results) {
                    return $this->o_db->commitTransaction();
                }
                else {
                    $this->error_message = $this->o_db->getSqlErrorMessage();
                    $this->o_db->rollbackTransaction();
                    return false;
                }
            }
        }
        else {
            $this->error_message = "Could not start transaction.";
            return false;
        }
    }

    /**
     * Returns the whole record base on name.
     * @param string $name
     * @return array
     */
    public function readByName($name = '')
    {
        $a_search_values = ['ng_name' => $name];
        return $this->read($a_search_values);
    }

    /**
     * Returns the navgroup id based on navgroup name.
     * @param string $name
     * @return mixed
     */
    public function readNavgroupId($name = '')
    {
        $sql = "SELECT ng_id FROM {$this->db_table} WHERE ng_name = :ng_name";
        $a_values = [':ng_name' => $name];
        $results = $this->o_db->search($sql, $a_values);
        if ($results) {
            return $results[0]['ng_id'];
        }
        else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getFieldNames()
    {
        return $this->a_field_names;
    }

    /**
     * @param array $a_field_names
     */
    public function setFieldNames(array $a_field_names = [])
    {
        if (count($a_field_names) > 0) {
            $this->a_field_names = $a_field_names;
        }
        else {
            $this->a_field_names = [
                'ng_id',
                'ng_name',
                'ng_class',
                'ng_active'
            ];
        }
    }

}
