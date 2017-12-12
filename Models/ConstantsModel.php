<?php
/**
 * @brief     Creates a Model object.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/ConstantsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   3.0.2
 * @date      2017-12-12 11:33:45
 * @note      see ConstantsEntity for database table definition.
 * @note <b>Change Log</b>
 * - v3.0.2 - ModelException changes reflected here                         - 2017-12-12 wer
 * - v3.0.1 - Bug fixes                                                     - 2017-07-12 wer
 * - v3.0.0 - Refactored to use ModelException and bug fixes                - 2017-06-14 wer
 * - v2.5.0 - Removed unused property and setting of same                   - 2017-05-18 wer
 * - v2.4.2 - DbUtilityTraits change reflected here                         - 2017-05-09 wer
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

use Ritc\Library\Exceptions\ModelException;
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

    /**
     * ConstantsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'constants');
    }

    ### Database Functions ###
    # Methods required by Interface #
    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return int
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = [])
    {
        if (empty($a_values)) {
            throw new ModelException('No values provided to save.', 120);
        }
        $a_required_keys = [
            'const_name'
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
            return $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, returns all records if not provided
     * @param array $a_search_params optional, defaults to ['order_by' => 'const_name']
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
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
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Generic update for a record using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values)
    {
        if (!isset($a_values[$this->primary_index_name])
            || (!is_numeric($a_values[$this->primary_index_name]))
            || $a_values[$this->primary_index_name] < 1
        ) {
            $this->error_message = 'Required values missing';
            throw new ModelException($this->error_message, 320);
        }
        if (isset($a_values['const_name']) && $a_values['const_name'] == '') {
            unset($a_values['const_name']);
        }
        if (isset($a_values['const_immutable']) && !is_numeric($a_values['const_immutable'])) {
            unset($a_values['const_immutable']);
        }
        try {
            $results = $this->read([$this->primary_index_name => $a_values[$this->primary_index_name]]);
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            throw new ModelException($this->error_message, $e->getCode(), $e);
        }
        if ($results[0]['const_immutable'] == 1) {
            unset($a_values['const_name']);
            unset($a_values['const_value']);
            if (isset($a_values['const_immutable'])) {
                switch ($a_values['const_immutable']) {
                    case 0:
                    case 1:
                        break;
                    default:
                        $this->error_message = 'You must change the record to not immutable to change other values.';
                        throw new ModelException($this->error_message, 320);

                }
            }
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Generic deletes a record based on the id provided.
     * @param int $const_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($const_id = -1)
    {
        if ($const_id == -1) {
            throw new ModelException('Missing the id to delete.', 420);
        }
        $find_this = [$this->primary_index_name => $const_id];
        $a_params  = ['a_fields' => ['const_immutable']];
        try {
            $search_results = $this->read($find_this, $a_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getCodeText(435), 435, $e);
        }
        if (empty($search_results)) {
            $this->error_message = 'That record does not exist';
            throw new ModelException($this->error_message, 435);
        }
        if (!isset($search_results[0]['const_immutable'])) {
            $this->error_message = 'It can not be determined if the constant is immutable.';
            throw new ModelException($this->error_message, 435);
        }
        if ($search_results[0]['const_immutable'] == 0) {
            try {
                return $this->genericDelete($const_id);
            }
            catch (ModelException $e) {
                throw new ModelException($e->errorMessage(), $e->getCode());
            }
        }
        else {
            $this->error_message = 'Sorry, that constant can not be deleted.';
            throw new ModelException($this->error_message, 450);
        }
    }

    # Specialized CRUD methods #

    /**
     * Creates all the constants based on the fallback constants file.
     * @pre the fallback_constants_array.php file exists and has the desired constants.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createNewConstants()
    {
        // todo ConstantsModel.createNewConstants - need to add to tests
        $a_constants = include SRC_CONFIG_PATH . '/fallback_constants_array.php';
        try {
            $this->o_db->startTransaction();
        }
        catch (ModelException $e) {
            $message = "Could not start transaction.";
            throw new ModelException($message, 12, $e);
        }
        if ($this->tableExists() === false) {
            try {
                $this->createTable();
            }
            catch (ModelException $e) {
                $this->o_db->rollbackTransaction();
                throw new ModelException('Unable to create the table', 560, $e);
            }
        }
        try {
            $this->createConstantRecords($a_constants);
            try {
                $this->o_db->commitTransaction();
                return true;
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to commit the transaction';
                throw new ModelException($this->error_message, $e->getCode(), $e);
            }
        }
        catch (ModelException $e) {
            $this->error_message = "Unable to create the records.";
            throw new ModelException($this->error_message, $e->getCode(), $e);
        }
    }

    /**
     * Creates the database table to store the constants.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createTable()
    {
        // todo ConstantsModel.createTable - need to add to tests
        $db_type = $this->o_db->getDbType();
        switch ($db_type) {
            case 'pgsql':
                $sql_table = <<<SQL
                    CREATE TABLE IF NOT EXISTS {$this->db_table} (
                        const_id integer NOT NULL DEFAULT nextval('const_id_seq'::regclass),
                        const_name character varying(64) NOT NULL,
                        const_value character varying(64) NOT NULL,
                        const_immutable integer NOT NULL DEFAULT 0
                    )
SQL;
                $sql_sequence = "
                    CREATE SEQUENCE const_id_seq
                        START WITH 1
                        INCREMENT BY 1
                        NO MINVALUE
                        NO MAXVALUE
                        CACHE 1
                    ";
                try {
                    $this->o_db->rawExec($sql_sequence);
                    try {
                        $this->o_db->rawExec($sql_table);
                        return true;
                    }
                    catch (ModelException $e) {
                        throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                    }
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), $e->getCode(), $e);
                }
            case 'sqlite':
                $sql = <<<SQL
                    CREATE TABLE IF NOT EXISTS {$this->db_table} (
                        const_id INTEGER PRIMARY KEY ASC,
                        const_name TEXT,
                        const_value TEXT,
                        const_immutable INTEGER
                    )
SQL;
                try {
                    $this->o_db->rawExec($sql);
                    return true;
                }
                catch (ModelException $e) {
                    throw new ModelException('Unable to create table in sqlite', $e->getCode(), $e);
                }
            case 'mysql':
            default:
                $sql = <<<SQL
                    CREATE TABLE IF NOT EXISTS `{$this->db_table}` (
                        `const_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `const_name` varchar(64) NOT NULL,
                        `const_value` varchar(64) NOT NULL,
                        `const_immutable` int(1) NOT NULL DEFAULT 0
                        PRIMARY KEY (`const_id`),
                        UNIQUE KEY `const_key` (`const_name`)
                    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
SQL;
                try {
                    $this->o_db->rawExec($sql);
                    return true;
                }
                catch (ModelException $e) {
                    throw new ModelException('Unable to create table in mysql', $e->getCode(), $e);
                }
            // end default
        }
    }

    /**
     * Create the records in the constants table.
     * @param array $a_constants must have at least one record.
     *                           array is in the form of<code>
     *                           [
     *                           [
     *                           'const_name_value,
     *                           'const_value_value',
     *                           'const_immutable_value'
     *                           ],
     *                           [
     *                           'const_name_value,
     *                           'const_value_value',
     *                           'const_immutable_value'
     *                           ]
     *                           ]</code>
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createConstantRecords(array $a_constants = array())
    {
        // todo ConstantsModel.createConstantRecords - need to add to tests
        if ($a_constants == array()) {
            throw new ModelException('Missing values', 120);
        }
        $query = "
            INSERT INTO {$this->db_table} (const_name, const_value, const_immutable)
            VALUES (?, ?, ?)";
        try {
            return $this->o_db->insert($query, $a_constants, "{$this->db_table}");
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Selects the constants records.
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function selectConstantsList()
    {
        // todo ConstantsModel.selectConstantsList - need to add to tests
        try {
            return $this->read();
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Checks to see if the table exists.
     * @return bool
     */
    public function tableExists()
    {
        // todo ConstantsModel.tableExists - need to add to tests
        $lib_prefix = $this->o_db->getLibPrefix();
        $a_tables = $this->o_db->selectDbTables();
        if (array_search("{$lib_prefix}constants", $a_tables, true) === false) {
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
        $const_name = trim($const_name);
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
        // todo ConstantsModel.makeValidValue - need to add to tests
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
