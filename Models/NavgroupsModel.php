<?php
/**
 * @brief     Does all the database CRUD stuff for the navigation groups.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/NavgroupsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-02-25 12:04:44
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.0 - Initial version                              - 02/25/2016 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NavgroupsModel.
 * @class   NavgroupsModel
 * @package Ritc\Library\Models
 */
class NavgroupsModel implements ModelInterface
{
    use LogitTraits;

    /** @var string */
    private $a_field_names;
    /** @var string */
    private $db_prefix;
    /** @var string */
    private $db_table;
    /** @var string */
    private $db_type;
    /** @var string */
    private $error_message;
    /** @var \Ritc\Library\Services\DbModel */
    private $o_db;

    /**
     * NavgroupsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
        $this->db_table  = $this->db_prefix . 'navgroups';
        $this->setFieldNames();
    }

    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function create(array $a_values)
    {
        $meth = __METHOD__ . '.';
        if ($a_values == []) { return false; }
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        $a_required_keys = ['ng_name'];
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            $this->error_message = 'Did not have the navgroup name.';
            return false;
        }
        $results = $this->read(['ng_name' => $a_values['ng_name']]);
        if ($results !== false && count($results) > 0) {
            $this->error_message = "The Navigation Group Name already exists.";
            return false;
        }
        $insert_value_names = $this->o_db->buildSqlInsert($a_values, $this->a_field_names);
        $sql = "
            INSERT INTO {$this->db_prefix}navgroups (
            {$insert_value_names}
            )
        ";
        $a_table_info = [
            'table_name'  => "{$this->db_prefix}navgroups",
            'column_name' => 'ng_id'
        ];
        $results = $this->o_db->insert($sql, $a_values, $a_table_info);
        $this->logIt('Insert Results: ' . $results, LOG_OFF, $meth . __LINE__);
        $this->logIt('db object: ' . var_export($this->o_db, TRUE), LOG_OFF, $meth . __LINE__);
        if ($results) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, $meth . __LINE__);
            return $ids[0];
        }
        else {
            $this->error_message = 'The navigation group record could not be saved.';
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
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == []
                ? ['order_by' => 'ng_name ASC']
                : $a_search_params;
            $a_search_values = Arrays::removeUndesiredPairs($a_search_values, $this->a_field_names);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY ng_name ASC";
        }
        $select_me = $this->o_db->buildSqlSelectFields($this->a_field_names);
        $where = trim($where);
        $sql =<<<EOT

SELECT {$select_me}
FROM {$this->db_table}
{$where}

EOT;
        $this->logIt($sql, LOG_OFF, $meth . __LINE__);
        $this->logIt("Search Values: " . var_export($a_search_values, true), LOG_OFF, $meth . __LINE__);
        $results = $this->o_db->search($sql, $a_search_values);
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
        $meth = __METHOD__ . '.';
        if (!isset($a_values['ng_id'])
            || $a_values['ng_id'] == ''
            || (is_string($a_values['ng_id']) && !is_numeric($a_values['ng_id']))
        ) {
            $this->error_message = 'The Navgroup id was not supplied.';
            return false;
        }
        $a_values = Arrays::removeUndesiredPairs($a_values, $this->a_field_names);
        $set_sql = $this->o_db->buildSqlSet($a_values, ['ng_id']);
        $sql = "
            UPDATE {$this->db_table}
            {$set_sql}
            WHERE ng_id = :ng_id
        ";
        $this->logIt($sql, LOG_OFF, $meth . __LINE__);
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        $results = $this->o_db->update($sql, $a_values, true);
        if ($results) {
            return true;
        }
        else {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
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
                $sql = "
                    DELETE FROM {$this->db_table}
                    WHERE ng_id = :ng_id
                ";
                $results = $this->o_db->delete($sql, array(':ng_id' => $ng_id), true);
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
     * Returns the SQL error message
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
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
