<?php
/**
 * @brief     Does all the database CRUD stuff for the navigation.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/NavigationModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.3
 * @date      2017-06-15 16:06:48
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.3 - Refactored to use ModelException              - 2017-06-15 wer
 * - v1.0.0-alpha.2 - DbUtilityTraits change reflected here         - 2017-05-09 wer
 * - v1.0.0-alpha.1 - Refactoring in DbUtilityTraits reflected here - 2017-01-27 wer
 * - v1.0.0-alpha.0 - Initial version                               - 02/24/2016 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
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

    /**
     * PageModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'navigation');
    }

    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return string|bool
     * @throws \Ritc\Library\Exceptions\ModelException
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
        try {
            $results = $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $exception) {
            $message = $exception->errorMessage();
            $code = $exception->getCode();
            throw new ModelException($message, $code, $exception);
        }
        if (!empty($results)) {
            return $results[0];
        }
        else {
            $message = "A new record could not be created.";
            throw new ModelException($message, 100);
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'nav_parent_id ASC, nav_order ASC, nav_name ASC']
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_values,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'nav_parent_id ASC, nav_order ASC, nav_name ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Generic update for a record using the values provided.
     * @param array $a_values Required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values = [])
    {
        if ($a_values == []) {
            $this->error_message = "No values supplied to update the record.";
            throw new ModelException($this->error_message, 320);
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes a record based on the id provided.
     * @param int $nav_id Required.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($nav_id = -1)
    {
        if ($nav_id == -1) {
            $this->error_message = 'Missing required nav id.';
            throw new ModelException($this->error_message, 420);
        }
        try {
            return $this->genericDelete($nav_id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes a record based on the id provided.
     * It also deletes the relational records in the nav navgroup map table.
     * @param int $nav_id Required.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function deleteWithMap($nav_id = -1)
    {
        if ($nav_id == -1) {
            $this->error_message = 'Missing required nav id.';
            throw new ModelException($this->error_message, 420);
        }
        /* Going to assume that the map isn't set for relations */
        $o_map = new NavNgMapModel($this->o_db);
        try {
            $this->o_db->startTransaction();
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
        try {
            $o_map->delete(-1, $nav_id); // -1 specifies delete all map records with the nav_id
        }
        catch (ModelException $e) {
            $this->error_message = $o_map->getErrorMessage();
            $this->o_db->rollbackTransaction();
            throw new ModelException($this->error_message, 400);
        }
        try {
            $this->genericDelete($nav_id);
            try {
                $this->o_db->commitTransaction();
            }
            catch (ModelException $e) {
                throw new ModelException($e->errorMessage(), $e->getCode());
            }
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            try {
                $this->o_db->rollbackTransaction();
                throw new ModelException($this->error_message, $e->getCode());
            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                throw new ModelException($e->errorMessage(), $e->getCode());
            }
        }
        return true;
    }

    /**
     * Checks to see the the nav has children.
     * @param int $nav_id
     * @return bool
     */
    public function navHasChildren($nav_id = -1)
    {
        if ($nav_id == -1) {
            $this->error_message = 'Missing required nav id';
            return false;
        }
        $sql = "
            SELECT DISTINCT nav_level
            FROM {$this->db_table}
            WHERE nav_parent_id = :nav_parent_id
            AND nav_id != nav_parent_id
        ";
        $a_search_values = [':nav_parent_id' => $nav_id, ];
        try {
            $results = $this->o_db->search($sql, $a_search_values);
            if (count($results) > 0) {
                return true;
            }
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
        }
        return false;
    }
}
