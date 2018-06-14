<?php
/**
 * Trait DbUtilityTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Common functions that would be used in several database classes.
 * Although this is designed to be used for all database classes, it
 * does lack some functionality for multi-table operations and needs
 * some work there.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.3.0
 * @date    2018-04-26 08:38:57
 * @change_log
 * - v2.3.0          - New methods for new property a_required_keys         - 2018-04-26 wer
 *                     Modified generic methods to use ExceptionHelper
 *                     Modified generic methods slightly to ensure required
 *                     values are provided.
 * - v2.2.0          - Added new method to get count of a table             - 2018-04-19 wer
 * - v2.1.0          - Changes to ModelException reflected here             - 2017-12-12 wer
 * - v2.0.0          - updated to use ModelException                        - 2017-06-11 wer
 * - v1.4.0          - refactoring elsewhere regarding db_prefix here too   - 2017-01-14 wer
 * - v1.3.0          - added new property lib_prefix, code clean up         - 2017-01-13 wer
 * - v1.2.0          - Refactoring of DbCommonTraits reflected here         - 2016-09-23 wer
 * - v1.1.0          - added a parameter to generic read select_distinct    - 2016-09-09 wer
 * - v1.0.0          - this went live a while back, guess it isn't alpha    - 2016-08-22 wer
 * - v1.0.0-alpha.9  - added functionality to buildSqlSelectFields method   - 2016-05-04 wer
 * - v1.0.0-alpha.0 - initial version                                       - 2016-03-18 wer
 * @todo Review to see where multi-table operations can be strenthened.
 */
trait DbUtilityTraits
{
    use DbCommonTraits;

    /** @var array */
    protected $a_db_config = [];
    /** @var array  */
    protected $a_db_fields = [];
    /** @var array  */
    protected $a_prefix = [];
    /** @var array  */
    protected $a_required_keys = [];
    /** @var string */
    protected $db_prefix = '';
    /** @var  string */
    protected $db_table = '';
    /** @var string Can be 'mysql', 'pgsql', 'sqlite' */
    protected $db_type = 'mysql';
    /** @var  string */
    protected $error_message = '';
    /** @var string $immutable_field */
    protected $immutable_field = '';
    /** @var string */
    protected $lib_prefix = 'lib_';
    /** @var \Ritc\Library\Services\DbModel */
    protected $o_db;
    /** @var  string */
    protected $primary_index_name = '';

    #### Generic CRUD calls ####
    /**
     * Generic method to create a new record in a table.
     * Requires both parameters to be complete to work.
     * This method assumes that if $a_values is an array of assoc arrays
     * then each assoc array has the same keys. It will blow up otherwise.
     * @param array $a_values     Required, the values to be saved in a new record.
     * @param array $a_parameters \code
     * ['a_required_keys' => [],
     *  'a_field_names'   => [],
     *  'a_psql'          => [
     *      'table_name'  => string,
     *      'column_name' => string
     *  ]
     * ] \endcode
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     * @see  \ref createparams
     */
    protected function genericCreate(array $a_values = [], array $a_parameters = [])
    {
        $meth = __METHOD__ . '.';
        if (empty($a_values)) {
            $this->error_message = 'No Values Provided';
            $error_code = ExceptionHelper::getCodeNumberModel('create missing values');
            throw new ModelException($this->error_message, $error_code);
        }
        if (empty($a_parameters['a_psql'])) {
            $a_psql = [
                'table_name'  => $this->db_table,
                'column_name' => $this->primary_index_name
            ];
            $db_table = $this->db_table;
        }
        else {
            $a_psql = $a_parameters['a_psql'];
            $db_table = $a_psql['table_name'];
        }
        $a_required_keys = isset($a_parameters['a_required_keys'])
            ? $a_parameters['a_required_keys']
            : [];

        $a_field_names = isset($a_parameters['a_field_names'])
            ? $a_parameters['a_field_names']
            : $this->a_db_fields != []
                ? $this->a_db_fields
                : [];
        // If a_field_names is empty, the sql cannot be built. Return false.
        if ($a_field_names == []) {
            $this->error_message = 'Missing required values';
            $error_code = ExceptionHelper::getCodeNumberModel('create_missing_values');
            throw new ModelException($this->error_message, $error_code);
        }

        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_value) {
                if (!empty($a_required_keys)) {
                    $a_missing_values = Arrays::findMissingValues($a_value, $a_required_keys);
                    if (!empty($a_missing_values)) {
                        $this->error_message = "Missing required keys: " . json_encode($a_missing_values);
                        $error_code = ExceptionHelper::getCodeNumberModel('create_missing_values');
                        throw new ModelException($this->error_message, $error_code);
                    }
                }
            }
            $sql_set = $this->buildSqlInsert($a_values[0], $a_field_names);
        }
        else {
            if (!empty($a_required_keys)) {
                $a_missing_values = Arrays::findMissingValues($a_values, $a_required_keys);
                if (!empty($a_missing_values)) {
                    $this->error_message = "Missing required keys: " . json_encode($a_missing_values);
                    $error_code = ExceptionHelper::getCodeNumberModel('create_missing_values');
                    throw new ModelException($this->error_message, $error_code);
                }
            }
            $sql_set = $this->buildSqlInsert($a_values, $a_field_names);
        }

        $sql =<<<SQL
INSERT INTO {$db_table} (
{$sql_set}
)

SQL;
        $this->logIt("SQL: " . $sql, LOG_OFF, $meth . __LINE__);
        $this->logIt("Values: " . var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        try {
            $this->o_db->insert($sql, $a_values, $a_psql);
            $a_new_ids = $this->o_db->getNewIds();
            if (count($a_new_ids) < 1) {
                $this->error_message = 'No New Ids were returned in the create.';
                $error_code = ExceptionHelper::getCodeNumberModel('create_unspecified');
                throw new ModelException($this->error_message, $error_code);
            }
            return $a_new_ids;
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, $e->getCode(), $e);
        }
    }

    /**
     * Generic method to read records from a table.
     * @param array $a_parameters Required ['table_name']
     * @note $a_parameters may include:\verbatim
     * 'table_name'      The name of the table being access.
     * 'a_fields'        The fields to return ['id', 'name', 'is_alive'] or ['id' as 'id', 'name' as 'the_name', 'is_alive' as 'is_dead']
     * 'a_search_for'    What to search for    ['id' => 3]
     * 'a_allowed_keys'  Which fields may be searched upon ['id', 'name']
     * 'return_format'   assoc, num, both - defaults to assoc
     * 'order_by'        The order to return  'is_alive DESC, name ASC'
     * 'search_type'     Either 'AND' | 'OR'
     * 'limit_to'        Limit the number of records to return
     * 'starting_from'   Which record number to start a limited return
     * 'comparison_type' What kind of comparison operator to use for ALL WHEREs
     * 'where_exists'    Either true or false \endverbatim
     * @return bool|array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    protected function genericRead(array $a_parameters = [])
    {
        if (!isset($a_parameters['table_name']) && $this->db_table == '') {
            $this->error_message = "The table name must be specified.";
            $error_code = ExceptionHelper::getCodeNumberModel('read_missing_values');
            throw new ModelException($this->error_message, $error_code);
        }
        elseif (isset($a_parameters['table_name'])) {
            $table_name = $a_parameters['table_name'];
            if (!$this->o_db->tableExists($table_name)) {
                if ($this->o_db->tableExists($this->db_prefix . $table_name)) {
                    $table_name = $this->db_prefix . $table_name;
                }
                elseif ($this->o_db->tableExists($this->lib_prefix . $table_name)) {
                    $table_name = $this->lib_prefix . $table_name;
                }
                else {
                    $this->error_message = "The table specified doesn't exist.";
                    $error_code = ExceptionHelper::getCodeNumberModel('structure');
                    throw new ModelException($this->error_message, $error_code);
                }
            }
        }
        else {
            $table_name = $this->db_table;
        }
        $a_fields = isset($a_parameters['a_fields'])
            ? $a_parameters['a_fields']
            : $this->a_db_fields;
        $a_search_for = isset($a_parameters['a_search_for'])
            ? $a_parameters['a_search_for']
            : [];
        $return_format = isset($a_parameters['return_format'])
            ? $a_parameters['return_format']
            : 'assoc';
        $a_allowed_keys = isset($a_parameters['a_allowed_keys'])
            ? $a_parameters['a_allowed_keys']
            : $this->a_db_fields;
        $distinct = isset($a_parameters['select_distinct'])
            ? $a_parameters['select_distinct'] === true ? 'DISTINCT ' : ''
            : '';

        unset($a_parameters['table_name']);
        unset($a_parameters['a_search_for']);
        unset($a_parameters['return_format']);
        unset($a_parameters['a_allowed_keys']);
        unset($a_parameters['select_distinct']);

        $select_me = $this->buildSqlSelectFields($a_fields);
        $where = $this->buildSqlWhere($a_search_for, $a_parameters, $a_allowed_keys);
        $sql = "
            SELECT {$distinct}{$select_me}
            FROM {$table_name}
            {$where}
        ";
        try {
            return $this->o_db->search($sql, $a_search_for, $return_format);
        }
        catch (ModelException $e) {
            $this->setErrorMessage();
            throw new ModelException($this->error_message, 200, $e);
        }
    }

    /**
     * Generic method to update values in a table WHERE primary index is as supplied.
     * Needs the primary index name. The primary index value also needs to
     * be in the $a_values. It only updates record(s) WHERE the primary index = primary index value.
     * @param array  $a_values Required may be be assoc array or an array of assoc arrays
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    protected function genericUpdate(array $a_values = [])
    {
        if ($a_values == []) {
            $this->error_message = 'No values provided to update.';
            $error_code = ExceptionHelper::getCodeNumberModel('update_missing_values');
            throw new ModelException($this->error_message, $error_code);
        }
        $primary_index_name = $this->primary_index_name;
        $a_required_keys = array($primary_index_name);
        $a_allowed_keys = $this->prepareListArray($this->a_db_fields);
        $set_sql = '';
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            $x = 1;
            foreach ($a_values as $a_thing) {
                if (!Arrays::hasRequiredKeys($a_thing, $a_required_keys)) {
                    $this->error_message = "The array must have the primary key in it.";
                    $error_code = ExceptionHelper::getCodeNumberModel('update_missing_values');
                    throw new ModelException($this->error_message, $error_code);
                }
                if ($x == 1) {
                    $set_sql = $this->buildSqlSet($a_thing, $a_required_keys, $a_allowed_keys);
                }
                $x++;
            }
        }
        else {
            if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
                $this->error_message = "The array must have the primary key in it.";
                $error_code = ExceptionHelper::getCodeNumberModel('update_missing_values');
                throw new ModelException($this->error_message, $error_code);
            }
            $set_sql = $this->buildSqlSet($a_values, $a_required_keys, $a_allowed_keys);
        }

        $sql = "
            UPDATE {$this->db_table}
            {$set_sql}
            WHERE {$primary_index_name} = :{$primary_index_name}
        ";
        try {
            return $this->o_db->update($sql, $a_values, true);
        }
        catch (ModelException $e) {
            $this->error_message = $e->getMessage();
            $error_code = ExceptionHelper::getCodeNumberModel('update_unspecified');
            throw new ModelException($this->error_message, $error_code, $e);
        }
    }

    /**
     * Deletes record(s) based on the primary index value.
     * @param mixed $record_ids Required
     *                          Can be an int, string or array. If array is list of record ids to delete, e.g.
     *                          [1, 2, 3, 4] or ['fred', 'barney', 'wilma', 'betty'], matching primary index type.
     *                          If array, it passes the values to the genericDeleteMultiple. This provided backwards
     *                          compatibility when genericDelete only allowed int.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    protected function genericDelete($record_ids = -1)
    {
        if (is_array($record_ids)) {
           if (empty($record_ids)) {
               $this->error_message = 'No record ids were provided.';
               $error_code = ExceptionHelper::getCodeNumberModel('delete_missing_primary');
               throw new ModelException($this->error_message, $error_code);
           }
           else {
               return $this->genericDeleteMultiple($record_ids);
           }
        }
        elseif (is_numeric($record_ids) && $record_ids < 1) {
            $this->error_message = 'No valid record ids were provided.';
            $error_code = ExceptionHelper::getCodeNumberModel('delete_missing_primary');
            throw new ModelException($this->error_message, $error_code);
        }
        $piname = $this->primary_index_name;
        $sql = "
          DELETE FROM {$this->db_table}
          WHERE {$piname} = :{$piname}
        ";
        $delete_this = [$piname => $record_ids];
        try {
            return $this->o_db->delete($sql, $delete_this, true);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, 410, $e);
        }
    }

    /**
     * Deletes a multiple records based on the primary index value.
     * @param array $a_record_ids Required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    protected function genericDeleteMultiple(array $a_record_ids = [])
    {
        if (empty($a_record_ids)) {
            $this->error_message = 'No record ids were provided.';
            $error_code = ExceptionHelper::getCodeNumberModel('delete_missing_primary');
            throw new ModelException($this->error_message, $error_code);
        }
        $piname = $this->primary_index_name;
        $sql =<<<SQL
DELETE FROM {$this->db_table}
WHERE {$piname} = :{$piname}
SQL;
        $a_delete_these = [];
        foreach ($a_record_ids as $record_id) {
           $a_delete_these[] = [$piname => $record_id];
        }
        try {
            return $this->o_db->delete($sql, $a_delete_these, false);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            $error_code = ExceptionHelper::getCodeNumberModel('delete_unspecified');
            throw new ModelException($this->error_message, $error_code);
        }
    }

    #### Utility Methods ####
    /**
     * Creates a string that is part of an INSERT sql statement.
     * It produces a string in "prepared" format, e.g. ":fred" for the values.
     * It removes any pair whose key is not in the allowed keys array.
     * @param array $a_values       assoc array of values to that will be inserted.
     *                              It should be noted that only the key name is important,
     *                              but using the array of the actual values being inserted
     *                              ensures that only those values are inserted.
     * @param array $a_allowed_keys list array of key names that are allowed in the a_values array.
     * @return string
     */
    protected function buildSqlInsert(array $a_values = [], array $a_allowed_keys = [])
    {
        if (count($a_values) === 0 || count($a_allowed_keys) === 0) {
            return '';
        }
        $a_values = $this->o_db->prepareKeys($a_values);
        $a_allowed_keys = $this->prepareListArray($a_allowed_keys);
        $a_values = Arrays::removeUndesiredPairs($a_values, $a_allowed_keys);
        $insert_names = '';
        $value_names  = '';
        foreach ($a_values as $key => $value) {
            if (strpos($key, ':') !== false) {
                $insert_name = str_replace(':', '', $key);
                $value_name  = $key;
            }
            else {
                $insert_name = $key;
                $value_name  = ':' . $key;
            }
            $insert_names .= $insert_names == ''
                ? '    ' . $insert_name
                : ",\n    "  . $insert_name;
            $value_names  .= $value_names == ''
                ? '    ' . $value_name
                : ",\n    " . $value_name;
        }
        return $insert_names . "\n) VALUES (\n" . $value_names;
    }

    /**
     * Builds the select field portion of a sql select statement.
     * Can be a simple array list or an assoc array which specifies
     * the name of the field in the array key, the name to be as is the array value, e.g.,
     * ['ngv' => 'general] becomes the string "ngv as 'general'"
     * @param array $a_values Required
     * @param string $prefix  Optional. Allows a table name or table alias to be added to the select name.
     * @return string
     */
    protected function buildSqlSelectFields(array $a_values = [], $prefix = '')
    {
        if ($a_values == []) {
            return '';
        }
        $select_me = '';
        if ($prefix != '' && strpos($prefix, '.') === false) {
            $prefix .= '.';
        }
        if (Arrays::isAssocArray($a_values)) {
            foreach ($a_values as $name => $name_as) {
                $select_me .= $select_me == ''
                    ? $prefix . $name . " as '" . $name_as . "'"
                    : ', ' . $prefix . $name . " as '" . $name_as . "'";
            }
        }
        else {
            foreach ($a_values as $name) {
                $select_me .= $select_me == ''
                    ? $prefix . $name
                    : ', ' . $prefix . $name;
            }
        }
        return $select_me;
    }

    /**
     * Builds the SET part of an UPDATE sql statement.
     * Provides optional abilities to skip certain pairs and removed undesired pairs.
     * @param array $a_values       required key=>value pairs
     *                              pairs are those to be used in the statement fragment.
     * @param array $a_skip_keys    optional list of keys to skip in the set statement
     * @param array $a_allowed_keys optional list of allowed keys to be in the values array.
     * @return string $set_sql
     */
    protected function buildSqlSet(array $a_values = [], array $a_skip_keys = ['nothing_to_skip'], array $a_allowed_keys = [])
    {
        if ($a_values == array()) { return ''; }
        $set_sql = '';
        $a_values = $this->o_db->prepareKeys($a_values);
        if ($a_allowed_keys !== []) {
            $a_allowed_keys = $this->prepareListArray($a_allowed_keys);
            $a_values = Arrays::removeUndesiredPairs($a_values, $a_allowed_keys);
        }
        $a_skip_keys = $this->prepareListArray($a_skip_keys);
        foreach ($a_values as $key => $value) {
            if (!in_array($key, $a_skip_keys)) {
                if ($set_sql == '' ) {
                    $set_sql = "SET " . str_replace(':', '', $key) . " = {$key} ";
                }
                else {
                    $set_sql .= ", " . str_replace(':', '', $key) . " = {$key} ";
                }
            }
        }
        return $set_sql;
    }

    /**
     * Builds the WHERE section of a SELECT stmt.
     * Also optionally builds the ORDER BY and LIMIT section of a SELECT stmt.
     * It might be noted that if first two arguments are empty, it returns a blank string.
     * Also, just so I have this documented, if the where included multiple tables, $a_allowed_keys should be set.
     * @param array $a_search_for        optional assoc array field_name=>field_value
     * @param array $a_search_parameters optional allows one to specify various settings
     * @param array $a_allowed_keys      optional list array, specifically use if multiple tables are being used.
     * @ref searchparams For more info on a_search_parameters.
     * @return string $where
     */
    protected function buildSqlWhere(array $a_search_for = [], array $a_search_parameters = [], array $a_allowed_keys = [])
    {
        $search_type = 'AND';
        $comparison_type = '=';
        $starting_from = '';
        $limit_to = '';
        $order_by = '';
        $where_exists = false;
        $where = '';
        if (count($a_search_parameters) > 0) {
            $a_allowed_parms = array(
                'search_type',
                'comparison_type',
                'starting_from',
                'limit_to',
                'order_by',
                'where_exists'
            );
            foreach ($a_search_parameters as $key => $value) {
                if (array_search($key, $a_allowed_parms) !== false) {
                    $$key = $value;
                }
            }
        }
        if ($a_allowed_keys == []) {
            $a_allowed_keys = $this->a_db_fields;
        }
        /* set the $key to have a value compatible for a prepared statement */
        if (count($a_search_for) > 0) {
            $a_search_for = $this->o_db->prepareKeys($a_search_for);
        }
        /* remove any unwanted pairs from array */
        if (count($a_search_for) > 0 && count($a_allowed_keys) > 0) {
            $a_allowed_keys = $this->prepareListArray($a_allowed_keys);
            $a_search_for = Arrays::removeUndesiredPairs($a_search_for, $a_allowed_keys);
        }
        /* after all that, if there are still pairs, go for it */
        if (count($a_search_for) > 0) {
            foreach ($a_search_for as $key => $value) {
                $field_name = preg_replace('/^:/', '', $key);
                if (strpos($key, '.') !== false) {
                    $key = preg_replace('/^:(.*)\.(.*)/', ':$2', $key);
                }
                if ($where_exists === false) {
                    $where = "WHERE {$field_name} {$comparison_type} {$key} \n";
                    $where_exists = true;
                }
                else {
                    $where = $where == '' ? "\n" : $where;
                    $where .= "{$search_type} {$field_name} {$comparison_type} {$key} \n";
                }
            }
        }
        if ($order_by != '') {
            $where .= "ORDER BY {$order_by} \n";
        }
        if ($limit_to != '') {
            if ($starting_from != '') {
                if($starting_from > 0) {
                    --$starting_from; // limit offset starts at 0 so if we want to start at record 6 the LIMIT offset is 5
                }
                $where .= "LIMIT {$starting_from}, {$limit_to}";
            }
            else {
                $where .= "LIMIT {$limit_to}";
            }
        }
        return $where;
    }

    /**
     * Massages values used to update record(s).
     * @param array  $a_values
     * @param string $immutable_field
     * @param array  $a_immutable_fields
     * @return array|bool
     */
    protected function fixUpdateValues(array $a_values = [], $immutable_field = '', array $a_immutable_fields = [])
    {
        $meth = __METHOD__ . '.';
        $log_message = 'a values ' . var_export($a_values, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $log_message = 'a immut: ' . var_export($a_immutable_fields, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                $results = $this->fixUpdateValues($a_record);
                if ($results === false) {
                    return false;
                }
                $a_values[$key] = $results;
            }
        }
        else {
            if (
                !isset($a_values[$this->primary_index_name])
                || $a_values[$this->primary_index_name] == ''
                || (!is_numeric($a_values[$this->primary_index_name]))
            ) {
                return false;
            }
            else {
                $primary_id = $a_values[$this->primary_index_name];
                try {
                    $a_results = $this->readById($primary_id);
                    if (isset($a_results[$immutable_field]) && $a_results[$immutable_field] == 'true') {
                        foreach ($a_immutable_fields as $field) {
                            unset($a_values[$field]);
                        }
                    }
                } /** @noinspection PhpRedundantCatchClauseInspection */
                catch (ModelException $e) {
                    $this->error_message = $e->errorMessage();
                    return false;
                }

            }
        }
        $log_message = 'final values ' . var_export($a_values, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        return $a_values;
    }

    /**
     * Checks to see if the array(s) has key specified.
     * @param array  $a_values
     * @param string $primary_index_name
     * @return bool
     */
    protected function hasPrimaryId(array $a_values = [], $primary_index_name = '')
    {
        if (empty($a_values[$primary_index_name]) || (!is_numeric($a_values[$primary_index_name]))) {
            return false;
        }
        return true;
    }

    /**
     * Returns true/false if there are records.
     * @param array  $a_values The values for the read statement.
     * @return bool
     */
    protected function hasRecords($a_values = [])
    {
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                try {
                    $results = $this->read($a_record);
                } /** @noinspection PhpRedundantCatchClauseInspection */
                catch (ModelException $e) {
                    return false;
                }
                if (empty($results)) {
                    return false;
                }
            }
        }
        else {
            try {
                $results = $this->read($a_values);
            } /** @noinspection PhpRedundantCatchClauseInspection */
            catch (ModelException $e) {
                return false;
            }
            if (empty($results)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks to see if the record is immutable.
     * There has to be a field in the table that has immutable in its name
     * else it will be false.
     *
     * @param int $record_id
     * @return bool
     */
    public function isImmutable($record_id = -1)
    {
        if ($record_id < 1 || empty($this->immutable_field)) {
            false;
        }
        try {
            $results = $this->readById($record_id);
            if (!empty($results[$this->immutable_field]) && $results[$this->immutable_field] === 'true') {
                return true;
            }
            return false;
        }
        catch (ModelException $e) {
            return true;
        }
    }

    /**
     * Checks to see if the id is valid.
     * @param string $id
     * @return bool
     */
    public function isValidId($id = '')
    {
        if (empty($id)) {
            false;
        }
        try {
            $results = $this->readById($id);
            if (!empty($results[$this->primary_index_name]) &&
                $results[$this->primary_index_name] === $id
            ) {
                return true;
            }
            return false;
        }
        catch (ModelException $e) {
            return false;
        }
    }

    /**
     * Creates an array of required keys to do db opperations.
     * By default, returns all fields except the primary index name.
     * @param array $a_exclude   Optional, defaults to empty
     * @param bool  $exclude_pin Optional, defaults to true
     */
    protected function makeRequiredKeys(array $a_exclude = [], $exclude_pin = true)
    {
        $a_field_names = $this->a_db_fields;
        if ($exclude_pin) {
            $pin = $this->primary_index_name;
            $key = array_search($pin, $a_field_names);
            unset($a_field_names[$key]);
        }
        if (!empty($a_exclude)) {
            foreach ($a_exclude as $value) {
                $key = array_search($value, $a_field_names);
                unset($a_field_names[$key]);
            }
        }
        $this->a_required_keys = $a_field_names;
    }

    /**
     * Verifies that the php mysqli extension is installed
     * Left over, not sure it is needed now
     * @return bool
     */
    protected function mysqliInstalled()
    {
        if (function_exists('mysqli_connect')) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Changes the list array so that the values (key names) have "prepared" format, e.g., ":fred".
     * @param array $array
     * @return array
     */
    protected function prepareListArray(array $array = [])
    {
        foreach ($array as $key => $value) {
            $array[$key] = substr($value, 0, 1) === ':'
                ? $value
                : ':' . $value;
        }
        return $array;
    }

    /**
     * Reads a record based on id.
     *
     * @param int    $primary_id
     * @param string $pi_name
     * @return array|bool
     * @throws ModelException
     */
    public function readById($primary_id = -1, $pi_name = '')
    {
        if (is_numeric($primary_id) && $primary_id > 0) {
            if ($pi_name == '') {
                $pi_name = $this->primary_index_name;
            }
            $a_search_for = ['a_search_for' => [$pi_name => $primary_id]];
            try {
                $results = $this->genericRead($a_search_for);
                if (!empty($results[0])) {
                    return $results[0];
                }
                return [];
            }
            catch (ModelException $e) {
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
        else {
            $message = 'Missing valid primary id value.';
            $err_code = ExceptionHelper::getCodeNumberModel('read missing value');
            throw new ModelException($message, $err_code);
        }
    }

    /**
     * Returns the number of records in the table.
     * @param string $where the where values string if desired, e.g. 'fred' LIKE 'barney'.
     * @param array  $a_where_values
     * @return int
     */
    public function readCount($where = '', array $a_where_values = [])
    {
        $sql = "SELECT count(*) as 'count' FROM " . $this->db_table;
        if (!empty($where) && !empty($a_where_values)) {
            $sql .= ' ' . $where;
        }
        try {
            $results = $this->o_db->search($sql, $a_where_values);
            return $results[0]['count'];
        }
        catch (ModelException $e) {
            return 0;
        }
    }

    /**
     * Removes unwanted key=>values for a prepared query
     * @param array $a_required_keys
     * @param array $a_values the array which needs cleaned up
     * @return array $a_fixed_values
     */
    protected function removeBadKeys(array $a_required_keys = array(), array $a_values = array())
    {
        foreach ($a_values as $key => $value) {
            if (
                array_search($key, $a_required_keys) === false
                &&
                array_search(str_replace(':', '', $key), $a_required_keys) === false
                &&
                array_search(':' . $key, $a_required_keys) === false
            ) {
                unset($a_values[$key]);
            }
        }
        return $a_values;
    }

    /**
     * @param string $value
     */
    public function setErrorMessage($value = '')
    {
        if ($value == '') {
            $value = $this->o_db->retrieveFormattedSqlErrorMessage();
        }
        $this->error_message = $value;
    }

    /**
     * Sets the class property immutable_field from the list of db_fields, if it exists.
     */
    protected function setImmutableField()
    {
       foreach ($this->a_db_fields as $field) {
           if (strpos($field, 'immutable') !== false) {
               $this->immutable_field = $field;
               break;
           }
       }
    }

    /**
     * Retrieves the primary index field name from database and sets the class property.
     */
    protected function setPrimaryIndexName()
    {
        switch($this->db_type) {
            case 'pgsql':
                /** @noinspection SyntaxError */
                $query = "
                    SELECT a.attname as pkeyname, format_type(a.atttypid, a.atttypmod) AS data_type
                    FROM   pg_index i
                    JOIN   pg_attribute a ON a.attrelid = i.indrelid
                           AND a.attnum = ANY(i.indkey)
                    WHERE  i.indrelid = '{$this->db_table}'::regclass
                    AND    i.indisprimary;
                ";
                try {
                    $results = $this->o_db->rawQuery($query);
                    if (!empty($results)) {
                        $this->primary_index_name = $results[0]['pkeyname'];
                    }
                    else {
                        $this->primary_index_name = '';
                    }
                }
                catch (ModelException $e) {
                    $this->setErrorMessage('Could not set primary index name' . $e->errorMessage());
                }
                break;
            case 'sqlite':
                $query = "PRAGMA table_info({$this->db_table})";
                try {
                    $results = $this->o_db->rawQuery($query);
                    if (!empty($results)) {
                        foreach ($results as $a_row) {
                            if ($a_row['pk'] === 1) {
                                $this->primary_index_name = $a_row['name'];
                            }
                        }
                    }
                    else {
                        $this->primary_index_name = '';
                    }
                }
                catch (ModelException $e) {
                    $this->setErrorMessage('Could not set primary index name' . $e->errorMessage());
                }
                break;
            case 'mysql':
            default:
                $query = "SHOW index FROM {$this->db_table}";
                try {
                    $results = $this->o_db->rawQuery($query);
                    if (!empty($results)) {
                        foreach ($results as $a_index) {
                            if ($a_index['Key_name'] == 'PRIMARY') {
                                $this->primary_index_name = $a_index['Column_name'];
                            }
                        }
                    }
                    else {
                        $this->primary_index_name = '';
                    }
                }
                catch (ModelException $e) {
                    $this->setErrorMessage('Could not set primary index name' . $e->errorMessage());
                }
            // end 'mysql' and default;
        }
    }

    /**
     * Sets up the standard properties.
     * @param \Ritc\Library\Services\DbModel $o_db
     * @param string                         $table_name
     */
    protected function setupProperties(DbModel $o_db, $table_name = '')
    {
        $this->o_db        = $o_db;
        $this->a_db_config = $o_db->getDbConfig();
        $this->a_prefix    = $o_db->getPrefixArray();
        $this->db_prefix   = $o_db->getDbPrefix();
        $this->db_type     = $o_db->getDbType();
        $this->lib_prefix  = $o_db->getLibPrefix();
        if ($table_name != '') {
            $tname0 = $table_name;
            $tname1 = $this->db_prefix . $table_name;
            $tname2 = $this->lib_prefix . $table_name;
            if ($o_db->tableExists($tname0)) {
                $this->db_table = $tname0;
            }
            elseif ($o_db->tableExists($tname1)) {
                $this->db_table = $tname1;
            }
            elseif ($o_db->tableExists($tname2)) {
                $this->db_table = $tname2;
            }
            else {
                $this->db_table = '';
            }
            if ($this->db_table != '') {
                try {
                    $this->a_db_fields = $o_db->selectDbColumns($this->db_table);
                }
                catch (ModelException $e) {
                    $this->setErrorMessage($e->errorMessage());
                }
                $this->setPrimaryIndexName();
                $this->setImmutableField();
            }
        }
    }

    ### Getters and Setters ###
    /**
     * Getter for $a_db_config.
     * @return array
     */
    public function getDbConfig()
    {
        return $this->a_db_config;
    }

    /**
     * Getter for $a_db_fields.
     * @return array
     */
    public function getDbFields()
    {
        return $this->a_db_fields;
    }

    /**
     * Getter for $db_prefix.
     * @return string
     */
    public function getDbPrefix()
    {
        return $this->db_prefix;
    }

    /**
     * Getter for $db_table.
     * @return string
     */
    public function getDbTable()
    {
        return $this->db_table;
    }

    /**
     * Getter for $db_type.
     * @return string
     */
    public function getDbType()
    {
        return $this->db_type;
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
     * GETter for property lib_prefix.
     * @return string
     */
    public function getLibPrefix()
    {
        return $this->lib_prefix;
    }

    /**
     * Retrieves the class property $primary_index_name.
     * @return string
     */
    public function getPrimaryIndexName()
    {
        return $this->primary_index_name;
    }

    /**
     * GETter for class property a_required_keys.
     * @return array
     */
    public function getRequiredKeys()
    {
        return $this->a_required_keys;
    }

    /**
     * Adds a new key name to the class property a_required_keys, if it doesn't exist.
     * @param string $key_name
     */
    public function setRequiredKey($key_name = '')
    {
        if ($key_name !== '') {
            if (!array_key_exists($key_name, $this->a_required_keys)) {
                $this->a_required_keys[] = $key_name;
            }
        }
    }

    /**
     * SETter for class property a_required_keys.
     * @param array $a_required_keys
     */
    public function setRequiredKeys(array $a_required_keys = [])
    {
        $this->a_required_keys = $a_required_keys;
    }
}

