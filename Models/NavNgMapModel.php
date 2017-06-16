<?php
/**
 * @brief     Does all the database CRUD stuff for the navigation to navgroups mapping.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/NavNgMapModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.3
 * @date      2017-06-15 16:34:42
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.3 - Refactored to use DbException                - 2017-06-15 wer
 * - v1.0.0-alpha.2 - DbUtilityTraits change reflected here        - 2017-05-09 wer
 * - v1.0.0-alpha.1 - Refactoring reflected here                   - 2017-01-27 wer
 * - v1.0.0-alpha.0 - Initial version                              - 02/25/2016 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\DbException;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NavNgMapModel.
 * @class   NavNgMapModel
 * @package Ritc\Library\Models
 */
class NavNgMapModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * NavNgMapModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'nav_ng_map');
    }

    /**
     * General create a record using the values provided.
     * @param array $a_values required assoc array or array of assoc array
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function create(array $a_values = [])
    {
        $a_required_keys = $this->a_db_fields;
        unset($a_required_keys[$this->primary_index_name]);
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
            throw new DbException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * If no search values and search params are given, all records are returned.
     * @param array $a_search_values optional
     * @param array $a_search_params optional
     * @return array
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_values,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'ng_id ASC, nav_id ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (DbException $e) {
            throw new DbException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Generic update for a record using the values provided.
     * Required by interface but not used in this instance.
     * The two fields in the table create a single primary key so can not be changed.
     * To change, an INSERT/DELETE thing has to be done.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function update(array $a_values)
    {
        throw new DbException('Update not allowed.', 350);
    }

    /**
     * Deletes a record based on either/both id(s) provided.
     * @param int $ng_id  semi-optional, either/both ng_id and/or nav_id must be set
     * @param int $nav_id semi-optional, either/both ng_id and/or nav_id must be set
     * @note The obvious needs to be noted. If only one param is provided, all the records
     *       for that id will be deleted. This may be an unwanted consequence.
     * @return bool
     * @throws \Ritc\Library\Exceptions\DbException
     */
    public function delete($ng_id = -1, $nav_id = -1)
    {
        if ($ng_id == -1 && $nav_id == -1) {
            return false;
        }
        elseif ($ng_id == -1) {
            $where = 'nav_id = :nav_id';
            $a_values = ['nav_id' => $nav_id];
        }
        elseif ($nav_id == -1) {
            $where = 'ng_id = :ng_id';
            $a_values = ['ng_id' => $ng_id];
        }
        else {
            $where = "ng_id = :ng_id AND nav_id = :nav_id";
            $a_values = ['ng_id' => $ng_id, 'nav_id' => $nav_id];
        }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE {$where}";
        try {
            return $this->o_db->delete($sql, $a_values, true);
        }
        catch (DbException $e) {
            $this->error_message = 'Unable to delete the record: ' . $this->o_db->getSqlErrorMessage();
            throw new DbException($this->error_message, 400, $e);
        }
    }

    /**
     * Checks to see if either/both navigation record and navgroups record exists.
     * Note that this does make a weird assumption: it must be able to verify records do not exist.
     * If it errors on the read it returns true, as if it found something.
     * @param array $a_values
     * @return bool
     */
    public function relatedRecordsExist(array $a_values = [])
    {
        if (empty($a_values['nav_id']) && empty($a_values['ng_id'])) {
            return true;
        }
        if (!empty($a_values['nav_id'])) {
            $o_nav = new NavigationModel($this->o_db);
            try {
                $results = $o_nav->read(['nav_id' => $a_values['nav_id']]);
                if (count($results) > 0) {
                    return true;
                }
            }
            catch (DbException $e) {
                $this->error_message = "The Navigation record does not exist.";
                return true;
            }
        }
        if (!empty($a_values['ng_id'])) {
            $o_ng  = new NavgroupsModel($this->o_db);
            try {
                $results = $o_ng->read(['ng_id' => $a_values['ng_id']]);
                if (count($results) > 0) {
                    $this->error_message = "The Navgroup record does not exist.";
                    return true;
                }
            }
            catch (DbException $e) {
                $this->error_message = "The Navgroup record does not exist.";
                return true;
            }
        }
        return false;
    }
}
