<?php
/**
 *  Does all the database stuff.
 *  For read/write access to the database based on PDO.
 *  @file Database.php
 *  @class Database
 *  @ingroup wer_framework classes
 *  @author William Reveal <wer@wereveal.com>
 *  @version 2.3.2
 *  @date 2013-03-30 10:25:36
 *  @par Change Log
 *      v2.3.2 - new method to remove bad keys
 *               removed some redundant code
 *               reorganized putting main four commands at top for easy reference
 *               renamed from modify to update, no good reason truthfully except no legacy code to support
 *      v2.3.1 - new method to check for missing keys
 *               made a couple changes to clarify what was going on.
 *      v2.3.0 - Modified to work within Symfony
 *      v2.2.0 - FIG-standard changes
 *  @par Wer Framework v4.0.0
**/
namespace Wer\FrameworkBundle\Library;

use Wer\FrameworkBundle\Library\Elog;
use Wer\FrameworkBundle\Library\Files;
use Wer\FrameworkBundle\Library\Arrays;
use Symfony\Component\Yaml\Yaml;

class Database extends Base
{
    private static $instance_rw;
    private static $instance_ro;
    private $affected_rows;
    protected $current_page;
    private $db_name;
    private $db_host;
    private $db_pass;
    private $db_passro;
    private $db_persist;
    private $db_port;
    private $db_type;
    private $db_user;
    private $db_userro;
    private $dsn;
    private $a_new_ids = array();
    private $o_arr;
    private $o_db;
    protected $o_elog;
    private $o_files;
    private $o_pdo_statement = '';
    private $pgsql_sequence_name = '';
    protected $private_properties;
    private $read_type = '';
    private $root_path;
    private $sql_error_message;
    private $success;
    private function __construct($read_type = 'ro', $config_file = 'parameters.yml')
    {
        $this->root_path = $_SERVER['DOCUMENT_ROOT'];
        $this->setPrivateProperties();
        $this->o_elog = Elog::start();
        $this->o_arr = new Arrays;
        $this->setDatabaseParameters($config_file);
        $this->read_type = $read_type;
        $this->o_elog->write('Read Type = ' . $this->read_type, LOG_OFF, __METHOD__);
    }
    /**
     *  Starts the Singleton.
     *  @param str $read_type Default rw
     *  @param bool $config_file default '' specifies exact location of config file
     *      if not specified, the Files class should be use to locate the config file
     *  @return obj - reference the the database object created
    **/
    public static function start($read_type = 'rw', $config_file = 'parameters.yml')
    {
        if ($read_type == 'ro') {
            if (!isset(self::$instance_ro)) {
                self::$instance_ro = new Database('ro', $config_file);
            }
            return self::$instance_ro;
        } else {
            if (!isset(self::$instance_rw)) {
                self::$instance_rw = new Database('rw', $config_file);
            }
            return self::$instance_rw;
        }
    }

    ### Main Four Commands (CRUD) ###
    /**
     *  Inserts data into the database
     *  @param $the_query (str) - the INSERT statement, default is empty.
     *  @param $a_values (mixed) - default is empty
     *      If blank, the values are in the INSERT string
     *      If array then the INSERT string is for a prepared query
     *  @param @table_name - needed only if PostgreSQL is being used, Default ''
     *  @return BOOL - success or failure
    **/
    public function insert($the_query = '', $a_values = '', $table_name = '')
    {
        if ($the_query == '') {
            $this->o_elog->write('The query must not be blank.', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return false;
        }
        $sequence_name = '';
        if($this->db_type == 'pgsql' && $table_name != '') {
            $sequence_name = $this->getPgsqlSequenceName($table_name);
        }
        if ($a_values == '') {
            $this->affected_rows = $this->o_db->exec($the_query);
            if ($this->affected_rows === false) {
                $this->setSqlErrorMessage();
                $this->o_elog->write($this->getSqlErrorMessage(), LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                return false;
            } elseif ($this->affected_rows == 0) {
                $this->o_elog->write('The INSERT affected no records.', LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            } else { // note: kind of assumes there was a single record inserted
                $this->a_new_ids = array($this->o_db->lastInsertId($sequence_name));
                return true;
            }
        } elseif (is_array($a_values) && count($a_values) > 0) {
            $o_pdo_statement = $this->prepare($the_query);
            if ($o_pdo_statement === false) {
                $this->o_elog->write('Could not prepare the statement', LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            } else {
                return $this->insertPrepared($a_values, $o_pdo_statement);
            }
        } else {
            $this->o_elog->write('The array of values for a prepared insert was empty.', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return false;
        }
    }
    /**
     *  Searches the database for records.
     *  Can be set up with upto 3 arguments, the first required, the sql
     *  @param string $the_query, required
     *  @param array array $a_values associative array, key in named prepared
     *      format e.g., array(':id'=>1, ':name'=>'fred');
     *  @param string $type optional, type of results, num, both, assoc which
     *      specifies the PDO formats, defaults to assoc
     *  @return mixed results of search or false
    **/
    public function search($the_query = '', $a_values = '', $type = 'assoc')
    {
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
            $this->o_elog->write('The query must not be blank.', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return false;
        }
        if ($a_values == '' || count($a_values) == 0) {
            $o_pdo_statement = $this->o_db->prepare($the_query);
            if ($o_pdo_statement === false) {
                $this->setSqlErrorMessage();
                $this->o_elog->write($this->getSqlErrorMessage(), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            }
            $o_pdo_statement->execute();
            $a_results = $o_pdo_statement->fetchAll($fetch_style);
            $o_pdo_statement->closeCursor();
        } elseif (is_array($a_values) && count($a_values) > 0) {
            $this->o_elog->write("Query is: {$the_query}", LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->o_elog->write("The array is " . var_export($a_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);

            $o_pdo_statement = $this->prepare($the_query);
            if ($o_pdo_statement) {
                $a_results = $this->searchPrepared($a_values, $o_pdo_statement, $type);
            }
        } else {
            $this->o_elog->write("There was a problem with the array", LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->o_elog->write("a_values is: " . var_export($a_values , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return false;
        }
        return $a_results;
    }
    /**
     *  Executes a query to modify one or more records.
     *  This is a stub. It executes the $this->mdQuery method
     *  @param $the_query (str)
     *  @param $a_values (array) - associative array with paramaters
     *  @param @single_record - specifies if only a single record should be deleted per query
     *  @return bool - success or failure
    **/
    public function update($the_query = '', $a_values = '', $single_record = true)
    {
        return $this->mdQuery($the_query, $a_values, $single_record);
    }
    /**
     *  Executes a query to delete one or more records.
     *  This is a stub. It executes the $this->mdQuery method
     *  @param $the_query (str)
     *  @param $a_values (array) - associative array with where paramaters
     *  @param @single_record - specifies if only a single record should be deleted per query
     *  @return bool - success or failure
    **/
    public function delete($the_query = '', $a_values = '', $single_record = true)
    {
        return $this->mdQuery($the_query, $a_values, $single_record);
    }

    ### Getters and Setters
    /**
     *  Get the value of the property specified.
     *  @param $var_name (str)
     *  @return mixed - value of the property
     *  @note - this is normally set to private so not to be used
    **/
    private function getVar($var_name)
    {
        return $this->$var_name;
    }
    public function getAffectedRows()
    {
        return $this->affected_rows;
    }
    public function getNewIds()
    {
        return $this->a_new_ids;
    }
    public function getPgsqlSequenceName($table_name = '')
    {
        if ($table_name != '') {
            $this->setPgsqlSequenceName($table_name);
        }
        return $this->pgsql_sequence_name;
    }
    public function getSuccess()
    {
        return $this->success;
    }
    public function getSqlErrorMessage()
    {
        return $this->sql_error_message;
    }
    private function setDatabaseParameters($config_file = 'parameters.yml')
    {
        $config_w_path = $this->root_path . '/../app/config/' . $config_file;
        if (!file_exists($config_w_path)) {
            $config_w_path = 'app/config/' . $config_file;
        }
        $file_contents = file_get_contents($config_w_path);
        $a_results = Yaml::parse($file_contents);
        $a_parameters = $a_results['parameters'];
        $this->db_type   = str_replace('pdo_', '', $a_parameters['database_driver']);
        $this->db_host   = $a_parameters['database_host'];
        $this->db_port   = $a_parameters['database_port'];
        $this->db_name   = $a_parameters['database_name'];
        $this->db_userro = $a_parameters['database_userro'];
        $this->db_passro = $a_parameters['database_passro'];
        $this->db_user   = $a_parameters['database_user'];
        $this->db_pass   = $a_parameters['database_password'];
    }
    public function setDbName($value = '')
    {
        if ($value !== '') {
            $this->db_name = $value;
        }
    }
    public function setDbHost($value = '')
    {
        if ($value !== '') {
            $this->db_host = $value;
        }
    }
    public function setDbPass($value = '')
    {
        if ($value !== '') {
            $this->db_pass = $value;
        }
    }
    public function setDbPassro($value = '')
    {
        if ($value !== '') {
            $this->db_passro = $value;
        }
    }
    public function setDbPersist($value = '')
    {
        if (($value !== '') && (is_bool($value) !== false)) {
            $this->db_type = $value;
        }
    }
    public function setDbType($value = '')
    {
        $a_allowed_types = array('mysql', 'sqlite', 'pgsql');
        if (($value !== '') && (array_search($value, $a_allowed_types) !== false)) {
            $this->db_type = $value;
        }
    }
    public function setDbUser($value = '')
    {
        if ($value !== '') {
            $this->db_user = $value;
        }
    }
    public function setDbUserro($value = '')
    {
        if ($value !== '') {
            $this->db_userro = $value;
        }
    }
    public function setDsn()
    {
        if ($this->db_port != '' && $this->db_port !== null) {
            $this->dsn = $this->db_type . ':host=' . $this->db_host . ';port=' . $this->db_port . ';dbname=' . $this->db_name;
        } else {
            $this->dsn = $this->db_type . ':host=' . $this->db_host . ';dbname=' . $this->db_name;
        }
    }
    public function setNewId($value = '')
    {
        if ($value !== '') {
            $this->a_new_ids[] = $value;
        }
    }
    /**
     *  Get and save the sequence name for a pgsql table in the protected property $pgsql_sequence_name.
     *  @param $table_name (str) - name of the table - Default '' - Required
     *  @param $schema_name (str) - name of the schema the table is in - Default 'public' - Optional
     *  @param $column_name (str) - name of the column that should have the sequence - default 'id' - optional
     *  @return success
    **/
    public function setPgsqlSequenceName($table_name = '', $schema_name = 'public', $column_name = 'id')
    {
        $query = "
            SELECT column_default
            FROM information_schema.columns
            WHERE table_schema = :schema
            AND table_name = :table_name
            AND column_name = :column_name";
        $results = $this->search($query, array('schema'=>$schema_name, 'table_name'=>$table_name, 'column_name'=>$column_name));
        if ($results) {
            $this->o_elog->write("Results: " . var_export($results, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            $column_default = $results[0]['column_default'];
            $this->pgsql_sequence_name = preg_replace("/nextval\('(.*)'(.*)\)/i", '$1', $column_default);
            $this->o_elog->write("pgsql_sequence_name: " . $this->pgsql_sequence_name, LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->closeCursor();
        }
    }
    public function setSqlErrorMessage($type = 'pdo')
    {
        $a_error_stuff = $type == 'pdos' ? $this->o_pdo_statement->errorInfo() : $this->o_db->errorInfo() ;
        $this->sql_error_message = 'SQLSTATE Error Code: ' . $a_error_stuff[0] . ' Driver Error Code: ' . $a_error_stuff[1] . ' Driver Error Message: ' . $a_error_stuff[2];
    }
    /**
     *  A setter of the $a_new_ids property
     *  @return void
    **/
    public function resetNewIds()
    {
        $this->a_new_ids = array();
    }

    ### Basic Commands - The basic building blocks for doing db work
    /**
     *  Bind values from an assoc array to a prepared query.
     *  @param $array (array) - Keys must match the prepared query
     *  @return bool - success or failure
    **/
    public function bindValues($array = '', $o_pdo_statement = '')
    {
        if ($o_pdo_statement != '' && $o_pdo_statement instanceof \PDOStatement) {
            $this->o_pdo_statement = $o_pdo_statement;
        }
        $this->o_elog->setFromMethod(__METHOD__ . '.' . __LINE__);
        $this->o_elog->write("bind array: " . var_export($array, true), LOG_OFF);
        if ($this->o_arr->isAssocArray($array)) {
            foreach ($array as $key=>$value) {
                if ($this->o_pdo_statement->bindValue($key, $value) === false) {
                    $a_error = $this->o_pdo_statement->errorInfo();
                    $this->o_elog->write($a_error[2], 4);
                    return false;
                }
            }
            return true;
        } elseif (is_array($array)) {
            $this->o_elog->write('binding a basic array', LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->o_elog->write($array[0], LOG_OFF, __METHOD__ . '.' . __LINE__);
            $x = 1;
            foreach ($array as $value) {
                if ($this->o_pdo_statement->bindValue($x++, $value) === false) {
                    $a_error = $this->o_pdo_statement->errorInfo();
                    $this->o_elog->write($a_error[2], LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                    return false;
                }
                $this->o_elog->write("Successful Binding of {$value}", LOG_OFF, __METHOD__ . '.' . __LINE__);
            }
        } else {
            $this->o_elog->write('The value passed into bindValues must be an array.');
            return false;
        }
    }
    public function closeCursor($o_pdo_statement = '')
    {
        if ($o_pdo_statement != '' && $o_pdo_statement instanceof \PDOStatement) {
            $this->o_pdo_statement = $o_pdo_statement;
        }
        return $this->o_pdo_statement->closeCursor();
    }
    public function commitTransaction()
    {
        return $this->o_db->commit();
    }
    public function connect()
    {
        $this->o_elog->setFromMethod(__METHOD__);
        if (is_object($this->o_db)) {
            $this->o_elog->write('The database is already connected.', LOG_OFF);
            return true;
        }
        $this->setDsn();
        try {
            if ($this->read_type == 'ro') {
                $this->o_db = new \PDO(
                    $this->dsn,
                    $this->db_userro,
                    $this->db_passro,
                    array(\PDO::ATTR_PERSISTENT=>$this->db_persist));
            } else {
                $this->o_db = new \PDO(
                    $this->dsn,
                    $this->db_user,
                    $this->db_pass,
                    array(\PDO::ATTR_PERSISTENT=>$this->db_persist));
            }
            $this->o_elog->write("The dsn is: $this->dsn", LOG_OFF);
            $this->o_elog->write('Connect to db success.', LOG_OFF);
            return true;
        }
        catch(\PDOException $e) {
            $this->o_elog->write('Error! Could not connect to database: ' . $e->getMessage(), LOG_ALWAYS);
            return false;
        }
    }
    /**
     *  Executes a prepared query
     *  @param $a_values array <pre>
     *  $a_values could be:
     *      array("test", "brains") for question mark place holders prepared statement
     *      array(":test"=>"test", ":food"=>"brains") for named parameters prepared statement
     *      '' when the values have been bound before calling this method
     *  @param @o_pdo_statement (obj) - the object created from the prepare
     *  @return bool - success or failure
    **/
    public function execute($a_values = '', $o_pdo_statement = '')
    {
        $this->o_elog->write('Array: ' . var_export($a_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($o_pdo_statement != '' && $o_pdo_statement instanceof \PDOStatement) {
            $this->o_pdo_statement = $o_pdo_statement;
        } elseif ($this->o_pdo_statement instanceof \PDOStatement) {
            $o_pdo_statement = $this->o_pdo_statement;
        } else {
            $this->o_elog->setFrom(basename(__FILE__), __METHOD__);
            $this->o_elog->write('A statement must be prepared and an instance of \PDOStatement then exist.', 4);
            return false;
        }
        if (is_array($a_values) && count($a_values) > 0) {
            if ($this->o_arr->isAssocArray($a_values)) { // for a query with bind values
                $a_values = $this->prepareKeys($a_values);
                $this->o_elog->write('Fixed Array: ' . var_export($a_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);

                if ($this->bindValues($a_values) === false) {
                    return false;
                }
                return $o_pdo_statement->execute();
            } elseif (isset($a_values[0]) && is_array($a_values[0])) { // is an array of arrays
                $this->o_elog->write('The array cannot be an array of array.', LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            } else { // $array is for question mark place holders prepared statement
                $this->o_elog->write("Attempting to execute a question mark place prepared statement", LOG_OFF, __METHOD__ . '.' . __LINE__);
                if ($this->bindValues($a_values) === false) {
                    return false;
                }
                return $o_pdo_statement->execute();
            }
        } else {
            $this->o_elog->write('Executing a query with pre-bound values', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $o_pdo_statement->execute(); // values have been bound elsewhere
        }
    }
    /**
     *  Executes the pdo fetch method
     *  @param $fetch_style (str) - specifies which type of fetch to do
     *  @param $cursor_orientation (str) - @see \PDOStatment::fetch
     *  @param $cursor_offset (str) - @see \PDOStatment::fetch
     *  @return mixed - depends on fetch_style, always return false on failure - @see \PDOStatment::fetch
    **/
    public function fetch($fetch_style = 'ASSOC', $cursor_orientation = '', $cursor_offset = 0)
    {
        if ($cursor_orientation == '') {
            $cursor_orientation = \PDO::FETCH_ORI_NEXT;
        }
        switch ($fetch_style) {
            case 'BOTH':
                return $this->o_pdo_statement->fetch(\PDO::FETCH_BOTH, $cursor_orientation, $cursor_offset);
                break;
            case 'BOUND':
                return $this->o_pdo_statement->fetch(\PDO::FETCH_BOUND, $cursor_orientation, $cursor_offset);
                break;
            case 'CLASS':
                return $this->o_pdo_statement->fetch(\PDO::FETCH_CLASS, $cursor_orientation, $cursor_offset);
                break;
            case 'INTO':
                return $this->o_pdo_statement->fetch(\PDO::FETCH_INTO, $cursor_orientation, $cursor_offset);
                break;
            case 'LAZY':
                return $this->o_pdo_statement->fetch(\PDO::FETCH_LAZY, $cursor_orientation, $cursor_offset);
                break;
            case 'NUM':
                return $this->o_pdo_statement->fetch(\PDO::FETCH_NUM, $cursor_orientation, $cursor_offset);
                break;
            case 'OBJ':
                return $this->o_pdo_statement->fetch(\PDO::FETCH_OBJ, $cursor_orientation, $cursor_offset);
                break;
            case 'ASSOC':
            default:
                return $this->o_pdo_statement->fetch(\PDO::FETCH_ASSOC, $cursor_orientation, $cursor_offset);
        }
    }
    /**
     *  Prepares a sql statement for execution
     *  @param $the_query (str) - the query to prepare
     *  @param $cusror (str) - @see \PDO::prepare (optional)
     *  @return mixed - \PDO object if successful, else false
    **/
    public function fetch_all($fetch_style = 'ASSOC')
    {
        switch ($fetch_style) {
            case 'BOTH':
                return $this->o_pdo_statement->fetchAll(\PDO::FETCH_BOTH);
                break;
            case 'BOUND':
                return $this->o_pdo_statement->fetchAll(\PDO::FETCH_BOUND);
                break;
            case 'CLASS':
                return $this->o_pdo_statement->fetchAll(\PDO::FETCH_CLASS);
                break;
            case 'INTO':
                return $this->o_pdo_statement->fetchAll(\PDO::FETCH_INTO);
                break;
            case 'LAZY':
                return $this->o_pdo_statement->fetchAll(\PDO::FETCH_LAZY);
                break;
            case 'NUM':
                return $this->o_pdo_statement->fetchAll(\PDO::FETCH_NUM);
                break;
            case 'OBJ':
                return $this->o_pdo_statement->fetchAll(\PDO::FETCH_OBJ);
                break;
            case 'ASSOC':
            default:
                return $this->o_pdo_statement->fetchAll(\PDO::FETCH_ASSOC);
        }
    }
    public function prepare($the_query = '', $cursor = '')
    {
        $this->o_elog->write("Query passed in: {$the_query}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($the_query != '') {
            switch ($cursor) {
                case 'SCROLL':
                    $o_pdo_statement = $this->o_db->prepare($the_query, array(\PDO::ATTR_CURSOR =>  \PDO::CURSOR_SCROLL));
                    break;
                case 'FWDONLY':
                default:
                    $o_pdo_statement = $this->o_db->prepare($the_query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            }
            if ($o_pdo_statement !== false) {
                $this->o_pdo_statement = $o_pdo_statement;
                $this->o_elog->write('Success for prepare.', LOG_OFF, __METHOD__ . '.' . __LINE__);
                return $o_pdo_statement;
            } else {
                $this->o_elog->write('Failure for prepare.', LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            }
        } else {
            $this->o_elog->write('The query must not be blank.', LOG_ALWAYS);
            return false;
        }
    }
    public function rollbackTransaction()
    {
        return $this->o_db->rollback();
    }
    public function rowCount($o_pdo_statement = '')
    {
        if ($o_pdo_statement != '' && $o_pdo_statement instanceof \PDOStatement) {
            $this->o_pdo_statement = $o_pdo_statement;
        }
        return $this->o_pdo_statement->rowCount();
    }
    public function startTransaction()
    {
        return $this->o_db->beginTransaction();
    }

    ### Complete Transaction in a single command
    public function insertTransaction($the_query = '', $a_values = '', $table_name = '')
    {
        if ($the_query == '') {
            $this->o_elog->write('The Query was blank.', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return false;
        }
        if ($this->o_db->beginTransaction()) {
            $results = $this->insert($the_query, $a_values, $table_name = '');
            if ($results) {
                if ($this->o_db->commit() === false) {
                    $message = 'Could Not Commit the Transaction.';
                } else {
                    return true;
                }
            } else {
                $message = 'Could Not Successfully Do the Insert.';
            }
        } else {
            $message = 'Could not start transaction so we could not execute the insert, Please Try Again.';
        }
        $this->setSqlErrorMessage();
        $this->o_elog->write($this->getSqlErrorMessage(), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->o_db->rollback();
        $this->o_elog->write($message);
        return false;
    }
    public function queryTransaction($the_query = '', $the_array = '', $single_record = true)
    {
        $this->o_elog->write("The Query is: {$the_query}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($this->o_db->beginTransaction()) {
            $results = $this->mdQuery($the_query, $the_array, $single_record);
            if ($results) {
                if ($this->o_db->commit() === false) {
                    $message = 'Could Not Commit the Transaction.';
                } else {
                    return true;
                }
            } else {
                $message = 'Could Not Successfully Do the Query.';
            }
        } else {
            $message = 'Could not start transaction so we could not execute the query, Please Try Again.';
        }
        $this->setSqlErrorMessage();
        $this->o_elog->write($message . ' ==> ' . $this->getSqlErrorMessage(), LOG_OFF);
        $this->rollbackTransaction();
        return false;
    }
    public function updateTransaction($the_query = '', $the_array = '', $single_record = true)
    {
        $this->o_elog->write("The query coming in is: $the_query", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->queryTransaction($the_query, $the_array, $single_record);
        if ($results === false) {
            $this->o_elog->write("Could not modify the record(s) - query was {$the_query}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        }
        return $results;
    }
    public function deleteTransaction($the_query = '', $the_array = '', $single_record = true)
    {
        $results = $this->queryTransaction($the_query, $the_array, $single_record);
        if ($results === false) {
            $this->o_elog->write("Could not delete the record(s)", LOG_OFF, __METHOD__ . '.' . __LINE__);
        }
        return $results;
    }

    ### Complex Commands
    /**
     *  Does an insert based on a prepared query.
     *  @param $a_values (array) - the values to be insert
     *  @param $o_pdo_statement (obj) - the object pointing to the prepared statement
     *  @param $table_name (str) - the name of the table into which an insert is happening
     *  @return bool - success or failure
    **/
    public function insertPrepared($a_values = '', $o_pdo_statement = '', $table_name = '')
    {
        $this->o_elog->setFromMethod(__METHOD__);
        if ($o_pdo_statement != '') {
            $this->o_pdo_statement = $o_pdo_statement;
        }
        if (is_array($a_values) && count($a_values) > 0) {
            $this->o_elog->write("" . var_export($a_values , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->resetNewIds();
            $results = $this->executeInsert($a_values, $table_name);
            if ($results === false) {
                $this->o_elog->write('Execute Failure', LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                $this->setSqlErrorMessage('pdo');
                $this->o_elog->write('PDO: ' . $this->getSqlErrorMessage(), LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                $this->setSqlErrorMessage('pdos');
                $this->o_elog->write('PDO_Statement: ' . $this->getSqlErrorMessage(), LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                $this->resetNewIds();
                return false;
            }
            return true;
        } else {
            $this->o_elog->write('The array of values for a prepared insert was empty.', 4);
            $this->resetNewIds();
            return false;
        }
    }
    /**
     *  Specialized version of execute which retains ids of each insert.
     *  @param $a_values (array) - see $this->execute for details
    **/
    public function executeInsert($a_values = '', $table_name = '')
    {
        if (is_array($a_values) && count($a_values) > 0) {
            $sequence_name = $this->db_type == 'pgsql' ? $this->getPgsqlSequenceName($table_name) : '' ;
            if (isset($a_values[0]) && is_array($a_values[0])) { // is an array of arrays, can not be mixed
                foreach ($a_values as $a_stuph) {
                    if ($this->executeInsert($a_stuph, $table_name) === false) {
                        return false;
                    }
                }
            } else { // should be a single record insert
                if ($this->execute($a_values, true) === false) {
                    return false;
                }
                $this->a_new_ids[] = $this->o_db->lastInsertId($sequence_name);
            }
            return true;
        } else {
            $this->o_elog->write('The function executeInsert requires a an array for its first parameter.', LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
            return false;
        }
    }
    /**
     *  Used for both modifying and deleting record(s)
     *  @param $the_query (str) - the sql statement, required, default is ''
     *  @param $a_values (array) - formated values for a prepared sql statement - optional, default is ''
     *  @param $single_record - if only a single record should be changed/deleted - optional, default is true
     *  @return bool - success or failure
    **/
    public function mdQuery($the_query = '', $a_values = '', $single_record = true)
    {
        $this->o_elog->setFromMethod(__METHOD__);
        if ($the_query == '') {
            $this->o_elog->write('The query must not be blank.', LOG_OFF);
            return false;
        }
        if ($a_values == '') {
            $this->affected_rows = $this->o_db->exec($the_query);
            if ($this->affected_rows === false) {
                $this->setSqlErrorMessage();
                $this->o_elog->write($this->getSqlErrorMessage(), LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                return false;
            } elseif ($single_record && $this->affected_rows > 1) {
                $this->o_elog->write('The query affected multiple records instead of a single one.', LOG_OFF);
                return false;
            } elseif ($this->affected_rows == 0) {
                $this->o_elog->write('The query affected no records.', LOG_OFF);
                return true;
            } else {
                return true;
            }
        } elseif (is_array($a_values) && count($a_values) > 0) {
            $o_pdo_stmt = $this->prepare($the_query);
            if ($o_pdo_stmt === false) {
                $this->setSqlErrorMessage('pdo');
                $this->o_elog->setFromMethod(__METHOD__);
                $this->o_elog->setFromLine(__LINE__);
                $this->o_elog->write('Could not prepare the query: ' . $this->getSqlErrorMessage(), LOG_OFF);
                return false;
            } else {
                return $this->mdQueryPrepared($a_values, $single_record, $o_pdo_stmt);
            }
        } else {
            $this->o_elog->write('The array of values for a prepared query was empty.', LOG_OFF);
            return false;
        }
    }
    public function mdQueryPrepared($a_values = '', $single_record = true, $o_pdo_statement = '')
    {
        if ($a_values == '') {
            return false;
        }
        if ($o_pdo_statement != '') {
            $this->o_pdo_statement = $o_pdo_statement;
        }
        if (is_array($a_values) && count($a_values) > 0) {
            if (isset($a_values[0]) && is_array($a_values[0])) { // array of arrays
                foreach ($a_values as $row) {
                    $results = $this->mdQueryPrepared($row, $single_record, $o_pdo_statement);
                    if ($results === false) {
                        $this->setSqlErrorMessage('pdos');
                        $this->o_elog->write("Could not execute the query: {$this->getSqlErrorMessage()}", LOG_OFF, __METHOD__ . '.' . __LINE__);
                        $this->o_elog->write('The array was: ' . var_export($a_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                        return false;
                    }
                }
                return true;
            } else {
                $results = $this->execute($a_values, $this->o_pdo_statement);
                if ($results === false) {
                    $this->setSqlErrorMessage('pdos');
                    $this->o_elog->write("Could not execute the query: {$this->getSqlErrorMessage()}", LOG_OFF, __METHOD__ . '.' . __LINE__);
                    $this->o_elog->write('The array was: ' . var_export($a_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                    return false;
                }
                return true;
            }
        } else {
            $this->o_elog->write('The array of values for a prepared query was empty.', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return false;
        }
    }
    /**
     *  Do a query.
     *  Has two params. The first is required. The second is required if the first param doesn't include a valid sql statement.
     *  @param $query_params (array) = default is empty str.  - required<pre>
     *      Should correspond to something like
     *      array('type'=>'search', 'table_name'=>'test_table', 'single_record'=>false, 'sql'=>'')</pre>
     *  @param $data (mixed)<pre>
     *      array(field_name => value) - data to be insert or modified
     *      'id, name, date' (str) - a string of fields to be returned in a search</pre>
     *  @param $where_values (array), array(field_name => value) - paramaters used to find records for search or modify
     *  @return bool
    **/
    public function query($query_params = '', $data = '', $where_values)
    {
        $default_params = array(
            'type'=>'',
            'table_name'=>'',
            'single_record'=>false,
            'sql' => '');
        if ($query_params == '') {
            $query_params = $default_params;
        } else {
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
                $this->o_elog->write("Where Params in method: " . var_export($where_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                $where = '';
                $a_where = array();
                foreach ($where_values as $values) {
                    $where .= $where != '' ? ' AND ' : '' ;
                    $where .= $values['field_name'] . $values['operator'] . ':' . $values['field_name'];
                    $a_where = array_merge($a_where, array($values['field_name'] =>$values['field_value']));
                }
                $query .= $where;
                $this->o_elog->write("a where is: " . var_export($a_where, true),  LOG_OFF, __METHOD__ . '.' . __LINE__);
                $this->o_elog->write("Query is: $query", LOG_OFF, __METHOD__ . '.' . __LINE__);
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
                    return $this->modify($query_params['sql'], $data, $query_params['single_record']);
                }
                $query = "UPDATE {$query_params['table_name']} ";
                $set_lines = '';
                $field_names = $this->prepareValues($data);
                foreach ($field_names as $field_name => $value) {
                    $set_lines .= $set_lines != '' ? ', ' : 'SET ';
                    $set_lines .= $field_name . ' = ' . $value;
                }
                $where_names = $this->prepareValues($where_values);
                $where_lines = '';
                foreach ($where_values as $values) {
                    $where_lines .= $where_lines != '' ? ' AND ' : '' ;
                    $where_lines .= $values['field_name'] . $values['operator'] . $values['field_value'];
                }
                $query .= $set_lines . $where_lines;
                return $this->modify($query, $data, $query_params['single_record']);
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
    public function searchPrepared($a_values = '', $o_pdo_statement = '', $type = 'assoc')
    {
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
        if ($o_pdo_statement != '') {
            $this->o_pdo_statement = $o_pdo_statement;
        }
        if (is_array($a_values) && count($a_values) > 0) {
            $this->o_elog->write("Array: " . var_export($a_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            if (isset($a_values[0]) && is_array($a_values[0])) {
                $a_results = array();
                foreach ($a_values as $row) {
                    if ($this->execute($row, $this->o_pdo_statement)) {
                        $a_results[] = $this->o_pdo_statement->fetchAll($fetch_style);
                    } else {
                        return false;
                    }
                }
            } else {
                if ($this->execute($a_values, $o_pdo_statement)) {
                    $a_results = $this->o_pdo_statement->fetchAll($fetch_style);
                } else {
                    $this->o_elog->write("Could not execute the query", LOG_OFF, __METHOD__ . '.' . __LINE__);
                    return false;
                }
            }
            return $a_results;
        } else {
            $this->o_elog->write('There was a problem with the array');
            $this->o_elog->write("a_values: " . var_export($a_values , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return false;
        }
    }

    ### Utility Functions
    /**
     *  Verifies that the php mysqli extension is installed
     *  Left over, not sure it is needed now
     *  @return bool
    **/
    private function mysqliInstalled()
    {
        if (function_exists('mysqli_connect')) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *  Changes array keys to be compatible with prepared statements
     *  @param array $array associative array, named keys, required
     *  @return array - fixed key names
    **/
    public function prepareKeys($array = '')
    {
        $a_new = array();
        if ($this->o_arr->isAssocArray($array)) {
            foreach ($array as $key=>$value) {
                $new_key = strpos($key, ':') === 0 ? $key : ':' . $key;
                $a_new[$new_key] = $value;
            }
            return $a_new;
        } elseif ($this->o_arr->isAssocArray($array[0])) {
            foreach ($array as $a_keys) {
                $results = $this->prepareKeys($a_keys);
                if ($results === false) {
                    return false;
                }
                $a_new[] = $results;
            }
            return $a_new;
        } else {
            $this->o_elog->setFrom(basename(__FILE__), __METHOD__);
            $this->o_elog->write('The array must be an associative array to fix.');
            return false;
        }
    }
    /**
     *  Changes array values to help build a prepared statement
     *  primarily the WHERE
     *  @param $array (array) - key/value pairs to fix
     *  @return array - fixed where needed
    **/
    public function prepareValues($array){
        $a_new = array();
        if ($this->o_arr->isAssocArray($array)) {
            foreach ($array as $key=>$value) {
                $new_key = strpos($key, ':') === 0 ? $key : ':' . $key;
                $a_new[$key] = $new_key;
            }
            return $a_new;
        } elseif ($this->o_arr->isAssocArray($array[0])) {
            return $this->prepareValues($array[0]);
        } else {
            $this->o_elog->setFrom(basename(__FILE__), __METHOD__);
            $this->o_elog->write('The array must be an associative array to fix.');
            return false;
        }
    }
    /**
     *  Use the \PDO::quote function to make the string safe for use in a query.
     *  Used only when not using a prepared sql statement
     *  @see \PDO::quote
     *  @param $value (str)
     *  @return string - quoted string
    **/
    public function quoteString($value)
    {
        return $this->o_db->quote($value);
    }
    /**
     *  Returns a list of the fields from a database table.
     *  @param $table_name (str) - name of the table
     *  @return array - field names
    **/
    public function selectDbFields($table_name = '')
    {
        if ($table_name != '') {
            $get_columns = "SHOW COLUMNS FROM {$table_name}";
            return $this->search($qet_columns);
        } else {
            $this->o_elog->setFrom(basename(__FILE__), __METHOD__);
            $this->o_elog->write('You must specify a table name for this to work.');
            return false;
        }
    }
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
     *  Determines if any required keys are missing
     *  @param array $a_required_keys required
     *  @param array $a_check_values required
     *  @return array $a_missing_keys
    **/
    public function findMissingKeys($a_required_keys = '', $a_check_values = '')
    {
        if ($a_required_keys == '' || $a_check_values == '') { return array(); }
        $a_missing_keys = array();
        foreach ($a_required_keys as $key) {
            if (
                array_key_exists($key, $a_check_values)
                ||
                array_key_exists(':' . $key, $a_check_values)
                ||
                array_key_exists(str_replace(':', '', $key), $a_check_values)
            ) {
                // we are happy
            } else {
                $a_missing_keys[] = $key;
            }
        }
        return $a_missing_keys;
    }
    /**
     *  Removes unwanted key=>values for a prepared query
     *  @param array $a_required_keys
     *  @param array $a_values the array which needs cleaned up
     *  @return array $a_fixed_values
    **/
    public function removeBadKeys($a_required_keys = '', $a_values = '')
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
    ### Magic Method fix
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}
