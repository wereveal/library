<?php
/**
 * @brief     Creates a Model object.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/ConstantsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.4.1
 * @date      2017-02-15 15:32:55
 * @note      see ConstantsEntity for database table definition.
 * @note <b>Change Log</b>
 * - v2.4.1 - Refactoring of file structure reflected here                  - 2017-02-15 wer
 * - v2.4.0 - Implementing more of the DbUtilityTraits                      - 2017-01-27 wer
 * - v2.3.1 - Bug fix in create mysql table                                 - 2017-01-13 wer
 * - v2.3.0 - Refactoring of DbModel reflected here                         - 2016-03-18 wer
 * - v2.2.0 - Refactoring to provide better pgsql compatibility             - 11/22/2015 wer
 * - v2.1.0 - No longer extends Base class but uses LogitTraits             - 08/19/2015 wer
 * - v2.0.1 - Refactoring of Class Arrays required changes here             - 07/31/2015 wer
 * - v2.0.0 - Renamed to match functionality                                - 01/17/2015 wer
 * - v1.1.1 - Namespace changes elsewhere required changes here             - 11/15/2014 wer
 *              Doesn't use DI/IOC because of where it is initialized
 * - v1.1.0 - Changed from Entity to Model                                  - 11/13/2014 wer
 * - v1.0.1 - minor change to the comments                                  - 09/11/2014 wer
 * - v1.0.0 - Initial version                                               - 04/01/2014 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class ConstantsModel.
 * @class   ConstantsModel
 * @package Ritc\Library\Models
 */
class ConstantsModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /** @var array|bool */
    private $a_constants;

    /**
     * ConstantsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'constants', 'lib');
        $this->a_constants = $this->selectConstantsList();
    }

    ### Database Functions ###
    # Methods required by Interface #
    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return bool|int
     */
    public function create(array $a_values)
    {
        $meth = __METHOD__ . '.';
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth);
        $a_required_keys = array(
            'const_name',
            'const_value'
        );
        if (isset($a_values[0]) && is_array($a_values[0])) { // is an array of arrays
            foreach ($a_values as $key => $a_record) {
                if (!Arrays::hasRequiredKeys($a_record, $a_required_keys)) {
                    return false;
                }
                $a_values[$key]['const_name'] = $this->makeValidName($a_values[$key]['const_name']);
                $a_values[$key]['const_value'] = $this->makeValidValue($a_values[$key]['const_value']);
                $a_values[$key] = Arrays::createRequiredPairs($a_values[$key], ['const_name', 'const_value', 'const_immutable'], true);
            }
        }
        else {
            if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
                return false;
            }
            $a_values['const_name'] = $this->makeValidName($a_values['const_name']);
            $a_values['const_value'] = $this->makeValidValue($a_values['const_value']);
            $a_values = Arrays::createRequiredPairs($a_values, ['const_name', 'const_value', 'const_immutable'], true);
        }
        $sql = "
            INSERT INTO {$this->db_table} (const_name, const_value, const_immutable)
            VALUES (:const_name, :const_value, :const_immutable)
        ";
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        $a_table_info = [
            'table_name'  => $this->db_table,
            'column_name' => 'const_id'
        ];
        if ($this->o_db->insert($sql, $a_values, $a_table_info)) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, $meth . __LINE__);
            return $ids[0];
        }
        else {
            $this->logIt("Error Message: " . $this->o_db->getSqlErrorMessage(), LOG_OFF, $meth . __LINE__);
            return false;
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, returns all records if not provided
     * @param array $a_search_params optional, defaults to ['order_by' => 'const_name']
     * @return array|bool
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_values,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => $this->primary_index_name . ' ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        return $this->genericRead($a_parameters);
    }

    /**
     * Generic update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values)
    {
        $meth = __METHOD__ . '.';
        $log_message = 'Values Passed In:  ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        if (!isset($a_values[$this->primary_index_name])
            || $a_values[$this->primary_index_name] == ''
            || (!is_numeric($a_values[$this->primary_index_name]))
        ) {

            return false;
        }
        return $this->genericUpdate($a_values);
    }

    /**
     * Generic deletes a record based on the id provided.
     * @param int $const_id
     * @return bool
     */
    public function delete($const_id = -1)
    {
        if ($const_id == -1) { return false; }
        $search_results = $this->read([$this->primary_index_name => $const_id], ['a_fields' => ['const_immutable']]);
        if (isset($search_results[0]) && $search_results[0]['const_immutable'] == 1) {
            $this->error_message = 'Sorry, that constant can not be deleted.';
            return false;
        }
        $results = $this->genericDelete($const_id);
        if ($results === false) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
        }
        return $results;
    }

    # Specialized CRUD methods #
    /**
     * Creates all the constants based on the fallback constants file.
     * @pre the fallback_constants_array.php file exists and has the desired constants.
     * @return bool
     */
    public function createNewConstants()
    {
        // todo ConstantsModel.createNewConstants - need to figure out if this is a bug
        $a_constants = include SRC_CONFIG_PATH . '/fallback_constants_array.php';
        if ($this->o_db->startTransaction()) {
            if ($this->tableExists() === false) {
                if ($this->createTable() === false) {
                    $this->o_db->rollbackTransaction();
                    return false;
                }
            }
            if ($this->createConstantRecords($a_constants) === true) {
                if ($this->o_db->commitTransaction() === false) {
                    $this->logIt("Could not commit new constants", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                }
                return true;

            }
            else {
                $this->o_db->rollbackTransaction();
                $this->logIt("Could not Insert new constants", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
            }
        }
        else {
            $this->logIt("Could not start transaction.", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
        return false;
    }

    /**
     * Creates the database table to store the constants.
     * @return bool
     */
    public function createTable()
    {
        $db_type = $this->o_db->getDbType();
        switch ($db_type) {
            case 'pgsql':
                $sql_table = "
                    CREATE TABLE IF NOT EXISTS {$this->db_table} (
                        const_id integer NOT NULL DEFAULT nextval('const_id_seq'::regclass),
                        const_name character varying(64) NOT NULL,
                        const_value character varying(64) NOT NULL,
                        const_immutable integer NOT NULL DEFAULT 0
                    )
                ";
                $sql_sequence = "
                    CREATE SEQUENCE const_id_seq
                        START WITH 1
                        INCREMENT BY 1
                        NO MINVALUE
                        NO MAXVALUE
                        CACHE 1
                    ";
                $results = $this->o_db->rawExec($sql_sequence);
                if ($results !== false) {
                    $results2 = $this->o_db->rawExec($sql_table);
                    if ($results2 === false) {
                        return false;
                    }
                }
                return true;
            case 'sqlite':
                $sql = "
                    CREATE TABLE IF NOT EXISTS {$this->db_table} (
                        const_id INTEGER PRIMARY KEY ASC,
                        const_name TEXT,
                        const_value TEXT,
                        const_immutable INTEGER
                    )
                ";
                $results = $this->o_db->rawExec($sql);
                if ($results === false) {
                    return false;
                }
                return true;
            case 'mysql':
            default:
                $sql = "
                    CREATE TABLE IF NOT EXISTS `{$this->db_table}` (
                        `const_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `const_name` varchar(64) NOT NULL,
                        `const_value` varchar(64) NOT NULL,
                        `const_immutable` int(1) NOT NULL DEFAULT 0
                        PRIMARY KEY (`const_id`),
                        UNIQUE KEY `const_key` (`const_name`)
                    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
                ";
                $results = $this->o_db->rawExec($sql);
                if ($results === false) {
                    return false;
                }
                return true;
            // end default
        }
    }

    /**
     * Create the records in the constants table.
     * @param array $a_constants must have at least one record.
     * array is in the form of<code>
     * [
     *     [
     *         'const_name_value,
     *         'const_value_value',
     *         'const_immutable_value'
     *     ],
     *     [
     *         'const_name_value,
     *         'const_value_value',
     *         'const_immutable_value'
     *     ]
     * ]</code>
     * @return bool
     */
    public function createConstantRecords(array $a_constants = array())
    {
        if ($a_constants == array()) { return false; }
        $query = "
            INSERT INTO {$this->db_table} (const_name, const_value, const_immutable)
            VALUES (?, ?, ?)";
        return $this->o_db->insert($query, $a_constants, "{$this->db_table}");
    }

    /**
     * Selects the constants records.
     * @return array|bool
     */
    public function selectConstantsList()
    {
        return $this->read();
    }

    /**
     * Checks to see if the table exists.
     * @return bool
     */
    public function tableExists()
    {
        $db_prefix = $this->o_db->getDbPrefix();
        $a_tables = $this->o_db->selectDbTables();
        if (array_search("{$db_prefix}constants", $a_tables, true) === false) {
            return false;
        }
        return true;
    }

    ### Utility Methods ###
    /**
     * Changes the string to be a valid constant name.
     * @param $const_name
     * @return string
      */
    public function makeValidName($const_name = '')
    {
        $const_name = Strings::removeTagsWithDecode($const_name, ENT_QUOTES);
        $const_name = preg_replace("/[^a-zA-Z_ ]/", '', $const_name);
        $const_name = preg_replace('/(\s+)/i', '_', $const_name);
        return strtoupper($const_name);
    }

    /**
     * Changes the string to be a valid constant name.
     * @param string $const_value
     * @return string
     */
    public function makeValidValue($const_value = '')
    {
        $const_value = Strings::removeTagsWithDecode($const_value, ENT_QUOTES);
        return htmlentities($const_value,  ENT_QUOTES);
    }

    ### SETters and GETters ###
    /**
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function setDb(DbModel $o_db)
    {
        $this->o_db = $o_db;
    }

    /**
     * Returns Error Message, overrides trait method.
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->o_db->retrieveFormatedSqlErrorMessage();
    }
}
