<?php
/**
 * @brief     Common functions that would be used in several database classes.
 * @ingroup   lib_traits
 * @file      DbUtilityTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-03-18 15:03:04
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.0   - initial version                                                                 - 2016-03-18 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\Arrays;

/**
 * Class DbUtilityTraits Methods that are generic and can be used in many Model classes.
 * @class DbUtilityTraits
 * @package Ritc\Library\Traits
 */
trait DbUtilityTraits {
    use DbTraits;

    /** @var  string */
    protected $error_message;
    /** @var \Ritc\Library\Services\DbModel */
    protected $o_db;

    #### Database Configuration Values ####
    /**
     * Gets the array $a_db_config which holds the config for the db.
     * @return array
     */
    protected function getDbConfig()
    {
        return $this->a_db_config;
    }

    /**
     * @return mixed
     */
    protected function getDbPrefix()
    {
        return $this->db_prefix;
    }

    /**
     * @return mixed
     */
    protected function getDbType()
    {
        return $this->db_type;
    }

    /**
     * @param string $value This method does nothing, intentionally.
     * @return null
     */
    protected function setDbConfig($value)
    {
        unset($value);
        return null; // db_config can only be set privately
    }

    /**
     * @param string $value
     * @return null
     */
    protected function setDbPrefix($value)
    {
        unset($value);
        return null; // db prefix can only be set privately
    }

    /**
     * @param string $value
     * @return null
     */
    protected function setDbType($value = '')
    {
        unset($value);
        return null; // db type can only be set privately
    }

    #### Generic CRUD calls ####
    /**
     * Generic method to create a new record in a table.
     * Requires both parameters to be complete to work.
     * @param array $a_values         The values to be saved in a new record.
     * @param array $a_parameters     [
     *                                 'a_required_keys' => [],
     *                                 'a_field_names' => [],
     *                                 'a_psql' => [
     *                                     'table_name'  => string,
     *                                     'column_name' => string
     *                                ]]
     * @return array|bool
     */
    protected function genericCreate(array $a_values = [], array $a_parameters = [])
    {
        $meth = __METHOD__ . '.';
        $a_values        = $this->o_db->prepareKeys($a_values);
        $db_table        = $a_parameters['psql']['table_name'];
        $a_required_keys = $a_parameters['a_required_keys'];
        $a_field_names   = $a_parameters['a_field_names'];
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            $a_missing_keys = $this->o_db->findMissingKeys($a_required_keys, $a_values);
            $this->error_message = "Missing required values: " . json_encode($a_missing_keys);
            return false;
        }
        $a_allowed_keys = $this->prepareListArray($a_field_names);
        $sql_set = $this->buildSqlInsert($a_values, $a_allowed_keys);
        $sql =<<<SQL
INSERT INTO {$db_table} (
{$sql_set}
)

SQL;
        $this->logIt("SQL: " . $sql, LOG_OFF, $meth . __LINE__);
        $a_psql = $a_parameters['a_psql'];
        $results = $this->o_db->insert($sql, $a_values, $a_psql);
        if ($results) {
            return $this->o_db->getNewIds();
        }
        return false;
    }

    /**
     * Generic method to read records from a table.
     * @param array $a_parameters Required ['table_name']
     *                            May also include:
     *                            - 'a_fields'        The fields to return ['id', 'name', 'is_alive'] or ['id' as 'id', 'name' as 'the_name', 'is_alive' as 'is_dead']
     *                            - 'a_search_for'    What to search for    ['id' => 3]
     *                            - 'return_format'   assoc, num, both - defaults to assoc
     *                            - 'order_by'        The order to return  'is_alive DESC, name ASC'
     *                            - 'search_type'     Either 'AND' | 'OR'
     *                            - 'limit_to'        Limit the number of records to return
     *                            - 'starting_from'   Which record number to start a limited return
     *                            - 'comparison_type' What kind of comparison operator to use for ALL WHEREs
     *                            - 'where_exists'    Either true or false
     * @return bool|array
     */
    protected function genericRead(array $a_parameters = [])
    {
        if (!isset($a_parameters['table_name'])) {
            return false;
        }
        else {
            $table_name = $a_parameters['table_name'];
        }
        $a_fields = isset($a_parameters['a_fields'])
            ? $a_parameters['a_fields']
            : $this->o_db->selectDbColumns($table_name);
        $a_search_for = isset($a_parameters['a_search_for'])
            ? $a_parameters['a_search_for']
            : [];
        $return_format = isset($a_parameters['return_format'])
            ? $a_parameters['return_format']
            : 'assoc';
        unset($a_parameters['table_name']);
        unset($a_parameters['a_search_for']);
        unset($a_parameters['return_format']);

        $select_me = $this->buildSqlSelectFields($a_fields);
        $where = $this->buildSqlWhere($a_search_for, $a_parameters);
        $sql =<<<SQL
SELECT {$select_me}
FROM {$table_name}
{$where}

SQL;
        return $this->o_db->search($sql, $a_search_for, $return_format);
    }

    /**
     * Generic method to update values in a table.
     * @param array $a_values     Required
     * @param array $a_parameter  Required ['table_name', 'id_field_name', 'record_id']
     * @return bool
     */
    protected function genericUpdate(array $a_values = [], array $a_parameter = [])
    {
        if (   !isset($a_parameter['table_name'])
            || !isset($a_parameter['id_field_name'])
            || !isset($a_parameter['record_id'])
            || count($a_values) < 1
        ) {
            return false;
        }
        return false;
    }

    /**
     * @param int   $record_id     Required
     * @param array $a_parameters  Required
     * @return bool
     */
    protected function genericDelete($record_id = -1, array $a_parameters = [])
    {
        if ($record_id < 1 || count($a_parameters) < 1) {
            return false;
        }
        return false;
    }
    #### Utility Methods ####
    /**
     * Creates a string that is part of an INSERT sql statement.
     * It produces a string in "prepared" format, e.g. ":fred" for the values.
     * It removes any pair whose key is not in the allowed keys array.
     * @param array $a_values       assoc array of values to that will be inserted.
     *                             It should be noted that only the key name is important,
     *                             but using the array of the actual values being inserted
     *                             ensures that only those values are inserted.
     * @param array $a_allowed_keys list array of key names that are allowed in the a_values array.
     * @return string
     */
    protected function buildSqlInsert(array $a_values = [], array $a_allowed_keys = [])
    {
        if (count($a_values) === 0 || count($a_allowed_keys) === 0) {
            return '';
        }
        $a_values = $this->prepareKeys($a_values);
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
     * as the key the name of the field, the value the name to be as, e.g.,
     * ['ngv' => 'general] becomes the string "ngv as 'general'"
     * @param array $a_values
     * @return string
     */
    protected function buildSqlSelectFields(array $a_values = [])
    {
        $select_me = '';
        if (Arrays::isAssocArray($a_values)) {
            foreach ($a_values as $name => $name_as) {
                $select_me .= $select_me == ''
                    ? $name . " as '" . $name_as . "'"
                    : ', ' . $name . " as '" . $name_as . "'";
            }
        }
        else {
            foreach ($a_values as $name) {
                $select_me .= $select_me == ''
                    ? $name
                    : ', ' . $name;
            }
        }
        return $select_me;
    }

    /**
     * Builds the SET part of an UPDATE sql statement.
     * Provides optional abilities to skip certain pairs and removed undesired pairs.
     * @param array $a_values required key=>value pairs
     *                       pairs are those to be used in the statement fragment.
     * @param array $a_skip_keys optional list of keys to skip in the set statement
     * @param array $a_allowed_keys optional list of allowed keys to be in the values array.
     * @return string $set_sql
     */
    protected function buildSqlSet(array $a_values = [], array $a_skip_keys = ['nothing_to_skip'], array $a_allowed_keys = [])
    {
        if ($a_values == array()) { return ''; }
        $set_sql = '';
        $a_values = $this->prepareKeys($a_values);
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
     * @param array $a_search_for optional assoc array field_name=>field_value
     * @param array $a_search_parameters optional allows one to specify various settings
     * @param array $a_allowed_keys optional list array
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
        /* set the $key to have a value compatible for a prepared statement */
        if (count($a_search_for) > 0) {
            $a_search_for = $this->prepareKeys($a_search_for);
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
     * Finds missing or empty values for given key => value pair
     * @param array $a_required_keys required list of keys that need to have values
     * @param array $a_pairs
     * @return array $a_keys list of the the keys that are missing values
     */
    protected function findMissingValues(array $a_required_keys = array(), array $a_pairs = array())
    {
        if ($a_pairs == array() || $a_required_keys == array()) { return false; }
        $a_keys = array();
        foreach ($a_pairs as $key => $value) {
            if (
                array_key_exists($key, $a_required_keys)
                ||
                array_key_exists(':' . $key, $a_required_keys)
                ||
                array_key_exists(str_replace(':', '', $key), $a_required_keys)
            )
            {
                if ($value == '' || is_null($value)) {
                    $a_keys[] = $key;
                }
            }
        }
        return $a_keys;
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

}
