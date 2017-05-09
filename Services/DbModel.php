<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @details   For read/write access to the database based on PDO.
 * @ingroup   lib_services
 * @file      DbModel.php
 * @namespace Ritc\Library\Services
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   4.3.0
 * @date      2017-05-09 18:28:08
 * @note <b>Change Log</b>
 * - v4.3.0 - Added a new method to check if a table exists in the database                 - 2017-05-09 wer
 * - v4.2.0 - Added some debugging code, cleaned up other.                                  - 2017-01-13 wer
 * - v4.1.2 - Bug fix                                                                       - 2016-08-22 wer
 * - v4.1.1 - Bug fixes, the set/create/retrieve sql error message needed clarification     - 2016-03-28 wer
 * - v4.1.0 - Renamed rawQuery to rawExec and added a new rawQuery                          - 2016-03-25 wer
 *            The old rawQuery was doing a \PDO::exec command which doesn't return
 *            results of a SELECT. So, to avoid confusion it was renamed and
 *            a new rawQuery was created using the \PDO::query command which does
 *            allow returning the results of a SELECT via a PDOStatement::fetchAll.
 *            Also did a couple bug fixes.
 * - v4.0.0 - Changed class so that it focues on the actual database operations.            - 2016-03-18 wer
 *            Removed sql building and validation methods to a trait class which
 *            then can be used independently by other classes. Yes, this may require
 *            a lot of refactoring elsewhere.
 * - v3.6.1 - bug fixes                                                                     - 2016-03-17 wer
 * - v3.6.0 - Changed property name o_db to o_pdo to clarify what the object was            - 03/04/2016 wer
 *            - Added new method to "prepare" a list array
 *            - Added new method to create and return the formated sql error message.
 *            - Added new method to return the raw sql error info array.
 *            - Updated update set build method to block unallowed key value pairs
 *            - bug fixes, build insert sql didn't take into account prepared key names
 *            - Misc other fixes and modifications.
 * - v3.5.1 - added new method to create a string for the select field names                - 02/25/2016 wer
 * - v3.5.0 - added new method to create a string for the insert value names                - 02/23/2016 wer
 * - v3.4.0 - changed so that an array can be used in place of the preferred file           - 12/09/2015 wer
 * - v3.3.0 - bug fix - pgsql insert wasn't working right                                   - 11/22/2015 wer
 * - v3.2.6 - changed from extending Base class to using traits                             - unknown    wer
 * - v3.2.5 - added some additional information to be retrieved                             - 09/01/2015 wer
 * - v3.2.4 - refactoring elsewhere made a small name change here                           - 07/31/2015 wer
 * - v3.2.3 - moved to Services namespace                                                   - 11/15/2014 wer
 * - v3.2.1 - bug fix
 * - v3.2.0 - Made this class more stand alone except extending Base class.
 *            Added function to allow raw query.
 *            Changed it to use the new Base class elog inject method.
 *            Hammering down a couple bugs.
 * - v3.1.2 - bug fixes, needed to pass the pdo object into the class                       - 03/20/2014 wer
 * - v3.1.1 - added methods to set and return db prefix                                     - 02/24/2014 wer
 *            It should be noted, this assumes a db prefix has been set. see PdoFactory
 * - v3.1.0 - added method to return db tables                                              - 01/31/2014 wer
 * - v3.0.1 - renamed file to match function, eliminated the unnecessary                    - 12/19/2013 wer
 * - v3.0.0 - split the pdo creation (database connection) from the crud                    - 2013-11-06 wer
 * - v2.4.4 - bug fix in buildSqlWhere                                                      - 2013-07-23 wer
 * - v2.4.3 - reverted back to RITC Library only (removed Symfony specific stuff)           - 07/06/2013 wer
 * - v2.4.2 - added method to build sql where                                               - 05/09/2013 wer
 * - v2.4.1 - modified a couple methods to work with pgsql 05/08/2013
 * - v2.4.0 - Change to match new RITC Library layout                                       - 04/23/2013 wer
 * - v2.3.2 - new method to remove bad keys
 *            removed some redundant code
 *            reorganized putting main four commands at top for easy reference
 *            renamed from modify to update, no good reason truthfully except no legacy code to support
 * - v2.3.1 - new method to check for missing keys
 *            made a couple changes to clarify what was going on.
 * - v2.3.0 - Modified to work within Symfony
 * - v2.2.0 - FIG-standard changes
 */
namespace Ritc\Library\Services;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Traits\DbTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class DbModel Makes using the PDO stuff easier.
 * @class DbModel
 * @package Ritc\Library\Services
 */
class DbModel
{
    use DbTraits, LogitTraits;

    /** @var array */
    private $a_new_ids = [];
    /** @var int */
    private $affected_rows;
    /** @var \PDO */
    private $o_pdo;
    /** @var string */
    private $pgsql_sequence_name = '';
    /** @var mixed */
    private $sql_error_message;
    /** @var int */
    private $success;

    /**
     * On creating a new object, certain things happen.
     * @param  \PDO   $o_pdo       can be from PdoFactory or from a direct new PDO
     *                             This allows it to be independent of the PdoFactory.
     * @param  string $config_file name of the config file which is a returned array
     */
    public function __construct(\PDO $o_pdo, $config_file = 'db_config.php')
    {
        $this->createDbParams($config_file);
        $this->o_pdo = $o_pdo;
    }

    ### Main Four Commands (CRUD) ###
    /**
     * Inserts data into the database.
     * @param string $the_query the INSERT statement, default is empty.
     * @param array $a_values     default is empty array
     *                            If blank, the values are in the INSERT string
     *                            If array then the INSERT string is for a prepared query
     * @param array @a_table_info needed only if PostgreSQL is being used, Default array()
     *                            ['table_name' => '', 'column_name' => '', 'schema_name' => '']
     * @return bool
     * @return bool
     */
    public function insert($the_query = '', array $a_values = [], array $a_table_info = [])
    {
        $meth = __METHOD__ . '.';
        $this->logIt('Query and values: ' . $the_query . '  ' . var_export($a_values, TRUE), LOG_OFF, $meth . __LINE__);
        if ($the_query == '') {
            $this->logIt('The query must not be blank.', LOG_ALWAYS, $meth . __LINE__);
            return false;
        }
        $sequence_name = '';
        if ($this->db_type == 'pgsql' && $a_table_info != array()) {
            $sequence_name = $this->getPgsqlSequenceName($a_table_info);
        }
        $this->logIt('Sequence Name: ' . $sequence_name, LOG_OFF, $meth . __LINE__);
        if (count($a_values) == 0) {
            $this->affected_rows = $this->o_pdo->exec($the_query);
            if ($this->affected_rows === false) {
                $this->logIt($this->retrieveFormatedSqlErrorMessage(), LOG_ALWAYS, $meth . __LINE__);
                return false;
            }
            elseif ($this->affected_rows == 0) {
                $this->logIt('The INSERT affected no records.', LOG_ALWAYS, $meth . __LINE__);
                return false;
            }
            else { // note: kind of assumes there was a single record inserted
                $this->a_new_ids = array($this->o_pdo->lastInsertId($sequence_name));
                return true;
            }
        }
        elseif (count($a_values) > 0) {
            $o_pdo_stmt = $this->prepare($the_query);
            if ($o_pdo_stmt === false) {
                $this->logIt('Could not prepare the statement', LOG_ALWAYS, $meth . __LINE__);
                return false;
            }
            else {
                $this->logIt('Sending to insert Prepared.', LOG_OFF, $meth . __LINE__);
                return $this->insertPrepared($a_values, $o_pdo_stmt, $a_table_info);
            }
        }
        else {
            $this->logIt('The array of values for a prepared insert was empty.', LOG_OFF, $meth . __LINE__);
            return false;
        }
    }

    /**
     * Searches the database for records.
     * Can be set up with upto 3 arguments, the first required, the sql
     * @param string $the_query, required
     * @param array $a_values associative array, key in named prepared
     *     format preferred e.g., [':id'=>1, ':name'=>'fred'] but optional.
     * @param string $type optional, type of results, num, both, assoc which
     *     specifies the PDO formats, defaults to assoc
     * @return mixed results of search or false
     */
    public function search($the_query = '', array $a_values = [], $type = 'assoc')
    {
        $meth = __METHOD__ . '.';
        switch ($type) {
            case 'num':
                $fetch_style = \PDO::FETCH_NUM;
                break;
            case 'both':
                $fetch_style = \PDO::FETCH_BOTH;
                break;
            case 'assoc':
            default:
                $fetch_style = \PDO::FETCH_ASSOC;
        }
        if ($the_query == '') {
            $this->logIt('The query must not be blank.', LOG_ALWAYS, $meth . __LINE__);
            return false;
        }
        if (count($a_values) == 0) {
            $o_pdo_stmt = $this->o_pdo->prepare($the_query);
            if ($o_pdo_stmt === false) {
                $this->logIt($this->retrieveFormatedSqlErrorMessage($this->o_pdo), LOG_ALWAYS, $meth . __LINE__);
                return false;
            }
            if ($o_pdo_stmt->execute()) {
                $a_results = $o_pdo_stmt->fetchAll($fetch_style);
                $o_pdo_stmt->closeCursor();
            }
            else {
                $a_error = $o_pdo_stmt->errorInfo();
                $log_message = 'Error array ' . var_export($a_error, TRUE);
                $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

                return false;
            }
        }
        elseif (is_array($a_values) && count($a_values) > 0) {
            $this->logIt("Query is: {$the_query}", LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->logIt("The array is " . var_export($a_values, true), LOG_OFF, $meth . __LINE__);

            $o_pdo_stmt = $this->prepare($the_query);
            if ($o_pdo_stmt) {
                $a_results = $this->searchPrepared($a_values, $o_pdo_stmt, $type);
                if ($a_results === false) {
                    $this->logIt($this->retrieveFormatedSqlErrorMessage($o_pdo_stmt), LOG_OFF, $meth . __LINE__);
                    return false;
                }
            }
            else {
                $this->logIt("Could not prepare the query " . $the_query, LOG_OFF, $meth . __LINE__);
                $this->logIt($this->retrieveFormatedSqlErrorMessage($o_pdo_stmt), LOG_OFF, $meth . __LINE__);
                return false;
            }
        }
        else {
            $this->logIt("There was a problem with the array", LOG_OFF, $meth . __LINE__);
            $this->logIt("a_values is: " . var_export($a_values , true), LOG_OFF, $meth . __LINE__);
            return false;
        }
        return $a_results;
    }

    /**
     * Executes a query to modify one or more records.
     * This is a stub. It executes the $this->mdQuery method
     * @param string $the_query     default ''
     * @param array  $a_values      associative array with paramaters default empty array
     * @param bool   $single_record default true specifies if only a single record should be deleted per query
     * @return bool                 success or failure
     */
    public function update($the_query = '', array $a_values = [], $single_record = true)
    {
        return $this->mdQuery($the_query, $a_values, $single_record);
    }

    /**
     * Executes a query to delete one or more records.
     * This is a stub. It executes the $this->mdQuery method.
     * @param string $the_query
     * @param array  $a_values      associative array with where paramaters
     * @param bool   $single_record specifies if only a single record should be deleted per query
     * @return bool                 success or failure
     */
    public function delete($the_query = '', array $a_values = [], $single_record = true)
    {
        return $this->mdQuery($the_query, $a_values, $single_record);
    }

    /**
     * Allows a raw \PDO::exec sql statement to be made.
     * As specified by \PDO, this does not return results from a select statement.
     * The query must be properly escaped, otherwise, this could be vulnerable.
     * @param string $the_query
     * @return int
     */
    public function rawExec($the_query)
    {
        return $this->o_pdo->exec($the_query);
    }

    /**
     * Executes and returns the results of a \PDO::query.
     * The query must be properly escaped, otherwise, this could be vulnerable.
     * @param string $the_query
     * @return array
     */
    public function rawQuery($the_query = '')
    {
        if ($the_query == '') {
            return [];
        }
        $pdo_stmt = $this->o_pdo->query($the_query);
        if ($pdo_stmt !== false) {
            return $pdo_stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return [];
    }

    ### Getters and Setters
    /**
     * Get the value of the property specified.
     * @param string $var_name
     * @return mixed value of the property
     * @note - this is normally set to private so not to be used
     */
    protected function getVar($var_name)
    {
        return $this->$var_name;
    }

    /**
     * GETter for the class property affected_rows.
     * @return mixed
     */
    public function getAffectedRows()
    {
        return $this->affected_rows;
    }

    /**
     * GETter for the class property a_new_ids.
     * @return array
     */
    public function getNewIds()
    {
        return $this->a_new_ids;
    }

    /**
     * GETter for the class property pgsql_sequence_name.
     * Also sets the class property if array is provided.
     * @param array $a_table_info
     * @return string
     */
    public function getPgsqlSequenceName(array $a_table_info = [])
    {
        if ($a_table_info != array()) {
            $this->setPgsqlSequenceName($a_table_info);
        }
        return $this->pgsql_sequence_name;
    }

    /**
     * GETter for class property success.
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * GETter for class property sql_error_message.
     * @return mixed
     */
    public function getSqlErrorMessage()
    {
        return $this->sql_error_message;
    }

    /**
     * SETter for an individual element of the class property array a_new_ids.
     * @param string $value
     * @return bool
     */
    public function setNewId($value = '')
    {
        if ($value !== '') {
            $this->a_new_ids[] = $value;
        }
        return true;
    }

    /**
     * Get and save the sequence name for a pgsql table in the protected property $pgsql_sequence_name.
     * @param array $a_table_info  ['table_name', 'column_name', 'schema']
     * @note \verbatim
     * 'table_name'  value required,
     * 'column_name' value optional but recommended, defaults to 'id'
     * 'schema'      value optional, defaults to 'public' \endverbatim
     * @return bool success or failure
     */
    public function setPgsqlSequenceName(array $a_table_info = [])
    {
        if ($a_table_info == array()) {
            return false;
        }
        if (!isset($a_table_info['table_name']) || $a_table_info['table_name'] == '') {
            return false;
        }
        if (!isset($a_table_info['column_name']) || $a_table_info['column_name'] == '') {
            $a_table_info['column_name'] = 'id';
        }
        if (!isset($a_table_info['schema']) || $a_table_info['schema'] == '') {
            $a_table_info['schema'] = 'public';
        }
        $query = "
            SELECT column_default
            FROM information_schema.columns
            WHERE table_schema = :schema
            AND table_name = :table_name
            AND column_name = :column_name";
        $results = $this->search($query, $a_table_info);
        if ($results) {
            $this->logIt("Results: " . var_export($results, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            $column_default = $results[0]['column_default'];
            $this->pgsql_sequence_name = preg_replace("/nextval\('(.*)'(.*)\)/i", '$1', $column_default);
            $this->logIt("pgsql_sequence_name: " . $this->pgsql_sequence_name, LOG_OFF, __METHOD__ . '.' . __LINE__);
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Sets the class propery error_message to a formated string.
     * @param $pdo null|\PDO|\PDOStatement
     */
    public function setSqlErrorMessage($pdo = null)
    {
        if ($pdo instanceof \PDOStatement || $pdo instanceof \PDO) {
            $a_error_stuff = $pdo->errorInfo();
        }
        else {
            $a_error_stuff = $this->o_pdo->errorInfo();
        }
        $this->sql_error_message = 'SQLSTATE Error Code: ' . $a_error_stuff[0] .
            "\nDriver Error Code: " . $a_error_stuff[1] .
            "\nDriver Error Message: " . $a_error_stuff[2];
    }

    /**
     * Sets and gets the sql_error_message property.
     * @param $pdo null|\PDO|\PDOStatement
     * @return string
     */
    public function retrieveFormatedSqlErrorMessage($pdo = null)
    {
        if (!is_null($pdo)) {
            $this->setSqlErrorMessage($pdo);
        }
        elseif ($this->sql_error_message == '') {
            $this->setSqlErrorMessage();
        }
        return $this->sql_error_message;
    }

    /**
     * Retrieves the raw sql errors.
     * In the format of ['SQLSTATE Error Code', 'Driver Error Code', 'Driver Error Message'].
     * @param $pdo null|\PDO|\PDOStatement
     * @return array
     */
    public function retrieveRawSqlErrorInfo($pdo = null)
    {
        if ($pdo instanceof \PDOStatement || $pdo instanceof \PDO) {
            return $pdo->errorInfo();
        }
        else {
            return $this->o_pdo->errorInfo();
        }
    }

    /**
     * A setter of the $a_new_ids property
     * @return void
     */
    public function resetNewIds()
    {
        $this->a_new_ids = [];
    }

    ### Basic Commands - The basic building blocks for doing db work
    /**
     * Bind values from an assoc array to a prepared query.
     * @param array                $a_values    Keys must match the prepared query
     * @param object|\PDOStatement $o_pdo_stmt
     * @return bool - success or failure
     */
    public function bindValues(array $a_values = [], \PDOStatement $o_pdo_stmt)
    {
        $meth = __METHOD__ . '.';
        $this->logIt("bind array: " . var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        if (Arrays::isAssocArray($a_values)) {
            $a_values = $this->prepareKeys($a_values);
            $this->logIt("prepared array: " . var_export($a_values, true), LOG_OFF, $meth . __LINE__);
            foreach ($a_values as $key => $value) {
                if (is_array($key) || is_array($value)) { return false; }
                if ($o_pdo_stmt->bindValue($key, $value) === false) {
                    $a_error = $o_pdo_stmt->errorInfo();
                    $this->logIt($a_error[2], LOG_OFF, $meth . __LINE__);
                    return false;
                }
            }
            return true;
        }
        elseif (count($a_values) > 0) {
            $this->logIt('binding a basic array', LOG_OFF, $meth . __LINE__);
            $this->logIt($a_values[0], LOG_OFF, $meth . __LINE__);
            $x = 1;
            foreach ($a_values as $value) {
                if ($o_pdo_stmt->bindValue($x++, $value) === false) {
                    $a_error = $o_pdo_stmt->errorInfo();
                    $this->logIt($a_error[2], LOG_OFF, $meth . __LINE__);
                    return false;
                }
                $this->logIt("Successful Binding of {$value}", LOG_OFF, $meth . __LINE__);
            }
            return true;
       }
        else {
            $this->logIt('The value passed into bindValues must be an array.', LOG_OFF, $meth . __LINE__);
            return false;
        }
    }

    /**
     * Shortcut for PDOStatement::closeCursor().
     * @param \PDOStatement $o_pdo_stmt
     * @return bool
     */
    public function closeCursor(\PDOStatement $o_pdo_stmt)
    {
        return $o_pdo_stmt->closeCursor();
    }

    /**
     * Commits the PDO transaction.
     * @return bool
     */
    public function commitTransaction()
    {
        return $this->o_pdo->commit();
    }

    /**
     * Executes a prepared query
     * @param array $a_values <pre>
     *       $a_values could be:
     *       array("test", "brains") for question mark place holders prepared statement
     *       array(":test"=>"test", ":food"=>"brains") for named parameters prepared statement
     *       '' when the values have been bound before calling this method
     * @param object|\PDOStatement $o_pdo_stmt - the object created from the prepare
     * @return bool - success or failure
     */
    public function execute(array $a_values = [], \PDOStatement $o_pdo_stmt)
    {
        $meth = __METHOD__ . '.';
        $this->logIt('Array: ' . var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        if (count($a_values) > 0) {
            if (Arrays::isAssocArray($a_values)) { // for a query with bind values
                $a_values = $this->prepareKeys($a_values);
                $this->logIt('Fixed Array: ' . var_export($a_values, true), LOG_OFF, $meth . __LINE__);

                if ($this->bindValues($a_values, $o_pdo_stmt) === false) {
                    $this->logIt("Could not bind the values.", LOG_OFF, $meth . __LINE__);
                    return false;
                }
                return $o_pdo_stmt->execute();
            }
            elseif (isset($a_values[0]) && is_array($a_values[0])) { // is an array of arrays
                $this->logIt('The array cannot be an array of array.', LOG_ALWAYS, $meth . __LINE__);
                return false;
            }
            else { // $array is for question mark place holders prepared statement
                $this->logIt("Attempting to execute a question mark place prepared statement", LOG_OFF, $meth . __LINE__);
                if ($this->bindValues($a_values, $o_pdo_stmt) === false) {
                    return false;
                }
                return $o_pdo_stmt->execute();
            }
        }
        else {
            $this->logIt('Executing a query with pre-bound values', LOG_OFF, $meth . __LINE__);
            return $o_pdo_stmt->execute(); // values have been bound elsewhere
        }
    }

    /**
     * Executes the pdo fetch method
     *
     * @param object|\PDOStatement $o_pdo_stmt     a \PDOStatement object
     * @param array                $a_fetch_config array('fetch_style'=>'ASSOC', 'cursor_orientation'=>'', 'cursor_offset'=>0)
     *
     * @return mixed - depends on fetch_style, always return false on failure - @see \PDOStatment::fetch
     */
    public function fetchRow(\PDOStatement $o_pdo_stmt, array $a_fetch_config = [])
    {
        if ($a_fetch_config == array()) {
            $fetch_style = 'ASSOC';
            $cursor_orientation = \PDO::FETCH_ORI_NEXT;
            $cursor_offset = 0;
        }
        else {
            $fetch_style = $a_fetch_config['fetch_style'] != ''
                ? $a_fetch_config['fetch_style']
                : 'ASSOC';
            $cursor_orientation = $a_fetch_config['cursor_orientation'] != ''
                ? $a_fetch_config['cursor_orientation']
                : \PDO::FETCH_ORI_NEXT;
            $cursor_offset = $a_fetch_config['cursor_offset'] != ''
                ? $a_fetch_config['cursor_offset']
                : 0;
        }
        switch ($fetch_style) {
            case 'BOTH':
                return $o_pdo_stmt->fetch(\PDO::FETCH_BOTH, $cursor_orientation, $cursor_offset);
                break;
            case 'BOUND':
                return $o_pdo_stmt->fetch(\PDO::FETCH_BOUND, $cursor_orientation, $cursor_offset);
                break;
            case 'CLASS':
                return $o_pdo_stmt->fetch(\PDO::FETCH_CLASS, $cursor_orientation, $cursor_offset);
                break;
            case 'INTO':
                return $o_pdo_stmt->fetch(\PDO::FETCH_INTO, $cursor_orientation, $cursor_offset);
                break;
            case 'LAZY':
                return $o_pdo_stmt->fetch(\PDO::FETCH_LAZY, $cursor_orientation, $cursor_offset);
                break;
            case 'NUM':
                return $o_pdo_stmt->fetch(\PDO::FETCH_NUM, $cursor_orientation, $cursor_offset);
                break;
            case 'OBJ':
                return $o_pdo_stmt->fetch(\PDO::FETCH_OBJ, $cursor_orientation, $cursor_offset);
                break;
            case 'ASSOC':
            default:
                return $o_pdo_stmt->fetch(\PDO::FETCH_ASSOC, $cursor_orientation, $cursor_offset);
        }
    }

    /**
     * Prepares a sql statement for execution
     *
     * @param object|\PDOStatement $o_pdo_stmt  a \PDOStatement object
     * @param string               $fetch_style @see \PDO (optional)
     *
     * @return array if successful, else false
     */
    public function fetch_all(\PDOStatement $o_pdo_stmt, $fetch_style = 'ASSOC')
    {
        switch ($fetch_style) {
            case 'BOTH':
                return $o_pdo_stmt->fetchAll(\PDO::FETCH_BOTH);
                break;
            case 'BOUND':
                return $o_pdo_stmt->fetchAll(\PDO::FETCH_BOUND);
                break;
            case 'CLASS':
                return $o_pdo_stmt->fetchAll(\PDO::FETCH_CLASS);
                break;
            case 'INTO':
                return $o_pdo_stmt->fetchAll(\PDO::FETCH_INTO);
                break;
            case 'LAZY':
                return $o_pdo_stmt->fetchAll(\PDO::FETCH_LAZY);
                break;
            case 'NUM':
                return $o_pdo_stmt->fetchAll(\PDO::FETCH_NUM);
                break;
            case 'OBJ':
                return $o_pdo_stmt->fetchAll(\PDO::FETCH_OBJ);
                break;
            case 'ASSOC':
            default:
                return $o_pdo_stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * @param string $the_query
     * @param string $cursor
     * @return bool|\PDOStatement
     */
    public function prepare($the_query = '', $cursor = '')
    {
        $this->logIt("Query passed in: {$the_query}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($the_query != '') {
            switch ($cursor) {
                case 'SCROLL':
                    $o_pdo_stmt = $this->o_pdo->prepare($the_query, array(\PDO::ATTR_CURSOR =>  \PDO::CURSOR_SCROLL));
                    break;
                case 'FWDONLY':
                default:
                    $o_pdo_stmt = $this->o_pdo->prepare($the_query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            }
            if ($o_pdo_stmt !== false) {
                $this->logIt('Success for prepare.', LOG_OFF, __METHOD__ . '.' . __LINE__);
                $this->logIt('o pdo stmt: ' . var_export($o_pdo_stmt, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return $o_pdo_stmt;
            }
            else {
                $this->logIt('Failure for prepare.', LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            }
        }
        else {
            $this->logIt('The query must not be blank.', LOG_OFF);
            return false;
        }
    }

    /**
     * Rolls back a PDO transaction.
     * @return bool
     */
    public function rollbackTransaction()
    {
        return $this->o_pdo->rollBack();
    }

    /**
     * Executes PDOStatement::rowCount().
     * @param \PDOStatement $o_pdo_stmt
     * @return int
     */
    public function rowCount(\PDOStatement $o_pdo_stmt)
    {
        return $o_pdo_stmt->rowCount();
    }

    /**
     * Starts a PDO transaction.
     * @return bool
     */
    public function startTransaction()
    {
        return $this->o_pdo->beginTransaction();
    }

    ### Complete Transaction in a single command
    /**
     * Does an insert statement wrapped in a transaction.
     * @param string $the_query
     * @param array  $a_values
     * @param string $table_name
     * @return bool
     */
    public function insertTransaction($the_query = '', array $a_values = [], $table_name = '')
    {
        if ($the_query == '') {
            $this->logIt('The Query was blank.', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return false;
        }
        if ($this->o_pdo->beginTransaction()) {
            $results = $this->insert($the_query, $a_values, $table_name);
            if ($results) {
                if ($this->o_pdo->commit() === false) {
                    $message = 'Could Not Commit the Transaction.';
                }
                else {
                    return true;
                }
            }
            else {
                $message = 'Could Not Successfully Do the Insert.';
            }
        }
        else {
            $message = 'Could not start transaction so we could not execute the insert, Please Try Again.';
        }
        $this->setSqlErrorMessage($this->o_pdo);
        $this->logIt($this->getSqlErrorMessage(), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->o_pdo->rollBack();
        $this->logIt($message);
        return false;
    }

    /**
     * Does a query wrapped in a transaction.
     * @param string $the_query
     * @param array  $the_array
     * @param bool   $single_record
     * @return bool
     */
    public function queryTransaction($the_query = '', array $the_array = [], $single_record = true)
    {
        $this->logIt("The Query is: {$the_query}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($this->o_pdo->beginTransaction()) {
            $results = $this->mdQuery($the_query, $the_array, $single_record);
            if ($results) {
                if ($this->o_pdo->commit() === false) {
                    $message = 'Could Not Commit the Transaction.';
                }
                else {
                    return true;
                }
            }
            else {
                $message = 'Could Not Successfully Do the Query.';
            }
        }
        else {
            $message = 'Could not start transaction so we could not execute the query, Please Try Again.';
        }
        $this->setSqlErrorMessage($this->o_pdo);
        $this->logIt($message . ' ==> ' . $this->getSqlErrorMessage(), LOG_OFF);
        $this->rollbackTransaction();
        return false;
    }

    /**
     * Does an update wrapped in a transaction.
     * @param string $the_query
     * @param array  $the_array
     * @param bool   $single_record
     * @return bool
     */
    public function updateTransaction($the_query = '', array $the_array = [], $single_record = true)
    {
        $this->logIt("The query coming in is: $the_query", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->queryTransaction($the_query, $the_array, $single_record);
        if ($results === false) {
            $this->logIt("Could not modify the record(s) - query was {$the_query}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        }
        return $results;
    }

    /**
     * Does a delete wrapped in a transaction.
     * @param string $the_query
     * @param array  $the_array
     * @param bool   $single_record
     * @return bool
     */
    public function deleteTransaction($the_query = '', array $the_array = [], $single_record = true)
    {
        $results = $this->queryTransaction($the_query, $the_array, $single_record);
        if ($results === false) {
            $this->logIt("Could not delete the record(s)", LOG_OFF, __METHOD__ . '.' . __LINE__);
        }
        return $results;
    }

    ### Complex Commands
    /**
     * Does an insert based on a prepared query.
     * @param array          $a_values   the values to be insert
     * @param \PDOStatement  $o_pdo_stmt the object pointing to the prepared statement
     * @param array          $a_table_info
     * @return bool success or failure
     */
    public function insertPrepared(array $a_values = [], \PDOStatement $o_pdo_stmt, array $a_table_info = [])
    {
        $meth = __METHOD__ . '.';
        if (count($a_values) > 0) {
            $this->logIt("" . var_export($a_values , true), LOG_OFF, $meth . __LINE__);
            $this->resetNewIds();
            $results = $this->executeInsert($a_values, $o_pdo_stmt, $a_table_info);
            if ($results === false) {
                $this->logIt('Execute Failure', LOG_OFF, $meth . __LINE__);
                $this->logIt('PDO: ' . $this->retrieveFormatedSqlErrorMessage($this->o_pdo), LOG_OFF, $meth . __LINE__);
                $this->logIt('PDO_Statement: ' . $this->retrieveFormatedSqlErrorMessage($o_pdo_stmt), LOG_OFF, $meth . __LINE__);
                $this->resetNewIds();
                return false;
            }
            return true;
        }
        else {
            $this->logIt('The array of values for a prepared insert was empty.', LOG_ALWAYS, $meth . __LINE__);
            $this->resetNewIds();
            return false;
        }
    }

    /**
     * Specialized version of execute which retains ids of each insert.
     *
     * @param array $a_values see $this->execute for details
     * @param \PDOStatement $o_pdo_stmt
     * @param array $a_table_info
     *
     * @return bool
     */
    public function executeInsert(array $a_values = [], \PDOStatement $o_pdo_stmt, array $a_table_info = [])
    {
        $meth = __METHOD__ . '.';
        if (count($a_values) > 0) {
            $sequence_name = $this->db_type == 'pgsql' && $a_table_info != array()
                ? $this->getPgsqlSequenceName($a_table_info)
                : '' ;
            $this->logIt('Sequence Name: ' . $sequence_name, LOG_OFF, $meth . __LINE__);
            if (isset($a_values[0]) && is_array($a_values[0])) { // is an array of arrays, can not be mixed
                foreach ($a_values as $a_stuph) {
                    if ($this->executeInsert($a_stuph, $o_pdo_stmt, $a_table_info) === false) {
                        $this->logIt($this->o_pdo->errorInfo(), LOG_ALWAYS, $meth . __LINE__);
                        return false;
                    }
                }
            }
            else { // should be a single record insert
                if ($this->execute($a_values, $o_pdo_stmt) === false) {
                    return false;
                }
                $this->a_new_ids[] = $this->o_pdo->lastInsertId($sequence_name);
            }
            return true;
        }
        else {
            $this->logIt('A non-empty array for its first parameter.', LOG_ALWAYS, $meth . __LINE__);
            return false;
        }
    }

    /**
     * Used for both modifying and deleting record(s)
     * @param string $the_query - the sql statement, required, default is ''
     * @param array $a_values - formated values for a prepared sql statement - optional, default is ''
     * @param $single_record - if only a single record should be changed/deleted - optional, default is true
     * @return bool - success or failure
     */
    public function mdQuery($the_query = '', array $a_values = [], $single_record = true)
    {
        $meth = __METHOD__ . '.';
        if ($the_query == '') {
            $this->logIt('The query must not be blank.', LOG_OFF, $meth);
            return false;
        }
        if ($a_values == array()) {
            $this->affected_rows = $this->o_pdo->exec($the_query);
            if ($this->affected_rows === false) {
                $this->setSqlErrorMessage($this->o_pdo);
                $this->logIt($this->getSqlErrorMessage(), LOG_OFF, $meth . __LINE__);
                return false;
            }
            elseif ($single_record && $this->affected_rows > 1) {
                $this->logIt('The query affected multiple records instead of a single one.', LOG_OFF, $meth);
                $this->sql_error_message = 'The query affected multiple records instead of a single one.';
                return false;
            }
            elseif ($this->affected_rows == 0) {
                $this->logIt('The query affected no records.', LOG_OFF, $meth);
                $this->sql_error_message = 'The query affected no records.';
                return false;
            }
            else {
                return true;
            }
        }
        elseif (count($a_values) > 0) {
            $o_pdo_stmt = $this->prepare($the_query);
            if (is_object($o_pdo_stmt) === false) {
                $this->logIt('Could not prepare the query: ' . $this->retrieveFormatedSqlErrorMessage($o_pdo_stmt), LOG_OFF, $meth . __LINE__);
                return false;
            }
            else {
                return $this->mdQueryPrepared($a_values, $single_record, $o_pdo_stmt);
            }
        }
        else {
            $this->logIt('The array of values for a prepared query was empty.', LOG_OFF, $meth);
            return false;
        }
    }

    /**
     * Executes a prepared sql statement, allowing for it to call itself for multiple records.
     * Question: why is it called mdQueryPrepared? You can tell this is an old method,
     * with its mysterious name.
     * @param array         $a_values
     * @param bool          $single_record
     * @param \PDOStatement $o_pdo_stmt
     * @return bool
     */
    public function mdQueryPrepared(array $a_values = [], $single_record = true, \PDOStatement $o_pdo_stmt)
    {
        $meth = __METHOD__ . '.';
        if ($a_values == array()) {
            return false;
        }
        if (count($a_values) > 0) {
            if (isset($a_values[0]) && is_array($a_values[0])) { // array of arrays
                foreach ($a_values as $row) {
                    $results = $this->mdQueryPrepared($row, $single_record, $o_pdo_stmt);
                    if ($results === false) {
                        $this->logIt("Could not execute the query: {$this->retrieveFormatedSqlErrorMessage($o_pdo_stmt)}", LOG_OFF, $meth . __LINE__);
                        $this->logIt('The array was: ' . var_export($a_values, true), LOG_OFF, $meth . __LINE__);
                        return false;
                    }
                }
                return true;
            }
            else {
                $results = $this->execute($a_values, $o_pdo_stmt);
                if ($results === false) {
                    $this->logIt("Could not execute the query: {$this->retrieveFormatedSqlErrorMessage($o_pdo_stmt)}", LOG_OFF, $meth . __LINE__);
                    $this->logIt('The array was: ' . var_export($a_values, true), LOG_OFF, $meth . __LINE__);
                    return false;
                }
                return true;
            }
        }
        else {
            $this->logIt('The array of values for a prepared query was empty.', LOG_OFF, $meth . __LINE__);
            return false;
        }
    }

    /**
     * Do a query.
     * Has two params. The first is required. The second is required if the first param doesn't include a valid sql statement.
     * @param $query_params (array) = default is empty str.  - required<pre>
     *     Should correspond to something like
     *     array('type'=>'search', 'table_name'=>'test_table', 'single_record'=>false, 'sql'=>'')</pre>
     * @param $data (mixed)<pre>
     *     array(field_name => value) - data to be insert or modified
     *     'id, name, date' (str) - a string of fields to be returned in a search</pre>
     * @param $where_values (array), array(field_name => value) - paramaters used to find records for search or modify
     * @return bool
     */
    public function query($query_params = '', $data = '', $where_values)
    {
        $default_params = array(
            'type'=>'',
            'table_name'=>'',
            'single_record'=>false,
            'sql' => '');
        if ($query_params == '') {
            $query_params = $default_params;
        }
        else {
            $query_params = array_merge($default_params, $query_params);
        }
        switch($query_params['type']) {
            case 'search': // can not build a JOIN so only complete sql statements as part of $query_params can do joins here
                if ($query_params['sql'] != '') {
                    return $this->search($query_params['sql'], $where_values);
                }
                $query = "
                    SELECT {$data} FROM {$query_params['table_name']}
                    WHERE ";
                $this->logIt("Where Params in method: " . var_export($where_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                $where = '';
                $a_where = [];
                foreach ($where_values as $values) {
                    $where .= $where != '' ? ' AND ' : '' ;
                    $where .= $values['field_name'] . $values['operator'] . ':' . $values['field_name'];
                    $a_where = array_merge($a_where, array($values['field_name'] =>$values['field_value']));
                }
                $query .= $where;
                $this->logIt("a where is: " . var_export($a_where, true),  LOG_OFF, __METHOD__ . '.' . __LINE__);
                $this->logIt("Query is: $query", LOG_OFF, __METHOD__ . '.' . __LINE__);
                return $this->search($query, $a_where);
            case 'add':
                if ($query_params['sql'] != '') {
                    return $this->insert($query_params['sql'], $data, $query_params['table_name']);
                }
                $query = "INSERT INTO {$query_params['table_name']} ";
                $field_names = '(';
                $values = '(';
                $field_data = $this->prepareValues($data);
                foreach($field_data as $field_name => $value) {
                    $field_names .= $field_names != '' ? ', ' : '';
                    $field_names .= $field_name;
                    $values .= $values != '' ? ', ' : '';
                    $values .= $field_name;
                }
                $field_names .= ')';
                $values .= ')';
                $query = $query . $field_names . ' VALUES ' . $values;
                $a_data = $this->prepareKeys($data);
                return $this->insert($query, $a_data, $query_params['table_name']);
            case 'modify':
                if ($query_params['sql'] != '') {
                    return $this->update($query_params['sql'], $data, $query_params['single_record']);
                }
                $query = "UPDATE {$query_params['table_name']} ";
                $set_lines = '';
                $field_names = $this->prepareValues($data);
                foreach ($field_names as $field_name => $value) {
                    $set_lines .= $set_lines != '' ? ', ' : 'SET ';
                    $set_lines .= $field_name . ' = ' . $value;
                }
                $where_values = $this->prepareValues($where_values);
                $where_lines = '';
                foreach ($where_values as $values) {
                    $where_lines .= $where_lines != '' ? ' AND ' : '' ;
                    $where_lines .= $values['field_name'] . $values['operator'] . $values['field_value'];
                }
                $query .= $set_lines . $where_lines;
                return $this->update($query, $data, $query_params['single_record']);
            case 'delete':
                if ($query_params['sql'] != '') {
                    return $this->delete($query_params['sql'], $data, $query_params['single_record']);
                }
                $query = "DELETE FROM {$query_params['table_name']} ";
                $where_names = $this->prepareValues($where_values);
                $where_lines = '';
                foreach ($where_names as $field_name => $value) {
                    $where_lines .= $where_lines != '' ? ' AND ' : ' WHERE ';
                    $where_lines .= $field_name . ' = ' . $value;
                }
                return $this->delete($query . $where_lines, $data, $query_params['single_record']);
            default:
                return false;
        }
    }

    /**
     * @param array         $a_values
     * @param \PDOStatement $o_pdo_stmt
     * @param string        $type
     * @return array|bool
     */
    public function searchPrepared(array $a_values = [], \PDOStatement $o_pdo_stmt, $type = 'assoc')
    {
        $meth = __METHOD__ . '.';
        switch ($type) {
            case 'num':
                $fetch_style = \PDO::FETCH_NUM;
                break;
            case 'both':
                $fetch_style = \PDO::FETCH_BOTH;
                break;
            case 'assoc':
            default:
                $fetch_style = \PDO::FETCH_ASSOC;
        }
        if (count($a_values) > 0) {
            $this->logIt("Array: " . var_export($a_values, true), LOG_OFF, $meth . __LINE__);
            if (isset($a_values[0]) && is_array($a_values[0])) {
                $a_results = [];
                foreach ($a_values as $row) {
                    if ($this->execute($row, $o_pdo_stmt)) {
                        $fetched = $o_pdo_stmt->fetchAll($fetch_style);
                        $a_results[] = $fetched;
                    }
                    else {
                        return false;
                    }
                }
            }
            else {
                if ($this->execute($a_values, $o_pdo_stmt)) {
                    $a_results = $o_pdo_stmt->fetchAll($fetch_style);
                    if ($a_results === false) {
                        $this->logIt($this->retrieveFormatedSqlErrorMessage($o_pdo_stmt), LOG_OFF, $meth . __LINE__);
                    }
                }
                else {
                    $this->logIt("Could not execute the query", LOG_OFF, $meth . __LINE__);
                    $message = $o_pdo_stmt->errorCode();
                    $this->logIt("Error Code: " . $message, LOG_OFF, $meth . __LINE__);
                    $this->logIt($this->retrieveFormatedSqlErrorMessage($o_pdo_stmt), LOG_OFF, $meth . __LINE__);
                    return false;
                }
            }
            return $a_results;
        }
        else {
            $this->logIt('There was a problem with the array');
            $this->logIt("a_values: " . var_export($a_values , true), LOG_OFF, $meth . __LINE__);
            return false;
        }
    }

    ### Utility Functions
    /**
     * Gets the variable for the fetch style
     * @param string $type
     * @return int
     */
    public function determineFetchStyle($type = 'assoc')
    {
        switch ($type) {
            case 'num':
                return \PDO::FETCH_NUM;
                break;
            case 'both':
                return \PDO::FETCH_BOTH;
                break;
            case 'assoc':
            default:
                return \PDO::FETCH_ASSOC;
        }
    }

    /**
     * Use the \PDO::quote function to make the string safe for use in a query.
     * Used only when not using a prepared sql statement.
     * @see \PDO::quote
     * @param $value (str)
     * @return string - quoted string
     */
    public function quoteString($value)
    {
        return $this->o_pdo->quote($value);
    }

    /**
     * Returns a list of the columns from a database table.
     * @param string $table_name name of the table
     * @return array|bool - field names
     */
    public function selectDbColumns($table_name = '')
    {
        if ($table_name != '') {
            $a_column_names = [];
            switch ($this->db_type) {
                case 'pgsql':
                    $sql = "
                        SELECT column_name
                        FROM information_schema.columns
                        WHERE table_name ='{$table_name}'
                    ";
                    $results = $this->search($sql);
                    foreach ($results as $row) {
                        $a_column_names[] = $row['column_name'];
                    }
                    return $a_column_names;
                    break;
                case 'sqlite':
                    $sql = "PRAGMA table_info({$table_name})";
                    $results = $this->search($sql);
                    foreach ($results as $row) {
                        $a_column_names[] = $row['Field'];
                    }
                    break;
                case 'mysql':
                default:
                    $sql = "SHOW COLUMNS FROM {$table_name}";
                    $results = $this->search($sql);
                    if (!empty($results)) {
                        foreach ($results as $row) {
                            $a_column_names[] = $row['Field'];
                        }
                    }
                // end both mysql and default
            }
            return $a_column_names;

        }
        else {
            $this->logIt('You must specify a table name for this to work.', LOG_OFF, __METHOD__);
            return false;
        }
    }

    /**
     * Selects the table names from the database.
     * @return array $a_table_names
     */
    public function selectDbTables()
    {
        switch ($this->db_type) {
            case 'pgsql':
                $sql = "
                    SELECT table_name
                    FROM information_schema.tables
                ";
                return $this->search($sql, array(), 'num');
            case 'sqlite':
                $sql = "
                    SELECT name
                    FROM sqlite_master
                    WHERE type='table'
                    ORDER BY name
                ";
                return $this->search($sql, array(), 'num');
            case 'mysql':
            default:
                $sql = "SHOW TABLES";
                return $this->search($sql, array(), 'num');
        }
    }

    /**
     * Checks to see if the table exists.
     * @param string $table_name
     * @return bool
     */
    public function tableExists($table_name = '')
    {
        switch ($this->db_type) {
            case 'pgsql':
                $sql = "
                    SELECT count(table_name) as count
                    FROM information_schema.tables 
                    WHERE table_name = '{$table_name}'
                    AND table_catalog = '{$this->a_db_config["name"]}'
                ";
                break;
            case 'sqlite':
                $sql = "
                    SELECT count(*) as count
                    FROM sqlite_master
                    WHERE type='table'
                    AND name='{$table_name}'
                ";
                break;
            case 'mysql':
            default:
                $sql = "
                    SELECT count(*) as count
                    FROM information_schema.tables 
                    WHERE TABLE_SCHEMA = '{$this->a_db_config["name"]}' 
                    AND TABLE_NAME = '{$table_name}'
                ";
        }

        $results = $this->search($sql, [], 'num');
        if (!empty($results)) {
            if ($results[0]['count'] > 0) {
                return true;
            }
        }
        return false;
    }

    ### Magic Method fix ###
    /**
     * Prevents cloning of the class.
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    ### LogitTraits Methods ###
    // logIt($message, $log_type, $location)
    // setElog(Elog $o_elog)
    // getElog();

    ### DbTraits Methods ###
    // retrieveDbConfig($config_file);
}
