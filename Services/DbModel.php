<?php /** @noinspection PhpUnreachableStatementInspection */
/**
 * Class DbModel
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Services;

use PDO;
use PDOException;
use PDOStatement;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Traits\DbTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class DbModel makes using the PDO stuff easier.
 * For read/write access to the database based on PDO.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v6.0.0
 * @date    2021-11-30 15:25:47
 * @change_log
 * - v6.0.0 - Updated for php 8 standards only                                              - 2021-11-30 wer
 * - v5.3.0 - Code clean up based on code inspections                                       - 2020-08-24 wer
 * - v5.2.2 - Bug fixes                                                                     - 2018-09-17 wer
 * - v5.2.0 - Method added to get the pdo object                                            - 2017-12-14 wer
 * - v5.1.3 - ModelException changes reflected here                                         - 2017-12-12 wer
 * - v5.1.0 - Method name change to match standard for method names							- 2017-10-18 wer
 * - v5.0.0 - Switch to throwing exceptions instead of returning false                      - 2017-06-12 wer
 * - v4.3.0 - Added a new method to check if a table exists in the database                 - 2017-05-09 wer
 * - v4.2.0 - Added some debugging code, cleaned up other.                                  - 2017-01-13 wer
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
 * - v3.6.0 - Changed property name o_db to o_pdo to clarify what the object was            - 03/04/2016 wer
 *            - Added new method to "prepare" a list array
 *            - Added new method to create and return the formated sql error message.
 *            - Added new method to return the raw sql error info array.
 *            - Updated update set build method to block unallowed key value pairs
 *            - bug fixes, build insert sql didn't take into account prepared key names
 *            - Misc other fixes and modifications.
 * - v3.5.0 - added new method to create a string for the insert value names                - 02/23/2016 wer
 * - v3.4.0 - changed so that an array can be used in place of the preferred file           - 12/09/2015 wer
 * - v3.3.0 - bug fix - pgsql insert wasn't working right                                   - 11/22/2015 wer
 * - v3.2.6 - changed from extending Base class to using traits                             - unknown    wer
 * - v3.2.5 - added some additional information to be retrieved                             - 09/01/2015 wer
 * - v3.2.4 - refactoring elsewhere made a small name change here                           - 07/31/2015 wer
 * - v3.2.3 - moved to Services namespace                                                   - 11/15/2014 wer
 * - v3.2.0 - Made this class more stand alone except extending Base class.
 *            Added function to allow raw query.
 *            Changed it to use the new Base class elog inject method.
 *            Hammering down a couple bugs.
 * - v3.1.1 - added methods to set and return db prefix                                     - 02/24/2014 wer
 *            It should be noted, this assumes a db prefix has been set. see PdoFactory
 * - v3.1.0 - added method to return db tables                                              - 01/31/2014 wer
 * - v3.0.0 - split the pdo creation (database connection) from the crud                    - 2013-11-06 wer
 * - v2.4.3 - reverted back to RITC Library only (removed Symfony specific stuff)           - 07/06/2013 wer
 * - v2.4.2 - added method to build sql where                                               - 05/09/2013 wer
 * - v2.4.0 - Change to match new RITC Library layout                                       - 04/23/2013 wer
 * - v2.3.0 - Modified to work within Symfony
 * - v2.2.0 - FIG-standard changes
 */
class DbModel
{
    use LogitTraits;
    use DbTraits;

    /** @var array $a_new_ids */
    private array $a_new_ids = [];
    /** @var int $affected_rows */
    private int $affected_rows;
    /** @var PDO $o_pdo */
    private PDO $o_pdo;
    /** @var string $pgsql_sequence_name */
    private string $pgsql_sequence_name = '';
    /** @var mixed $sql_error_message */
    private mixed $sql_error_message;
    /** @var int $success */
    private int $success;

    /**
     * On creating a new object, certain things happen.
     *
     * @param  PDO   $o_pdo        can be from PdoFactory or from a direct new PDO
     *                             This allows it to be independent of the PdoFactory.
     * @param string $config_file  name of the config file which is a returned array
     */
    public function __construct(PDO $o_pdo, string $config_file = 'db_config.php')
    {
        $this->createDbParams($config_file);
        $this->o_pdo = $o_pdo;
    }

    ### Main Four Commands (CRUD) ###

    /**
     * Inserts data into the database.
     *
     * @param string $the_query    the INSERT statement, default is empty.
     * @param array  $a_values     default is empty array
     *                             If blank, the values are in the INSERT string
     *                             If array then the INSERT string is for a prepared query
     * @param array  $a_table_info optional
     * @return bool
     * @throws ModelException
     * @internal param $array @a_table_info needed only if PostgreSQL is being used, Default array()
     *                             ['table_name' => '', 'column_name' => '', 'schema_name' => '']
     */
    public function insert(string $the_query = '', array $a_values = [], array $a_table_info = []):bool
    {
        $meth = __METHOD__ . '.';
        if ($the_query === '') {
            $this->error_message = 'The query must not be blank.';
            $this->logIt($this->error_message, LOG_ALWAYS, $meth . __LINE__);
            throw new ModelException($this->error_message, 120);
        }
        $sequence_name = '';
        if ($this->db_type === 'pgsql' && !empty($a_table_info)) {
            $sequence_name = $this->getPgsqlSequenceName($a_table_info);
        }
        if (count($a_values) === 0) {
            try {
                $this->affected_rows = $this->o_pdo->exec($the_query);
            }
            catch (PDOException $e) {
                $this->error_message = $e->getMessage();
                throw new ModelException($this->error_message, 110);
            }
            if ($this->affected_rows === 0) {
                $message = 'The INSERT affected no records.';
                $this->logIt($message, LOG_ALWAYS, $meth . __LINE__);
                throw new ModelException($message, 110);
            }

            // note: kind of assumes there was a single record inserted
            try {
                $id = $this->o_pdo->lastInsertId($sequence_name);
                $this->a_new_ids = [$id];
            }
            catch (PDOException $e) {
                $message = $e->getMessage();
                throw new ModelException($message, 110);
            }
        }
        elseif (count($a_values) > 0) {
            try {
                $o_pdo_stmt = $this->prepare($the_query);
                try {
                    $results = $this->insertPrepared($a_values, $o_pdo_stmt, $a_table_info);
                    if (!$results) {
                        throw new ModelException('Unable to insert record', 110);
                    }
                }
                catch (ModelException $e) {
                    $this->error_message = 'Could not insertPrepared. ' . $e->errorMessage();
                    $code = $e->getCode();
                    throw new ModelException($this->error_message, $code);
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Could not prepare the query. ' . $e->errorMessage();
                $code = $e->getCode();
                throw new ModelException($this->error_message, $code);
            }
        }
        else {
            $this->error_message = 'The array of values for a prepared insert was empty.';
            throw new ModelException($this->error_message, 110);
        }
        return true;
    }

    /**
     * Searches the database for records.
     * Can be set up with upto 3 arguments, the first required, the sql.
     *
     * @param string $the_query , required
     * @param array  $a_values  associative array, key in named prepared
     *                          format preferred e.g., [':id'=>1, ':name'=>'fred'] but optional.
     * @param string $type      optional, type of results, num, both, assoc which
     *                          specifies the PDO formats, defaults to assoc
     * @return array|bool results of search or false
     * @throws ModelException
     */
    public function search(string $the_query = '', array $a_values = [], string $type = 'assoc'): array|bool
    {
        $meth = __METHOD__ . '.';
        $fetch_style = $this->determineFetchStyle($type);
        if ($the_query === '') {
            $this->error_message = 'The query must not be blank.';
            $this->logIt($this->error_message, LOG_ALWAYS, $meth . __LINE__);
            throw new ModelException($this->error_message, 220);
        }
        if (count($a_values) === 0) {
            try {
                $o_pdo_stmt = $this->o_pdo->prepare($the_query);
            }
            catch (PDOException $e) {
                $this->error_message = $e->getMessage();
                throw new ModelException($this->error_message, 15);
            }

            try {
                $o_pdo_stmt->execute();
                try {
                    $a_results = $o_pdo_stmt->fetchAll($fetch_style);
                    $o_pdo_stmt->closeCursor();
                    return $a_results;
                }
                catch (PDOException $e) {
                    $this->error_message = $e->getMessage();
                    throw new ModelException($this->error_message, 18);
                }
            }
            catch (PDOException $e) {
                $this->error_message = $e->getMessage();
                throw new ModelException($this->error_message, 18);
            }
        }
        elseif (is_array($a_values) && count($a_values) > 0) {
            try {
                $o_pdo_stmt = $this->prepare($the_query);
                try {
                    return $this->searchPrepared($a_values, $o_pdo_stmt, $type);
                }
                catch (ModelException $e) {
                    $this->error_message = $e->errorMessage();
                    $code = $e->getCode();
                    throw new ModelException($e->errorMessage(), $code);
                }
            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                $code = $e->getCode();
                throw new ModelException($this->error_message, $code);
            }
        }
        else {
            $this->error_message = 'There was a problem with the array.';
            throw new ModelException($this->error_message, 220);
        }
    }

    /**
     * Executes a query to modify one or more records.
     * This is a stub. It executes the $this->mdQuery method.
     *
     * @param string $the_query     default ''
     * @param array  $a_values      associative array with paramaters default empty array
     * @param bool   $single_record default true specifies if only a single record should be deleted per query
     * @return bool success or failure
     * @throws ModelException
     */
    public function update(string $the_query = '', array $a_values = [], bool $single_record = true): bool
    {
        try {
            return $this->mdQuery($the_query, $a_values, $single_record);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage() . ': ' . $the_query, $e->getCode(), $e);
        }
    }

    /**
     * Executes a query to delete one or more records.
     * This is a stub. It executes the $this->mdQuery method.
     *
     * @param string $the_query
     * @param array  $a_values      associative array with where paramaters
     * @param bool   $single_record specifies if only a single record should be deleted per query
     * @return bool
     * @throws ModelException
     */
    public function delete(string $the_query = '', array $a_values = [], bool $single_record = true): bool
    {
        try {
            return $this->mdQuery($the_query, $a_values, $single_record);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Allows a raw \PDO::exec sql statement to be made.
     * As specified by \PDO, this does not return results from a select statement.
     * The query must be properly escaped, otherwise, this could be vulnerable.
     *
     * @param string $the_query
     * @return int
     * @throws ModelException
     */
    public function rawExec(string $the_query): int
    {
        try {
            return $this->o_pdo->exec($the_query);
        }
        catch (PDOException $e) {
            $this->error_message = 'Could not execute a PDO::exec operation.';
            throw new ModelException($this->error_message, 17, $e);
        }
    }

    /**
     * Executes and returns the results of a \PDO::query.
     * The query must be properly escaped, otherwise, this could be vulnerable.
     *
     * @param string $the_query
     * @return array
     * @throws ModelException
     */
    public function rawQuery(string $the_query = ''): array
    {
        if ($the_query === '') {
            $this->error_message = 'Missing the query to query.';
            throw new ModelException($this->error_message, 20);
        }
        try {
            $pdo_stmt = $this->o_pdo->query($the_query);
            try {
                return $pdo_stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            catch (PDOException $e) {
                $this->error_message = 'Unable to do a PDO::fetchAll operation.';
                throw new ModelException($this->error_message, 18, $e);
            }
        }
        catch (PDOException $e) {
            $this->error_message = 'Unable to do a PDO::query';
            throw new ModelException($this->error_message, 18, $e);
        }
    }

    ### Getters and Setters
    /**
     * Get the value of the property specified.
     *
     * @param string $var_name
     * @return mixed value of the property
     * @note - this is normally set to private so not to be used
     */
    protected function getVar(string $var_name): mixed
    {
        return $this->$var_name;
    }

    /**
     * GETter for the class property affected_rows.
     *
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->affected_rows;
    }

    /**
     * GETter for the class property a_new_ids.
     *
     * @return array
     */
    public function getNewIds():array
    {
        return $this->a_new_ids;
    }

    /**
     * Standard GETter for private property for the class.
     *
     * @return PDO
     */
    public function getPDO(): PDO
    {
        return $this->o_pdo;
    }

    /**
     * GETter for the class property pgsql_sequence_name.
     * Also sets the class property if array is provided.
     *
     * @param array $a_table_info
     * @return string
     */
    public function getPgsqlSequenceName(array $a_table_info = []):string
    {
        if ($a_table_info !== array()) {
            try {
                $this->setPgsqlSequenceName($a_table_info);
            }
            catch (ModelException $e) {
                $this->logIt('ModelException: ' . $e->errorMessage(), LOG_OFF, __METHOD__);
                return '';
            }
        }
        return $this->pgsql_sequence_name;
    }

    /**
     * GETter for class property success.
     *
     * @return int
     */
    public function getSuccess(): int
    {
        return $this->success;
    }

    /**
     * GETter for class property sql_error_message.
     *
     * @return mixed
     */
    public function getSqlErrorMessage(): mixed
    {
        return $this->sql_error_message;
    }

    /**
     * SETter for an individual element of the class property array a_new_ids.
     *
     * @param string $value
     * @return bool
     */
    public function setNewId(string $value = ''):bool
    {
        if ($value !== '') {
            $this->a_new_ids[] = $value;
        }
        return true;
    }

    /**
     * Get and save the sequence name for a pgsql table in the protected property $pgsql_sequence_name.
     *
     * @param array $a_table_info ['table_name', 'column_name', 'schema']
     * @return bool success or failure
     * @throws ModelException
     * @note \verbatim
     *     'table_name'  value required,
     *     'column_name' value optional but recommended, defaults to 'id'
     *     'schema'      value optional, defaults to 'public' \endverbatim
     */
    public function setPgsqlSequenceName(array $a_table_info = []): bool
    {
        if (empty($a_table_info) || empty($a_table_info['table_name'])) {
            $this->error_message = 'Missing required values.';
            throw new ModelException($this->error_message, 900);
        }
        if (!isset($a_table_info['column_name']) || $a_table_info['column_name'] === '') {
            $a_table_info['column_name'] = 'id';
        }
        if (!isset($a_table_info['schema']) || $a_table_info['schema'] === '') {
            $a_table_info['schema'] = 'public';
        }
        $query = '
            SELECT column_default
            FROM information_schema.columns
            WHERE table_schema = :schema
            AND table_name = :table_name
            AND column_name = :column_name
        ';
        try {
            $results = $this->search($query, $a_table_info);
            $column_default = $results[0]['column_default'];
            $this->pgsql_sequence_name = preg_replace("/nextval\('(.*)'(.*)\)/i", '$1', $column_default);
            return true;
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage() . ' Unable to set the PgsqlSequenceName';
            $code = $e->getCode();
            throw new ModelException($this->error_message, $code);
        }
    }

    /**
     * Sets the class propery error_message to a formated string.
     *
     * @param $pdo PDO|PDOStatement|null
     */
    public function setSqlErrorMessage(PDO|PDOStatement $pdo = null):void
    {
        if ($pdo instanceof PDOStatement || $pdo instanceof PDO) {
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
     *
     * @param $pdo PDO|PDOStatement|null
     * @return string
     */
    public function retrieveFormattedSqlErrorMessage(PDO|PDOStatement $pdo = null):string
    {
        if ($pdo !== null) {
            $this->setSqlErrorMessage($pdo);
        }
        elseif ($this->sql_error_message === '') {
            $this->setSqlErrorMessage();
        }
        return empty($this->sql_error_message) ? '' : $this->sql_error_message;
    }

    /**
     * Retrieves the raw sql errors.
     * In the format of ['SQLSTATE Error Code', 'Driver Error Code', 'Driver Error Message'].
     *
     * @param $pdo PDO|PDOStatement|null
     * @return array
     * @throws ModelException
     */
    public function retrieveRawSqlErrorInfo(PDO|PDOStatement $pdo = null): array
    {
        if ($pdo instanceof PDOStatement || $pdo instanceof PDO) {
            try {
                return $pdo->errorInfo();
            }
            catch (PDOException $e) {
                $this->error_message = 'Could not get pdo::errorInfo';
                throw new ModelException($this->error_message, 17, $e);
            }
        }
        else {
            try {
                return $this->o_pdo->errorInfo();
            }
            catch (PDOException $e) {
                $this->error_message = 'Could not get pdo::errorInfo';
                throw new ModelException($this->error_message, 17, $e);
            }
        }
    }

    /**
     * A setter of the $a_new_ids property.
     *
     * @return void
     */
    public function resetNewIds():void
    {
        $this->a_new_ids = [];
    }

    ### Basic Commands - The basic building blocks for doing db work

    /**
     * Bind values from an assoc array to a prepared query.
     *
     * @param array             $a_values Keys must match the prepared query
     * @param PDOStatement|null $o_pdo_stmt
     * @return bool
     * @throws ModelException
     */
    public function bindValues(array $a_values = [], PDOStatement $o_pdo_stmt = null): bool
    {
        if ($o_pdo_stmt === null) {
            $message = 'PDO must be passed into the execute';
            $err_code = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException($message, $err_code);
        }
        if (Arrays::isAssocArray($a_values)) {
            $a_values = $this->prepareKeys($a_values);
            foreach ($a_values as $key => $value) {
                if (is_array($key) || is_array($value)) {
                    $this->error_message = 'Missing Values';
                    throw new ModelException($this->error_message, 900);
                }
                try {
                    $o_pdo_stmt->bindValue($key, $value);
                }
                catch (PDOException) {
                    $a_error = $o_pdo_stmt->errorInfo();
                    $this->error_message = $a_error[2];
                    throw new ModelException($this->error_message, 18);
                }
            }
            return true;
        }
        if (count($a_values) > 0) {
            $x = 1;
            foreach ($a_values as $value) {
                try {
                    $o_pdo_stmt->bindValue($x++, $value);
                }
                catch (PDOException) {
                    $a_error = $o_pdo_stmt->errorInfo();
                    $this->error_message = $a_error[2];
                    throw new ModelException($this->error_message, 18);
                }
            }
            return true;
        }
        $this->error_message = 'The value passed into bindValues must be an array.';
        throw new ModelException($this->error_message, 20);
    }

    /**
     * Shortcut for PDOStatement::closeCursor().
     *
     * @param PDOStatement $o_pdo_stmt
     * @return bool
     * @throws ModelException
     */
    public function closeCursor(PDOStatement $o_pdo_stmt): bool
    {
        try {
            return $o_pdo_stmt->closeCursor();
        }
        catch (PDOException $e) {
            $this->error_message = 'Unable to close the pdo cursor';
            throw new ModelException($this->error_message, 18, $e);
        }
    }

    /**
     * Commits the PDO transaction.
     *
     * @return bool
     * @throws ModelException
     */
    public function commitTransaction(): bool
    {
        try {
            if ($this->o_pdo->commit()) {
                return true;
            }
            throw new ModelException('Unable to commit the transaction.', 13);
        }
        catch (PDOException $e) {
            throw new ModelException($e->getMessage(), 17, $e);
        }
    }

    /**
     * Executes a prepared query.
     *
     * @param array             $a_values      <pre>
     *                                         $a_values could be:
     *                                         array("test", "brains") for question mark place holders prepared statement
     *                                         array(":test"=>"test", ":food"=>"brains") for named parameters prepared statement
     *                                         '' when the values have been bound before calling this method
     *
     * @param PDOStatement|null $o_pdo_stmt    - the object created from the prepare
     * @return bool
     * @throws ModelException
     */
    public function execute(array $a_values = [], PDOStatement $o_pdo_stmt = null): bool
    {
        if ($o_pdo_stmt === null) {
            $message = 'PDO must be passed into the execute';
            $err_code = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException($message, $err_code);
        }
        if (count($a_values) > 0) {
            if (Arrays::isAssocArray($a_values)) { // for a query with bind values
                $a_values = $this->prepareKeys($a_values);
                try {
                    $this->bindValues($a_values, $o_pdo_stmt);
                }
                catch (ModelException $e) {
                    $message = $e->errorMessage();
                    $code = $e->getCode();
                    throw new ModelException($message, $code);
                }
                try {
                    return $o_pdo_stmt->execute();
                }
                catch (PDOException $e) {
                    $message = 'Unable to execute the sql.';
                    throw new ModelException($message, 18, $e);
                }
            }
            elseif (isset($a_values[0]) && is_array($a_values[0])) { // is an array of arrays
                $message = 'The array cannot be an array of array.';
                throw new ModelException($message, 22);
            }
            else { // $array is for question mark place holders prepared statement
                try {
                    $this->bindValues($a_values, $o_pdo_stmt);
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), $e->getCode());
                }
                try {
                    return $o_pdo_stmt->execute();
                }
                catch (PDOException $e) {
                    $message = 'Unable to execute the sql.';
                    throw new ModelException($message, 18, $e);
                }
            }
        }
        else { // stmt prepared elsewhere
            try {
                return $o_pdo_stmt->execute();
            }
            catch (PDOException $e) {
                $message = 'Unable to execute the sql.';
                throw new ModelException($message, 18, $e);
            }
        }
    }

    /**
     * Executes the pdo fetch method.
     *
     * @see \PDOStatment::fetch
     * @param PDOStatement $o_pdo_stmt     a \PDOStatement object
     * @param array                $a_fetch_config array('fetch_style'=>'ASSOC', 'cursor_orientation'=>'', 'cursor_offset'=>0)
     * @return array
     * @throws ModelException
     */
    public function fetchRow(PDOStatement $o_pdo_stmt, array $a_fetch_config = []): array
    {
        if ($a_fetch_config === array()) {
            $fetch_style = 'ASSOC';
            $cursor_orientation = PDO::FETCH_ORI_NEXT;
            $cursor_offset = 0;
        }
        else {
            $fetch_style = $a_fetch_config['fetch_style'] !== ''
                ? $a_fetch_config['fetch_style']
                : 'ASSOC';
            $cursor_orientation = $a_fetch_config['cursor_orientation'] !== ''
                ? $a_fetch_config['cursor_orientation']
                : PDO::FETCH_ORI_NEXT;
            $cursor_offset = $a_fetch_config['cursor_offset'] !== ''
                ? $a_fetch_config['cursor_offset']
                : 0;
        }
        $fetch_style = $this->determineFetchStyle($fetch_style);
        try {
            return $o_pdo_stmt->fetch($fetch_style, $cursor_orientation, $cursor_offset);
        }
        catch (PDOException $e) {
            $this->error_message = 'Unable to fetch the record(s).';
            throw new ModelException($this->error_message, 18, $e);
        }
    }

    /**
     * Fetches all from a pdo statement.
     *
     * @param PDOStatement $o_pdo_stmt  a \PDOStatement object
     * @param string       $fetch_style @see \PDO (optional)
     * @return array
     * @throws ModelException
     */
    public function fetchAll(PDOStatement $o_pdo_stmt, string $fetch_style = 'ASSOC'): array
    {
        $pdo_fetch_type = $this->determineFetchStyle($fetch_style);
        try {
            return $o_pdo_stmt->fetchAll($pdo_fetch_type);
        }
        catch (PDOException $e) {
            $this->error_message = 'Unable to fetch the record(s).';
            throw new ModelException($this->error_message, 18, $e);
        }
    }

    /**
     * Prepares a query for execution.
     *
     * @param string $the_query
     * @param string $cursor
     * @return PDOStatement
     * @throws ModelException
     */
    public function prepare(string $the_query = '', string $cursor = ''): PDOStatement
    {
        if ($the_query !== '') {
            switch ($cursor) {
                case 'SCROLL':
                    try {
                        $o_pdo_stmt = $this->o_pdo->prepare($the_query, array(PDO::ATTR_CURSOR =>  PDO::CURSOR_SCROLL));
                    }
                    catch (PDOException $e) {
                        $this->error_message = 'Unable to prepare the statement.';
                        throw new ModelException($this->error_message, 15, $e);
                    }
                    break;
                case 'FWDONLY':
                default:
                    try {
                        $o_pdo_stmt = $this->o_pdo->prepare($the_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                    }
                    catch (PDOException $e) {
                        $this->error_message = 'Unable to prepare the statement.';
                        throw new ModelException($this->error_message, 15, $e);
                    }
            }
            return $o_pdo_stmt;
        }

        $this->error_message = 'The query must not be blank.';
        throw new ModelException($this->error_message, 15);
    }

    /**
     * Rolls back a PDO transaction.
     *
     * @return bool
     * @throws ModelException
     */
    public function rollbackTransaction(): bool
    {
        try {
            if ($this->o_pdo->rollBack()) {
                return true;
            }
            throw new ModelException('Could not rollback the transaction.', 14);
        }
        catch (PDOException $e) {
            throw new ModelException($e->getMessage(), 17);
        }
    }

    /**
     * Executes PDOStatement::rowCount().
     *
     * @param PDOStatement $o_pdo_stmt
     * @return int
     * @throws ModelException
     */
    public function rowCount(PDOStatement $o_pdo_stmt): int
    {
        try {
            return $o_pdo_stmt->rowCount();
        }
        catch (PDOException $e) {
            throw new ModelException('Unable to get the row count.', 18, $e);
        }
    }

    /**
     * Starts a PDO transaction.
     *
     * @return bool
     * @throws ModelException
     */
    public function startTransaction(): bool
    {
        try {
            if ($this->o_pdo->beginTransaction()) {
                return true;
            }
            throw new ModelException('Unable to start the transaction.', 12);
        }
        catch (PDOException $e) {
            throw new ModelException('Unable to start the transaction.', 17, $e);
        }
    }

    ### Complete Transaction in a single command
    /**
     * Does an insert statement wrapped in a transaction.
     *
     * @param string $the_query
     * @param array  $a_values
     * @param string $table_name
     * @return bool
     * @throws ModelException
     */
    public function insertTransaction(string $the_query = '', array $a_values = [], string $table_name = ''): bool
    {
        if ($the_query === '') {
            $this->error_message = 'The Query was blank.';
            throw new ModelException($this->error_message, 20);
        }
        try {
            $this->o_pdo->beginTransaction();
        }
        catch (PDOException $e) {
            throw new ModelException('Unable to start the transaction.', 12, $e);
        }
        try {
            $this->insert($the_query, $a_values, ['table_name' => $table_name]);
            try {
                $this->o_pdo->commit();
                return true;
            }
            catch (PDOException $e) {
                $this->error_message = 'Could Not Commit the Transaction.';
                throw new ModelException($this->error_message, 13, $e);
            }
        }
        catch (ModelException $e) {
            $this->o_pdo->rollBack();
            $this->error_message = 'Unable to insert the record.';
            throw new ModelException($this->error_message, 110, $e);
        }
    }

    /**
     * Does a query wrapped in a transaction.
     *
     * @param string $the_query
     * @param array  $the_array
     * @param bool   $single_record
     * @return bool
     * @throws ModelException
     */
    public function queryTransaction(string $the_query = '', array $the_array = [], bool $single_record = true): bool
    {
        try {
            $this->o_pdo->beginTransaction();
        }
        catch (PDOException $e) {
            $this->error_message = 'Could not start transaction so we could not execute the query, Please Try Again.';
            throw new ModelException($this->error_message, 12, $e);
        }
        try {
            $this->mdQuery($the_query, $the_array, $single_record);
            try {
                $this->o_pdo->commit();
                return true;
            }
            catch (PDOException $e) {
                $this->error_message = 'Could Not Commit the Transaction.';
                throw new ModelException($this->error_message, 12, $e);

            }
        }
        catch (ModelException $e) {
            $this->error_message = 'Could Not Successfully Do the Query.';
            throw new ModelException($this->error_message, 10, $e);
        }
    }

    /**
     * Does an update wrapped in a transaction.
     *
     * @param string $the_query
     * @param array  $the_array
     * @param bool   $single_record
     * @return bool
     * @throws ModelException
     */
    public function updateTransaction(string $the_query = '', array $the_array = [], bool $single_record = true): bool
    {
        try {
            return $this->queryTransaction($the_query, $the_array, $single_record);
        }
        catch (ModelException $e) {
            $this->error_message = 'Could not update the record(s)';
            throw new ModelException($this->error_message, 300, $e);
        }
    }

    /**
     * Does a delete wrapped in a transaction.
     *
     * @param string $the_query
     * @param array  $the_array
     * @param bool   $single_record
     * @return bool
     * @throws ModelException
     */
    public function deleteTransaction(string $the_query = '', array $the_array = [], bool $single_record = true): bool
    {
        try {
            return $this->queryTransaction($the_query, $the_array, $single_record);
        }
        catch (ModelException $e) {
            $this->error_message = 'Could not delete the record(s)';
            throw new ModelException($this->error_message, 300, $e);
        }
    }

    ### Complex Commands

    /**
     * Does an insert based on a prepared query.
     *
     * @param array             $a_values   the values to be insert
     * @param PDOStatement|null $o_pdo_stmt the object pointing to the prepared statement
     * @param array             $a_table_info
     * @return bool success or failure
     * @throws ModelException
     */
    public function insertPrepared(array $a_values = [], PDOStatement $o_pdo_stmt = null, array $a_table_info = []): bool
    {
        if ($o_pdo_stmt === null) {
            $message = 'PDO must be passed into the execute';
            $err_code = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException($message, $err_code);
        }
        if (count($a_values) > 0) {
            $this->resetNewIds();
            try {
                $this->executeInsert($a_values, $o_pdo_stmt, $a_table_info);
                return true;
            }
            catch (ModelException $e) {
                throw new ModelException('Could not executeInsert. ' . $e->errorMessage(), $e->getCode(), $e);
            }
        }
        else {
            $this->error_message = 'The array of values for a prepared insert was empty.';
            $this->resetNewIds();
            throw new ModelException($this->error_message, 120);
        }
    }

    /**
     * Specialized version of execute which retains ids of each insert.
     *
     * @param array             $a_values see $this->execute for details
     * @param PDOStatement|null $o_pdo_stmt
     * @param array             $a_table_info
     * @return bool
     * @throws ModelException
     */
    public function executeInsert(array $a_values = [], PDOStatement $o_pdo_stmt = null, array $a_table_info = []):bool
    {
        if ($o_pdo_stmt === null) {
            $message = 'PDO must be passed into the execute';
            $err_code = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException($message, $err_code);
        }
        if (count($a_values) > 0) {
            if (isset($a_values[0]) && is_array($a_values[0])) { // is an array of arrays, can not be mixed
                foreach ($a_values as $a_stuph) {
                    try {
                        $this->executeInsert($a_stuph, $o_pdo_stmt, $a_table_info);
                    }
                    catch (ModelException $e) {
                        $a_pdo_info = $this->o_pdo->errorInfo();
                        $message = 'Could not executeInsert ' . $a_pdo_info[2];
                        throw new ModelException($message, $e->getCode(), $e);
                    }
                }
            }
            else { // should be a single record insert
                try {
                    $this->execute($a_values, $o_pdo_stmt);
                    try {
		                $sequence_name = '';
		                if ($this->db_type === 'pgsql' && !empty($a_table_info)) {
                            $sequence_name = $this->getPgsqlSequenceName($a_table_info);
		                }
                        $this->a_new_ids[] = $this->o_pdo->lastInsertId($sequence_name);
                    }
                    catch (PDOException $e) {
                        $this->error_message = 'Could not get pdo->lastInsertId. Sequence Name: ' . $sequence_name . ' ' . $e->getMessage();
                        throw new ModelException($this->error_message, 110, $e);
                    }
                }
                catch (ModelException $e) {
                    $message = 'Could not execute the sql. ' . $e->errorMessage();
                    throw new ModelException($message, $e->getCode(), $e);
                }
            }
        }
        else {
            $this->error_message = 'A non-empty array for its first parameter.';
            throw new ModelException($this->error_message, 120);
        }
        return true;
    }

    /**
     * Used for both modifying and deleting record(s)
     *
     * @param string $the_query     required, the sql statement, default is ''
     * @param array  $a_values      optional, formated values for a prepared sql statement, default is ''
     * @param bool   $single_record optional, if only a single record should be changed/deleted, default is true
     * @return bool
     * @throws ModelException
     */
    public function mdQuery(string $the_query = '', array $a_values = [], bool $single_record = true):bool
    {
        if ($the_query === '') {
            $this->error_message = 'The query must not be blank.';
            throw new ModelException($this->error_message, 20);
        }
        if (empty($a_values)) {
            try {
                $this->affected_rows = $this->o_pdo->exec($the_query);
                if ($single_record && $this->affected_rows > 1) {
                    $this->error_message = 'The query affected multiple records instead of a single one.';
                    throw new ModelException($this->error_message, 10);
                }
                if ($this->affected_rows === 0) {
                    $this->error_message = 'The query affected no records.';
                    throw new ModelException($this->error_message, 10);
                }
            }
            catch (PDOException $e) {
                $this->setSqlErrorMessage($this->o_pdo);
                $message = $this->getSqlErrorMessage();
                throw new ModelException($message, 10, $e);
            }
        }
        elseif (count($a_values) > 0) {
            try {
                $o_pdo_stmt = $this->prepare($the_query);
                try {
                    $results = $this->mdQueryPrepared($a_values, $single_record, $o_pdo_stmt);
                    if (!$results) {
                        throw new ModelException($this->error_message, 10);
                    }
                }
                catch (ModelException $e) {
                    $this->error_message = $e->errorMessage();
                    throw new ModelException($e->getMessage(), $e->getCode(), $e);
                }

            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
        else {
            $this->error_message = 'The array of values for a prepared query was empty.';
            throw new ModelException($this->error_message, 20);
        }
        return true;
    }

    /**
     * Executes a prepared sql statement, allowing for it to call itself for multiple records.
     * Question: why is it called mdQueryPrepared? You can tell this is an old method,
     * with its mysterious name. md stands for modify delete.
     *
     * @param array             $a_values
     * @param bool              $single_record
     * @param PDOStatement|null $o_pdo_stmt
     * @return bool
     * @throws ModelException
     */
    public function mdQueryPrepared(array $a_values = [], bool $single_record = true, PDOStatement $o_pdo_stmt = null): bool
    {
        if ($o_pdo_stmt === null) {
            $message = 'PDO must be passed into the execute';
            $err_code = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException($message, $err_code);
        }
        if (empty($a_values)) {
            throw new ModelException('Missing array values', 20);
        }

        if (isset($a_values[0]) && is_array($a_values[0])) { // array of arrays
            foreach ($a_values as $row) {
                try {
                    $this->mdQueryPrepared($row, $single_record, $o_pdo_stmt);
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), 10, $e);
                }
            }
            return true;
        }

        try {
            $this->execute($a_values, $o_pdo_stmt);
            return true;
        }
        catch (ModelException $e) {
            $this->error_message = "Could not execute the query: {$this->retrieveFormattedSqlErrorMessage($o_pdo_stmt)}";
            throw new ModelException($this->error_message, 10, $e);
        }
    }

    /**
     * Do a query.
     * Has three params. The first is required.
     * The second is required if the first param doesn't include a valid sql statement.
     *
     * @param array|string $query_params default is empty str.  - required
     *                                   Should correspond to something like
     *                                   ['type'=>'search', 'table_name'=>'test_table', 'single_record'=>false, 'sql'=>'']
     * @param array|string $data         <pre>
     *                                   array(field_name => value) - data to be insert or modified
     *                                   'id, name, date' (str) - a string of fields to be returned in a search</pre>
     * @param  array       $where_values array(field_name => value) - paramaters used to find records for search or modify
     * @return bool
     * @throws ModelException
     */
    public function query(array|string $query_params = '', array|string $data = '', array $where_values = []): bool
    {
        $default_params = [
            'type'          => '',
            'table_name'    => '',
            'single_record' => false,
            'sql'           => ''
        ];
        if ($query_params === '') {
            $query_params = $default_params;
        }
        else {
            $query_params = array_merge($default_params, $query_params);
        }
        switch($query_params['type']) {
            case 'search': // can not build a JOIN so only complete sql statements as part of $query_params can do joins here
                if ($query_params['sql'] !== '') {
                    try {
                        return $this->search($query_params['sql'], $where_values);
                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                }
                $query = "
                    SELECT {$data} FROM {$query_params['table_name']}
                    WHERE ";
                $where = '';
                $a_where = [];
                foreach ($where_values as $values) {
                    $where .= $where !== '' ? ' AND ' : '' ;
                    $where .= $values['field_name'] . $values['operator'] . ':' . $values['field_name'];
                    $a_where[$values['field_name']] = $values['field_value'];
                }
                $query .= $where;
                try {
                    return $this->search($query, $a_where);
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                }
                break;
            case 'add':
                if ($query_params['sql'] !== '') {
                    try {
                        return $this->insert($query_params['sql'], $data, $query_params['table_name']);
                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                }
                $field_names = '(';
                $values = '(';
                $field_data = $this->prepareValues($data);
                foreach($field_data as $field_name => $value) {
                    $field_names .= $field_names !== '' ? ', ' : '';
                    $field_names .= $field_name;
                    $values .= $values !== '' ? ', ' : '';
                    $values .= $field_name;
                }
                $field_names .= ')';
                $values .= ')';
                $query = "INSERT INTO {$query_params['table_name']} 
                    {$field_names} VALUES . {$values}";
                $a_data = $this->prepareKeys($data);
                try {
                    return $this->insert($query, $a_data, $query_params['table_name']);
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                }
                break;
            case 'modify':
                if ($query_params['sql'] !== '') {
                    try {
                        return $this->update($query_params['sql'], $data, $query_params['single_record']);
                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                }
                $query = "UPDATE {$query_params['table_name']} ";
                $set_lines = '';
                $field_names = $this->prepareValues($data);
                foreach ($field_names as $field_name => $value) {
                    $set_lines .= $set_lines !== '' ? ', ' : 'SET ';
                    $set_lines .= $field_name . ' = ' . $value;
                }
                $where_values = $this->prepareValues($where_values);
                $where_lines = '';
                foreach ($where_values as $values) {
                    $where_lines .= $where_lines !== '' ? ' AND ' : '' ;
                    $where_lines .= $values['field_name'] . $values['operator'] . $values['field_value'];
                }
                $query .= $set_lines . $where_lines;
                try {
                    return $this->update($query, $data, $query_params['single_record']);
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                }
                break;
            case 'delete':
                if ($query_params['sql'] !== '') {
                    try {
                        return $this->delete($query_params['sql'], $data, $query_params['single_record']);
                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                }
                $query = "DELETE FROM {$query_params['table_name']} ";
                $where_names = $this->prepareValues($where_values);
                $where_lines = '';
                foreach ($where_names as $field_name => $value) {
                    $where_lines .= $where_lines !== '' ? ' AND ' : ' WHERE ';
                    $where_lines .= $field_name . ' = ' . $value;
                }
                try {
                    return $this->delete($query . $where_lines, $data, $query_params['single_record']);
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                }
                break;
            default:
                throw new ModelException('Missing Query Type', 20);
        }
    }

    /**
     * Does a search with a prepared statement.
     *
     * @param array             $a_values
     * @param PDOStatement|null $o_pdo_stmt
     * @param string            $type
     * @return array
     * @throws ModelException
     */
    public function searchPrepared(array $a_values = [], PDOStatement $o_pdo_stmt = null, string $type = 'assoc'): array
    {
        if ($o_pdo_stmt === null) {
            $message = 'PDO must be passed into the execute';
            $err_code = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException($message, $err_code);
        }
        $fetch_style = $this->determineFetchStyle($type);
        if (count($a_values) > 0) {
            $a_results = [];
            if (isset($a_values[0]) && is_array($a_values[0])) {
                foreach ($a_values as $row) {
                    try {
                        $this->execute($row, $o_pdo_stmt);
                        try {
                            $a_results[] = $o_pdo_stmt->fetchAll($fetch_style);
                        }
                        catch (PDOException $e) {
                            throw new ModelException($e->getMessage(), 200, $e);
                        }
                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                }
            }
            else {
                try {
                    $this->execute($a_values, $o_pdo_stmt);
                    try {
                        $a_results = $o_pdo_stmt->fetchAll($fetch_style);
                    }
                    catch (PDOException $e) {
                        throw new ModelException('Unable to fetch the records.', 299, $e);
                    }
                }
                catch (ModelException $e) {
                    throw new ModelException('Unable to execute the search.', 299, $e);
                }
            }
            return $a_results;
        }
        $this->error_message = 'There was a problem with the array';
        throw new ModelException($this->error_message, 20);
    }

    ### Utility Functions
    /**
     * Gets the variable for the fetch style
     *
     * @param string $type
     * @return int
     */
    public function determineFetchStyle(string $type = 'assoc'): int
    {
        $type = strtolower($type);
        return match ($type) {
            'bound' => PDO::FETCH_BOUND,
            'class' => PDO::FETCH_CLASS,
            'into'  => PDO::FETCH_INTO,
            'lazy'  => PDO::FETCH_LAZY,
            'obj'   => PDO::FETCH_OBJ,
            'num'   => PDO::FETCH_NUM,
            'both'  => PDO::FETCH_BOTH,
            default => PDO::FETCH_ASSOC,
        };
    }

    /**
     * Use the \PDO::quote function to make the string safe for use in a query.
     * Used only when not using a prepared sql statement.
     * @see \PDO::quote
     * @param $value (str)
     * @return string - quoted string
     * @throws ModelException
     */
    public function quoteString($value): string
    {
        try {
            return $this->o_pdo->quote($value);
        }
        catch (PDOException $e) {
            throw new ModelException($e->getMessage(), 17);
        }
    }

    /**
     * Returns a list of the columns from a database table.
     *
     * @param string $table_name required name of the table
     * @return array             field names
     * @throws ModelException
     */
    public function selectDbColumns(string $table_name = ''): array
    {
        if ($table_name !== '') {
            $a_column_names = [];
            switch ($this->db_type) {
                case 'pgsql':
                    $sql = "
                        SELECT column_name
                        FROM information_schema.columns
                        WHERE table_name ='{$table_name}'
                    ";
                    try {
                        $results = $this->search($sql);
                        foreach ($results as $row) {
                            $a_column_names[] = $row['column_name'];
                        }

                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                    break;
                case 'sqlite':
                    $sql = "PRAGMA table_info({$table_name})";
                    try {
                        $results = $this->search($sql);
                        foreach ($results as $row) {
                            $a_column_names[] = $row['Field'];
                        }
                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                    break;
                case 'mysql':
                default:
                    $sql = "SHOW COLUMNS FROM {$table_name}";
                    try {
                        $results = $this->search($sql);
                        foreach ($results as $row) {
                            $a_column_names[] = $row['Field'];
                        }
                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                // end both mysql and default
            }
            return $a_column_names;

        }

        $this->error_message = 'You must specify a table name for this to work.';
        throw new ModelException($this->error_message, 20);
    }

    /**
     * Selects the table names from the database.
     * @return array $a_table_names
     * @throws ModelException
     */
    public function selectDbTables(): array
    {
        $sql = match ($this->db_type) {
            'pgsql'  => '
                    SELECT table_name
                    FROM information_schema.tables
                ',
            'sqlite' => "
                    SELECT name
                    FROM sqlite_master
                    WHERE type='table'
                    ORDER BY name
                ",
            default  => 'SHOW TABLES',
        };
        try {
            return $this->search($sql, array(), 'num');
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to retrieve tables.', 200, $e);
        }
    }

    /**
     * Checks to see if the table exists.
     *
     * @param string $table_name
     * @return bool
     */
    public function tableExists(string $table_name = ''): bool
    {
        $sql = match ($this->db_type) {
            'pgsql'  => "
                    SELECT count(table_name) as count
                    FROM information_schema.tables
                    WHERE table_name = '{$table_name}'
                    AND table_catalog = '{$this->a_db_config['name']}'
                ",
            'sqlite' => "
                    SELECT count(*) as count
                    FROM sqlite_master
                    WHERE type='table'
                    AND name='{$table_name}'
                ",
            default  => "
                    SELECT count(*) as count
                    FROM information_schema.tables
                    WHERE TABLE_SCHEMA = '{$this->a_db_config['name']}'
                    AND TABLE_NAME = '{$table_name}'
                ",
        };

        try {
            $results = $this->search($sql, []);
            return $results[0]['count'] > 0;
        }
        catch (ModelException) {
            return false;
        }
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
