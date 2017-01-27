<?php
/**
 * @brief     Does all the database CRUD stuff for the navigation.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/NavigationModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.1
 * @date      2017-01-27 12:38:33
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.1 - Refactoring in DbUtilityTraits reflected here - 2017-01-27 wer
 * - v1.0.0-alpha.0 - Initial version                               - 02/24/2016 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NavigationModel.
 * @class   NavigationModel
 * @package Ritc\Library\Models
 */
class NavigationModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /** @var array */
    private $a_field_names = array();

    /**
     * PageModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'navigation', 'lib');
        $this->setFieldNames();
    }

    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return string|bool
     */
    public function create(array $a_values)
    {
        $meth = __METHOD__ . '.';
        if ($a_values == array()) { return false; }
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        $a_required_keys = [
            'url_id',
            'nav_parent_id',
            'nav_name',
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

        $results = $this->genericCreate($a_values, $a_params);

        if ($results) {
            return $results[0];
        }
        else {
            $this->error_message = 'The nav could not be saved.';
            return false;
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'nav_parent_id ASC, nav_order ASC, nav_name ASC']
     * @return array
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        $meth = __METHOD__ . '.';
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_values,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'nav_parent_id ASC, nav_order ASC, nav_name ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        $results = $this->genericRead($a_parameters);
        $this->logIt("Nav Search results:\n" . var_export($results, true), LOG_OFF, $meth . __LINE__);
        return $results;
    }

    /**
     * Generic update for a record using the values provided.
     * @param array $a_values Required
     * @return bool
     */
    public function update(array $a_values = [])
    {
        if ($a_values == []) {
            $this->error_message = "No values supplied to update the record.";
            return false;
        }
        return $this->genericUpdate($a_values);
    }

    /**
     * Deletes a record based on the id provided.
     * It also deletes the relational records in the nav navgroup map table.
     * @param int $nav_id Required.
     * @return bool
     */
    public function delete($nav_id = -1)
    {
        if ($nav_id == -1) { return false; }

        /* Going to assume that the map isn't set for relations */
        $o_map = new NavNgMapModel($this->o_db);
        $this->o_db->startTransaction();
        $results = $o_map->delete(-1,$nav_id);
        if (!$results) {
            $this->error_message = $o_map->getErrorMessage();
            $this->o_db->rollbackTransaction();
            return false;
        }
        else {
            $results = $this->genericDelete($nav_id);
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

    /**
     * Checks to see the the nav has children.
     * @param int $nav_id
     * @return bool
     */
    public function navHasChildren($nav_id = -1)
    {
        if ($nav_id == -1) {
            return false;
        }
        $sql =<<<EOT
SELECT DISTINCT nav_level
FROM {$this->db_table}
WHERE nav_parent_id = :nav_parent_id
AND nav_id != nav_parent_id
EOT;
        $a_search_values = [':nav_parent_id' => $nav_id, ];
        $results = $this->o_db->search($sql, $a_search_values);
        if ($results !== false && count($results) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getFieldNames()
    {
        return $this->a_field_names;
    }

    /**
     * Sets the field names used by many of the sql statements.
     * Removes duplication of definition. Allows them to be changed on the fly.
     * @param array $a_field_names
     */
    public function setFieldNames(array $a_field_names = [])
    {
        if (count($a_field_names) > 0) {
            $this->a_field_names = $a_field_names;
        }
        else {
            $this->a_field_names = [
                'nav_id',
                'nav_page_id',
                'nav_parent_id',
                'nav_name',
                'nav_css',
                'nav_level',
                'nav_order',
                'nav_active'
            ];
        }
    }

    /**
     * Implements the ModelInterface method, getErrorMessage.
     * return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }
}
