<?php
/**
 * @brief     Does all the database CRUD stuff for the navigation to navgroups mapping.
 * @ingroup   ritc_library lib_models
 * @file      Ritc/Library/Models/NavNgMapModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0 β1
 * @date      2016-02-25 12:06:45
 * @note <b>Change Log</b>
 * - v1.0.0 β1 - Initial version                              - 02/25/2016 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NavNgMapModel.
 * @class   NavNgMapModel
 * @package Ritc\Library\Models
 */
class NavNgMapModel implements ModelInterface
{
    use LogitTraits;

    /** @var array */
    private $a_field_names = array();
    /** @var string */
    private $db_type = '';
    /** @var string */
    private $db_prefix = '';
    /** @var string */
    private $error_message = '';
    /** @var \Ritc\Library\Services\DbModel */
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
        $this->setFieldNames();
    }

    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function create(array $a_values = [])
    {
        $meth = __METHOD__ . '.';
        if ($a_values == []) { return false; }
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        $a_required_keys = $this->a_field_names;
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            $this->error_message = 'Did not have the nav id and/or navgroup id.';
            return false;
        }

        /* check to see if the corresponding records exist - assumes nothing */
        $o_nav = new NavigationModel($this->o_db);
        $o_ng  = new NavgroupsModel($this->o_db);
        $results1 = $o_nav->read(['nav_id' => $a_values['nav_id']]);
        $results2 = $o_ng->read(['ng_id' => $a_values['ng_id']]);

        if ($results1 !== false && count($results1) === 0) {
            $this->error_message = "The Navigation record does not exist.";
            return false;
        }
        if ($results2 !== false && count($results2) === 0) {
            $this->error_message = "The Navigation Group record does not exist.";
            return false;
        }
        /* check done */

        $insert_value_names = $this->o_db->buildSqlInsert($a_values, $this->a_field_names);
        $sql = "
            INSERT INTO {$this->db_prefix}nav_ng_map (
            {$insert_value_names}
            )
        ";
        // following probably is ineffective
        $a_table_info = [
            'table_name'  => "{$this->db_prefix}nav_ng_map",
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
            $this->error_message = 'The nav ng map record could not be saved.';
            return false;
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values
     * @param array $a_search_params
     * @return array
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == []
                ? ['order_by' => 'ng_id ASC, nav_id ASC']
                : $a_search_params;
            $a_search_values = Arrays::removeUndesiredPairs($a_search_values, $this->a_field_names);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY ng_id ASC, nav_id ASC";
        }
        $select_me = $this->o_db->buildSqlSelectFields($this->a_field_names);
        $sql = "
            SELECT {$select_me}
            FROM {$this->db_prefix}nav_ng_map
            {$where}
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__);
        $this->logIt("Search Values: " . var_export($a_search_values, true), LOG_OFF);
        $results = $this->o_db->search($sql, $a_search_values);
        return $results;
    }

    /**
     * Generic update for a record using the values provided.
     * Required by interface but not used in this instance.
     * The two fields in the table create a single primary key so can not be changed.
     * To change, an INSERT/DELETE thing has to be done.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values)
    {
        return false;
    }

    /**
     * Generic deletes a record based on the id provided.
     * @param int $ng_id  semi-optional, either/both ng_id and/or nav_id must be set
     * @param int $nav_id semi-optional, either/both ng_id and/or nav_id must be set
     * @note The obvious needs to be noted. If only one param is provided, all the records
     *      for that id will be deleted.
     * @return bool
     */
    public function delete($ng_id = -1, $nav_id = -1)
    {
        if ($ng_id == -1 && $nav_id == -1) {
            return false;
        }
        elseif ($ng_id == -1) {
            $where = 'nav_id = :nav_id';
            $a_values = [':nav_id' => $nav_id];
        }
        elseif ($nav_id == -1) {
            $where = 'ng_id = :ng_id';
            $a_values = [':ng_id' => $ng_id];
        }
        else {
            $where = "ng_id = :ng_id AND nav_id = :nav_id";
            $a_values = [':ng_id' => $ng_id, ':nav_id' => $nav_id];
        }
        $sql = "
            DELETE FROM {$this->db_prefix}nav_ng_map
            WHERE {$where}";
        $results = $this->o_db->delete($sql, $a_values, true);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
        else {
            return true;
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
                'nav_id'
            ];
        }
    }

}
