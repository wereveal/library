<?php
/**
 *  @brief Creates a Model object.
 *  @file ConstantsModel.php
 *  @ingroup ritc_library models
 *  @namespace Ritc/Library/Models
 *  @class ConstantsModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 2.1.0
 *  @date 2015-08-19 13:04:47
 *  @note A file in the Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v2.1.0 - No longer extends Base class but uses LogitTraits = 08/19/2015 wer
 *      v2.0.1 - Refactoring of Class Arrays required changes here - 07/31/2015 wer
 *      v2.0.0 - Renamed to match functionality                    - 01/17/2015 wer
 *      v1.1.1 - Namespace changes elsewhere required changes here - 11/15/2014 wer
 *               Doesn't use DI/IOC because of where it is initialized
 *      v1.1.0 - Changed from Entity to Model                      - 11/13/2014 wer
 *      v1.0.1 - minor change to the comments                      - 09/11/2014 wer
 *      v1.0.0 - Initial version                                   - 04/01/2014 wer
 *  </pre>
 * @note see ConstantsEntity for database table definition.
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class ConstantsModel implements ModelInterface
{
    use LogitTraits;

    private $a_constants;
    private $db_prefix;
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->o_db        = $o_db;
        $this->db_prefix   = $o_db->getDbPrefix();
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
        $this->logIt(var_export($a_values, true), LOG_ON, $meth);
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
                $a_values[$key] = Arrays::createRequiredPairs($a_values[$key], ['const_name', 'const_value', 'const_immutable'], true);
            }
        }
        else {
            if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
                return false;
            }
            $a_values['const_name'] = $this->makeValidName($a_values['const_name']);
            $a_values = Arrays::createRequiredPairs($a_values, ['const_name', 'const_value', 'const_immutable'], true);
        }
        $sql = "
            INSERT INTO {$this->db_prefix}constants (const_name, const_value, const_immutable)
            VALUES (:const_name, :const_value, :const_immutable)
        ";
        $this->logIt(var_export($a_values, true), LOG_ON, $meth . __LINE__);
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}constants")) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, $meth . __LINE__);
            return $ids[0];
        }
        else {
            $this->logIt("Error Message: " . $this->o_db->getSqlErrorMessage(), LOG_ON, $meth . __LINE__);
            return false;
        }
    }
    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, returns all records if not provided
     * @param array $a_search_params optional, defaults to ['order_by' => 'const_name']
     * @return array|bool
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? ['order_by' => 'const_name']
                : $a_search_params;
            $a_allowed_keys = array(
                'const_id',
                'const_name',
                'const_value',
                'const_immutable'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY const_name";
        }
        $sql = "
            SELECT const_id, const_name, const_value, const_immutable
            FROM {$this->db_prefix}constants
            {$where}
        ";
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     * Generic update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values)
    {
        if (Arrays::hasRequiredKeys($a_values, ['const_id']) === false) {
            return false;
        }
        if (count($a_values) < 2) {
            return false;
        }
        if (isset($a_values['const_name'])) {
            $a_values['const_name'] = $this->makeValidName($a_values['const_name']);
        }
        $sql_set = $this->o_db->buildSqlSet($a_values, ['const_id']);
        $sql = "
            UPDATE {$this->db_prefix}constants
            {$sql_set}
            WHERE const_id  = :const_id
        ";
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     * Generic deletes a record based on the id provided.
     * @param int $const_id
     * @return array
     */
    public function delete($const_id = -1)
    {
        if ($const_id == -1) {
            return ['message' => 'The constant id is required', 'type' => 'failure'];
        }
        if ($this->read(['const_id' => $const_id]) === false) {
            return ['message' => 'The constant does not exist', 'type' => 'failure'];
        }
        $sql = "
            DELETE FROM {$this->db_prefix}constants
            WHERE const_id = :const_id
        ";
        $results = $this->o_db->delete($sql, array('const_id' => $const_id), true);
        if ($results) {
            if ($this->o_db->getAffectedRows() === 0) {
                $a_results = [
                    'message' => 'The constant was not deleted.',
                    'type'    => 'failure'
                ];
            }
            else {
                $a_results = [
                    'message' => 'Success!',
                    'type'    => 'success'
                ];
            }
        }
        else {
            $a_results = [
                'message' => 'A problem occurred and the constant was not deleted.',
                'type'    => 'failure'
            ];
        }
        return $a_results;
    }

    # Specialized CRUD methods #
    /**
     * Creates all the constants based on the fallback constants file.
     * @pre the fallback_constants_array.php file exists and has the desired constants.
     * @return bool
     */
    public function createNewConstants()
    {
        $a_constants = include APP_CONFIG_PATH . '/fallback_constants_array.php';
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
                    CREATE TABLE IF NOT EXISTS {$this->db_prefix}constants (
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
                $results = $this->o_db->rawQuery($sql_sequence);
                if ($results !== false) {
                    $results2 = $this->o_db->rawQuery($sql_table);
                    if ($results2 === false) {
                        return false;
                    }
                }
                return true;
            case 'sqlite':
                $sql = "
                    CREATE TABLE IF NOT EXISTS {$this->db_prefix}constants (
                        const_id INTEGER PRIMARY KEY ASC,
                        const_name TEXT,
                        const_value TEXT,
                        const_immutable INTEGER
                    )
                ";
                $results = $this->o_db->rawQuery($sql);
                if ($results === false) {
                    return false;
                }
                return true;
            case 'mysql':
            default:
                $sql = "
                    CREATE TABLE IF NOT EXISTS `{$this->db_prefix}constants` (
                        `const_id` int(11) NOT NULL AUTO_INCREMENT,
                        `const_name` varchar(64) NOT NULL,
                        `const_value` varchar(64) NOT NULL,
                        `const_immutable` int(1) NOT NULL DEFAULT 0
                        PRIMARY KEY (`const_id`),
                        UNIQUE KEY `const_key` (`const_name`)
                    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
                ";
                $results = $this->o_db->rawQuery($sql);
                if ($results === false) {
                    return false;
                }
                return true;
            // end default
        }
    }
    /**
     *  Create the records in the constants table.
     *  @param array $a_constants must have at least one record.
     *  array is in the form of
     *  [
     *      [
     *          'const_name_value,
     *          'const_value_value',
     *          'const_immutable_value'
     *      ],
     *      [
     *          'const_name_value,
     *          'const_value_value',
     *          'const_immutable_value'
     *      ]
     * ]
     *  @return bool
     */
    public function createConstantRecords(array $a_constants = array())
    {
        if ($a_constants == array()) { return false; }
        $query = "
            INSERT INTO {$this->db_prefix}constants (const_name, const_value, const_immutable)
            VALUES (?, ?, ?)";
        return $this->o_db->insert($query, $a_constants, "{$this->db_prefix}constants");
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
     *  Changes the string to be a valid constant name.
     *  @param $const_name
     *  @return string
     **/
    public function makeValidName($const_name = '')
    {
        $const_name = Strings::removeTags($const_name);
        $const_name = preg_replace("/[^a-zA-Z_ ]/", '', $const_name);
        $const_name = preg_replace('/(\s+)/i', '_', $const_name);
        return strtoupper($const_name);
    }

    ### SETters and GETters ###
    public function setDb(DbModel $o_db)
    {
        $this->o_db = $o_db;
    }
    public function getErrorMessage()
    {
        $this->o_db->getSqlErrorMessage();
    }
}
