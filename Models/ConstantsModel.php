<?php
/**
 * Class ConstantsModel
 *
 * @package Ritc_Library
 */

namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Services\DbModel;

/**
 * Does all the database CRUD stuff for the constants table plus
 * other app/business logic that sets up the app with a bunch of required constants.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v4.1.0
 * @date    2018-11-09 12:44:47
 * @change_log
 * - v4.1.0 - New method to retrieve record by the constant name            - 2018-11-09 wer
 * - v4.0.0 - Refactored to extend ModelException                           - 2018-06-15 wer
 * - v3.0.0 - Refactored to use ModelException and bug fixes                - 2017-06-14 wer
 * - v2.5.0 - Removed unused property and setting of same                   - 2017-05-18 wer
 * - v2.4.0 - Implementing more of the DbUtilityTraits                      - 2017-01-27 wer
 * - v2.3.0 - Refactoring of DbModel reflected here                         - 2016-03-18 wer
 * - v2.2.0 - Refactoring to provide better pgsql compatibility             - 11/22/2015 wer
 * - v2.1.0 - No longer extends Base class but uses LogitTraits             - 08/19/2015 wer
 * - v2.0.0 - Renamed to match functionality                                - 01/17/2015 wer
 * - v1.1.0 - Changed from Entity to Model                                  - 11/13/2014 wer
 * - v1.0.0 - Initial version                                               - 04/01/2014 wer
 */
class ConstantsModel extends ModelAbstract
{
    /**
     * ConstantsModel constructor.
     *
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'constants');
        $this->setRequiredKeys(['const_name']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    ### Overrides Abstract ###
    /**
     * Generic create a record using the values provided.
     * Filters a couple values which is not available in abstract version.
     *
     * @param array $a_values
     * @param bool  $allow_pin
     * @return array
     * @throws ModelException
     */
    public function create(array $a_values = [], bool $allow_pin = false):array
    {
        if (empty($a_values)) {
            $err_code = ExceptionHelper::getCodeNumberModel('create missing values');
            throw new ModelException('No values provided to save.', $err_code);
        }
        if (!empty($a_values['const_name'])) {
            $a_values['const_name'] = $this->makeValidName($a_values['const_name']);
        }
        else {
            $err_code = ExceptionHelper::getCodeNumberModel('create missing values');
            throw new ModelException('Constant name required.', $err_code);
        }
        if (!empty($a_values['const_value'])) {
            $a_values['const_value'] = $this->makeValidValue($a_values['const_value']);
        }
        $a_psql   = [
            'table_name'  => $this->db_table,
            'column_name' => $this->primary_index_name
        ];
        $a_params = [
            'a_required_keys' => $this->a_required_keys,
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => $a_psql,
            'allow_pin'       => $allow_pin
        ];
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Updates a record using the values provided.
     * Filters values which is not available in abstract method.
     *
     * @param array $a_values
     * @param array $a_immutable Optional, param required by abstract.
     * @return bool
     * @throws ModelException
     */
    public function update(array $a_values = [], array $a_immutable = ['const_name']):bool
    {
        if (!isset($a_values[$this->primary_index_name]) ||
            (!is_numeric( $a_values[$this->primary_index_name])) ||
            $a_values[$this->primary_index_name] < 1
        ) {
            $this->error_message = 'Required values missing';
            $err_code = ExceptionHelper::getCodeNumberModel('update missing primary');
            throw new ModelException($this->error_message, $err_code);
        }
        if (isset($a_values['const_name']) && $a_values['const_name'] === '') {
            unset($a_values['const_name']);
        }
        try {
            $results = $this->readById($a_values[$this->primary_index_name]);
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            $message             = 'Cannot read the record to be updated.';
            throw new ModelException($message, $e->getCode(), $e);
        }
        if ($results['const_immutable'] === 'true') {
            foreach ($a_immutable as $field_name) {
                unset($a_values[$field_name]);
            }
        }
        else if (!empty($a_values['const_name'])) {
            $a_values['const_name'] = $this->makeValidName($a_values['const_name']);
        }
        if (!empty($a_values['const_value'])) {
            $a_values['const_value'] = $this->makeValidValue($a_values['const_value']);
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    # Specialized CRUD methods #
    /**
     * Creates all the constants based on the fallback constants file.
     *
     * @pre the fallback_constants_array.php file exists and has the desired constants.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createNewConstants():bool
    {
        $file_w_path = SRC_CONFIG_PATH . '/fallback_constants_array.php';
        if (file_exists($file_w_path)) {
            $a_constants = include $file_w_path;
        }
        else {
            throw new ModelException('Values not available', ExceptionHelper::getCodeNumberModel('missing values'));
        }
        try {
            $this->o_db->startTransaction();
        }
        catch (ModelException $e) {
            $message = 'Could not start transaction.';
            throw new ModelException($message, ExceptionHelper::getCodeNumberModel('transaction start'), $e);
        }
        if (!$this->tableExists('lib_constants')) {
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
            $this->error_message = 'Unable to create the records.';
            throw new ModelException($this->error_message, $e->getCode(), $e);
        }
    }

    /**
     * Creates the database table to store the constants.
     *
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createTable():bool
    {
        $db_type = $this->o_db->getDbType();
        switch ($db_type) {
            case 'pgsql':
                $sql_table    = <<<SQL
                    CREATE TABLE IF NOT EXISTS {$this->db_table} (
                        const_id integer NOT NULL DEFAULT nextval('const_id_seq'::regclass),
                        const_name character varying(64) NOT NULL,
                        const_value character varying(64) NOT NULL,
                        const_immutable character varying(10) NOT NULL DEFAULT 'false'::character varying
                    )
SQL;
                $sql_sequence = '
                    CREATE SEQUENCE const_id_seq
                        START WITH 1
                        INCREMENT BY 1
                        NO MINVALUE
                        NO MAXVALUE
                        CACHE 1
                    ';
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
                        const_immutable TEXT
                    )
SQL;
                try {
                    $this->o_db->rawExec($sql);
                }
                catch (ModelException $e) {
                    throw new ModelException('Unable to create table in sqlite', $e->getCode(), $e);
                }
                return true;
            case 'mysql':
            default:
                $sql = <<<SQL
                    CREATE TABLE IF NOT EXISTS `{$this->db_table}` (
                        `const_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `const_name` varchar(64) NOT NULL,
                        `const_value` varchar(64) NOT NULL,
                        `const_immutable` enum('true','false') NOT NULL DEFAULT 'false',
                        PRIMARY KEY (`const_id`),
                        UNIQUE KEY `const_name` (`const_name`)
                    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
SQL;
                try {
                    $this->o_db->rawExec($sql);
                }
                catch (ModelException $e) {
                    throw new ModelException('Unable to create table in mysql', $e->getCode(), $e);
                }
            return true;
            // end default
        }
    }

    /**
     * Create the records in the constants table.
     *
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
    public function createConstantRecords(array $a_constants = array()):bool
    {
        if ($a_constants === []) {
            throw new ModelException('Missing values', 120);
        }
        $query = "
            INSERT INTO {$this->db_table} (const_name, const_value, const_immutable)
            VALUES (?, ?, ?)";
        try {
            return $this->o_db->insert($query, $a_constants, $this->db_table);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the values for a specific constant record.
     *
     * @param string $const_name
     * @return array
     * @throws ModelException
     */
    public function selectByConstantName($const_name = ''):array
    {
        try {
            $a_results = $this->read(['const_name' => $const_name]);
            if (!empty($a_results[0])) {
                return $a_results[0];
            }
            return [];
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Selects the constants records.
     *
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function selectConstantsList()
    {
        try {
            return $this->read();
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    ### Utility Methods ###
    /**
     * Changes the string to be a valid constant name.
     *
     * @param $const_name
     * @return string
     */
    public function makeValidName($const_name = ''):string
    {
        $const_name = Strings::removeTagsWithDecode($const_name, ENT_QUOTES);
        $const_name = preg_replace('/[^a-zA-Z_ ]/', '', $const_name);
        $const_name = trim($const_name);
        $const_name = preg_replace('/(\s+)/i', '_', $const_name);
        return strtoupper($const_name);
    }

    /**
     * Changes the string to be a valid constant name.
     *
     * @param string $const_value
     * @return string
     */
    public function makeValidValue($const_value = ''):string
    {
        $const_value = Strings::removeTagsWithDecode($const_value, ENT_QUOTES);
        return htmlentities($const_value, ENT_QUOTES);
    }

}
