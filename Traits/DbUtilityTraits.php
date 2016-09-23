<?php
/**
 * @brief     Common functions that would be used in several database classes.
 * @ingroup   lib_traits
 * @file      DbUtilityTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.2.0
 * @date      2016-09-23 15:35:22
 * @note <b>Change Log</b>
 * - v1.2.0          - Refactoring of DbCommonTraits reflected here         - 2016-09-23 wer
 * - v1.1.0          - added a parameter to generic read select_distinct    - 2016-09-09 wer
 * - v1.0.0          - this went live a while back, guess it isn't alpha    - 2016-08-22 wer
 * - v1.0.0-alpha.10 - bug fix                                              - 2016-08-22 wer
 * - v1.0.0-alpha.9 - added functionality to buildSqlSelectFields method    - 2016-05-04 wer
 * - v1.0.0-alpha.8 - potential bug fix in genericDelete                    - 2016-04-20 wer
 * - v1.0.0-alpha.7 - bug fix in buildSqlWhere                              - 2016-04-13 wer
 * - v1.0.0-alpha.6 - bug fix in setupProperties                            - 2016-04-12 wer
 * - v1.0.0-alpha.5 - Added new method, additional refactoring              - 2016-04-01 wer
 *     - hasRecords
 *     - notEmptyArray
 *     - removed second parameter from genericUpdate, not needed
 *     - removed second parameter from genericDelete, not needed
 * - v1.0.0-alpha.4 - bug fix                                               - 2016-03-29 wer
 * - v1.0.0-alpha.3 - bug fixes                                             - 2016-03-28 wer
 * - v1.0.0-alpha.2 - modified genericRead to be more complete              - 2016-03-24 wer
 * - v1.0.0-alpha.1 - first hopefully working version                       - 2016-03-23 wer
 * - v1.0.0-alpha.0 - initial version                                       - 2016-03-18 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\DbModel;

/**
 * Class DbUtilityTraits Methods that are generic and can be used in many Model classes.
 * @class DbUtilityTraits
 * @package Ritc\Library\Traits
 */
trait DbUtilityTraits {
    use DbCommonTraits;

    /** @var array */
    protected $a_db_config = [];
    /** @var array  */
    protected $a_db_fields = [];
    /** @var string */
    protected $db_prefix = '';
    /** @var  string */
    protected $db_table = '';
    /** @var string Can be 'mysql', 'pgsql', 'sqlite' */
    protected $db_type = 'mysql';
    /** @var  string */
    protected $error_message = '';
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
     * @param array $a_values      The values to be saved in a new record.
     * @param array $a_parameters  \code
     * ['a_required_keys' => [],
     *  'a_field_names'   => [],
     *  'a_psql'          => [
     *      'table_name'  => string,
     *      'column_name' => string
     * ]] \endcode
     * @see \ref createparams
     * @return array|bool
     */
    protected function genericCreate(array $a_values = [], array $a_parameters = [])
    {
        $meth = __METHOD__ . '.';
        $a_values = $this->o_db->prepareKeys($a_values);

        $db_table = $this->db_table != ''
            ? $this->db_table
            : isset($a_parameters['a_psql']['table_name'])
                ? $a_parameters['a_psql']['table_name']
                : '';

        $a_required_keys = isset($a_parameters['a_required_keys'])
            ? $this->prepareListArray($a_parameters['a_required_keys'])
            : [];

        $a_field_names = isset($a_parameters['a_field_names'])
            ? $this->prepareListArray($a_parameters['a_field_names'])
            : $this->a_db_fields != []
                ? $this->prepareListArray($this->a_db_fields)
                : [];
        // If a_field_names is empty, the sql cannot be built. Return false.
        if ($a_field_names == []) {
            return false;
        }
        $log_message = 'Field Names ' . var_export($a_field_names, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_psql = isset($a_parameters['a_psql'])
            ? $a_parameters['a_psql']
            : ['table_name' => $this->db_table];

        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_value) {
                if (!Arrays::hasRequiredKeys($a_value, $a_required_keys)) {
                    $a_missing_keys = Arrays::findMissingKeys($a_required_keys, $a_value);
                    $this->error_message = "Missing required values: " . json_encode($a_missing_keys);
                    return false;
                }
            }
            $sql_set = $this->buildSqlInsert($a_values[0], $a_field_names);
        }
        else {
            if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
                $a_missing_keys = Arrays::findMissingKeys($a_required_keys, $a_values);
                $this->error_message = "Missing required values: " . json_encode($a_missing_keys);
                return false;
            }
            $sql_set = $this->buildSqlInsert($a_values, $a_field_names);
        }

        $sql =<<<SQL
INSERT INTO {$db_table} (
{$sql_set}
)

SQL;
        $this->logIt("SQL: " . $sql, LOG_OFF, $meth . __LINE__);
        $log_message = 'Create Values:  ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $results = $this->o_db->insert($sql, $a_values, $a_psql);
        if ($results) {
            $a_new_ids = $this->o_db->getNewIds();
            if (count($a_new_ids) < 1) {
                $this->error_message = 'No New Ids were returned in the create.';
                return false;
            }
            return $a_new_ids;
        }
        else {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            return false;
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
     */
    protected function genericRead(array $a_parameters = [])
    {
        $meth = __METHOD__ . '.';
        if (!isset($a_parameters['table_name']) && $this->db_table == '') {
            $this->logIt("The table name must be specified.", LOG_OFF, $meth . __LINE__);
            return false;
        }
        elseif (isset($a_parameters['table_name'])) {
            $table_name = $a_parameters['table_name'];
            if (strpos($table_name, $this->db_prefix) === false) {
                $table_name = $this->db_prefix . $table_name;
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
        $log_message = 'Parameters before unset:  ' . var_export($a_parameters, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        unset($a_parameters['table_name']);
        unset($a_parameters['a_search_for']);
        unset($a_parameters['return_format']);
        unset($a_parameters['a_allowed_keys']);
        unset($a_parameters['select_distinct']);

        $log_message = 'Search For Values:  ' . var_export($a_search_for, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $select_me = $this->buildSqlSelectFields($a_fields);
        $where = $this->buildSqlWhere($a_search_for, $a_parameters, $a_allowed_keys);
        $sql =<<<SQL
SELECT {$distinct}{$select_me}
FROM {$table_name}
{$where}

SQL;
        $this->logIt("SQL:\n" . $sql, LOG_OFF, $meth . __LINE__);
        $results = $this->o_db->search($sql, $a_search_for, $return_format);
        if ($results !== false) {
            return $results;
        }
        else {
            $this->setErrorMessage();
            return false;
        }
    }

    /**
     * Generic method to update values in a table WHERE primary index is as supplied.
     * Needs the primary index name. The primary index value also needs to
     * be in the $a_values. It only updates record(s) WHERE the primary index = primary index value.
     * @param array  $a_values           Required
     * @return bool
     */
    protected function genericUpdate(array $a_values = [])
    {
        if ($a_values == []) {
            return false;
        }
        $primary_index_name = $this->primary_index_name;
        $a_required_keys = array($primary_index_name);
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            $this->error_message = "The array must have the primary key in it.";
            return false;
        }
        $a_allowed_keys = $this->prepareListArray($this->a_db_fields);
        $set_sql = $this->buildSqlSet($a_values, $a_required_keys, $a_allowed_keys);

        $sql =<<<SQL
UPDATE {$this->db_table}
{$set_sql}
WHERE {$primary_index_name} = :{$primary_index_name}

SQL;
        $results = $this->o_db->update($sql, $a_values, true);
        if ($results) {
            return true;
        }
        else {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            return false;
        }
    }

    /**
     * Deletes a single record based on the primary index value.
     * @param int    $record_id          Required
     * @return bool
     */
    protected function genericDelete($record_id = -1)
    {
        if ($record_id < 1 || !is_numeric($record_id)) {
            return false;
        }
        $piname = $this->primary_index_name;
        $sql =<<<SQL
DELETE FROM {$this->db_table}
WHERE {$piname} = :{$piname}
SQL;
        $results = $this->o_db->delete($sql, [':' . $piname => $record_id], true);
        if ($results) {
            return true;
        }
        else {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            return false;
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
        $meth = __METHOD__ . '.';
        if (count($a_values) === 0 || count($a_allowed_keys) === 0) {
            return '';
        }
        $log_message = 'A Values:  ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $log_message = 'a_allowed_keys ' . var_export($a_allowed_keys, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

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
        if ($prefix != '') {
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
     * @param array $a_search_for        optional assoc array field_name=>field_value
     * @param array $a_search_parameters optional allows one to specify various settings
     * @param array $a_allowed_keys      optional list array
     *
     * @ref searchparams For more info on a_search_parameters.
     *
     * @return string $where
     */
    protected function buildSqlWhere(array $a_search_for = [], array $a_search_parameters = [], array $a_allowed_keys = [])
    {
        $meth = __METHOD__ . '.';
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
            $message = 'search pairs prepared: ' . var_export($a_search_for, true);
            $this->logIt($message, LOG_OFF, $meth . __LINE__);
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
            $where .= $where == '' ? "\n" : '';
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
     * Returns true/false if there are records.
     * @param array  $a_values The values for the read statement.
     * @return bool
     */
    protected function hasRecords($a_values = [])
    {
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                $results = $this->read($a_record);
                if (!is_array($results) || count($results) < 1) {
                    return false;
                }
            }
        }
        else {
            $results = $this->read($a_values);
            if (!is_array($results) || count($results) < 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $a_values
     * @return bool
     */
    protected function notEmptyArray($a_values) {
        if (!is_array($a_values) || count($a_values) < 1) {
            return false;
        }
        return true;
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
            $array[$key] = strpos($value, ':') === 0
                ? $value
                : ':' . $value;
        }
        return $array;
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
     * @return null
     */
    public function setErrorMessage($value = '')
    {
        if ($value == '') {
            $value = $this->o_db->retrieveFormatedSqlErrorMessage();
        }
        $this->error_message = $value;
    }

    /**
     * Retrieves the primary index field name from database and sets the class property.
     * @return null
     */
    protected function setPrimaryIndexName()
    {
        switch($this->db_type) {
            case 'pgsql':
                $query =<<<SQL
SELECT a.attname as pKeyName, format_type(a.atttypid, a.atttypmod) AS data_type
FROM   pg_index i
JOIN   pg_attribute a ON a.attrelid = i.indrelid
       AND a.attnum = ANY(i.indkey)
WHERE  i.indrelid = '{$this->db_table}'::regclass
AND    i.indisprimary;
SQL;
                $results = $this->o_db->rawQuery($query);
                if (!empty($results)) {
                    $this->primary_index_name = $results[0]['pKeyName'];
                }
                else {
                    $this->primary_index_name = '';
                }
                return null;
            case 'sqlite':
                $query = "PRAGMA table_info({$this->db_table})";
                $results = $this->o_db->rawQuery($query);
                if (!empty($results)) {
                    foreach ($results as $a_row) {
                        if ($a_row['pk'] === 1) {
                            $this->primary_index_name = $a_row['name'];
                            return null;
                        }
                    }
                }
                else {
                    $this->primary_index_name = '';
                }
                break;
            case 'mysql':
            default:
                $query = "SHOW index FROM {$this->db_table}";
                $results = $this->o_db->rawQuery($query);
                if (!empty($results)) {
                    foreach ($results as $a_index) {
                        if ($a_index['Key_name'] == 'PRIMARY') {
                            $this->primary_index_name = $a_index['Column_name'];
                            return null;
                        }
                    }
                }
                else {
                    $this->primary_index_name = '';
                }
            // end 'mysql' and default;
        }
    }

    /**
     * Sets up the standard properties.
     * @param \Ritc\Library\Services\DbModel $o_db
     * @param string                         $table_name
     * @return null
     */
    protected function setupProperties(DbModel $o_db, $table_name = '')
    {
        if ($o_db == '') {
            return null;
        }
        $this->o_db        = $o_db;
        $this->a_db_config = $o_db->getDbConfig();
        $this->db_prefix   = $o_db->getDbPrefix();
        $this->db_type     = $o_db->getDbType();
        if ($table_name != '') {
            $this->db_table    = $this->db_prefix . $table_name;
            $this->a_db_fields = $o_db->selectDbColumns($this->db_table);
            $this->setPrimaryIndexName();
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
     * Retrieves the class property $primary_index_name.
     * @return string
     */
    public function getPrimaryIndexName()
    {
        return $this->primary_index_name;
    }

}

