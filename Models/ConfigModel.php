<?php
/**
 *  @brief Creates a Model object.
 *  @file ConfigModel.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Models
 *  @class ConfigModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.2.1
 *  @date 2014-11-15 14:24:43
 *  @note A file in the Ritc Library
 *  @note <b>SQL for table<b>
 *  <pre>
 *  MySQL
 *  CREATE TABLE `{dbPrefix}config` (
 *    `config_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `config_name` varchar(64) NOT NULL,
 *    `config_value` varchar(64) NOT NULL,
 *    PRIMARY KEY (`config_id`),
 *    UNIQUE KEY `config_name` (`config_name`)
 *  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4
 *
 *  PostgreSQL
 *  CREATE SEQUENCE config_id_seq;
 *  ALTER TABLE public.config_id_seq OWNER TO dbOwner;
 *  CREATE TABLE config (
 *      config_id integer DEFAULT nextval('config_id_seq'::regclass) NOT NULL,
 *      config_name character varying(64) NOT NULL,
 *      config_value character varying(64) NOT NULL
 *  );
 *  ALTER TABLE public.{dbPrefix}config OWNER TO dbOwner;
 *  ALTER TABLE ONLY {dbPrefix}config
 *      ADD CONSTRAINT {dbPrefix}config_pkey PRIMARY KEY (config_id);
 *  ALTER TABLE ONLY {dbPrefix}config
 *      ADD CONSTRAINT {dbPrefix}config_config_name_key UNIQUE (config_name);
 *
 *  Expected config key=>value pairs
 *
 *  INSERT INTO config (config_name, config_value) VALUES
 *  ('DISPLAY_DATE_FORMAT', 'm/d/Y'),
 *  ('EMAIL_DOMAIN', 'revealitconsulting.com'),
 *  ('EMAIL_FORM_TO', 'bill@revealitconsulting.com'),
 *  ('ERROR_EMAIL_ADDRESS', 'webmaster@revealitconsulting.com'),
 *  ('PAGE_META_DESCRIPTION', 'Reveal IT Consulting'),
 *  ('PAGE_META_KEYWORDS', 'Reveal IT Consulting'),
 *  ('PAGE_TEMPLATE', 'index.twig'),
 *  ('PAGE_TITLE', 'Reveal IT Consulting'),
 *  ('THEMES_DIR', ''),
 *  ('THEME_NAME', ''),
 *  ('ADMIN_THEME_NAME', ''),
 *  ('CSS_DIR_NAME', 'css'),
 *  ('HTML_DIR_NAME', 'html'),
 *  ('JS_DIR_NAME', 'js'),
 *  ('IMAGE_DIR_NAME', 'images'),
 *  ('ADMIN_DIR_NAME', 'manager'),
 *  ('ASSETS_DIR_NAME', 'assets'),
 *  ('FILES_DIR_NAME', 'files'),
 *  ('DISPLAY_PHONE_FORMAT', 'XXX-XXX-XXXX'),
 *  ('RIGHTS_HOLDER', 'Reveal IT Consulting')
 *  </pre>
 *  @note <pre><b>Change Log</b>
 *      v1.1.1 - Namespace changes elsewhere required changes here - 11/15/2014 wer
 *               Doesn't use DI/IOC because of where it is initialized
 *      v1.1.0 - Changed from Entity to Model                      - 11/13/2014 wer
 *      v1.0.1 - minor change to the comments                      - 09/11/2014 wer
 *      v1.0.0 - Initial version                                   - 04/01/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;

class ConfigModel extends Base implements ModelInterface
{
    private $a_configs;
    private $db_prefix;
    private $o_arrays;
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->o_db      = $o_db;
        $this->a_configs = $this->selectConfigList();
        $this->o_arrays  = new Arrays();
        $this->o_strings = new Strings();
        $this->db_prefix = $this->o_db->getDbPrefix();

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
        $a_required_keys = array(
            'config_name',
            'config_value'
        );
        if (isset($a_values[0]) && is_array($a_values[0])) { // is an array of arrays
            foreach ($a_values as $a_record) {
                if (!$this->o_arrays->hasRequiredKeys($a_required_keys, $a_record)) {
                    return false;
                }
            }
        }
        else {
            if (!$this->o_arrays->hasRequiredKeys($a_required_keys, $a_values)) {
                return false;
            }
        }
        $a_values['config_name'] = $this->makeValidName($a_values['config_name']);
        $sql = "
            INSERT INTO {$this->db_prefix}config (config_name, config_value)
            VALUES (:config_name, :config_value)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}config")) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        }
        else {
            return false;
        }
    }
    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, returns all records if not provided
     * @param array $a_search_params optional, defaults to ['order_by' => 'config_name']
     * @return array|bool
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? ['order_by' => 'config_name']
                : $a_search_params;
            $a_allowed_keys = array(
                'config_id',
                'config_name',
                'config_value'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY config_name";
        }
        $sql = "
            SELECT config_id, config_name, config_value
            FROM {$this->db_prefix}config
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
        if ($this->o_arrays->hasRequiredKeys(array('config_id', 'config_value'), $a_values) === false) {
            return false;
        }
        $sql_set = $this->o_db->buildSqlSet($a_values, array('config_id'));
        $sql = "
            UPDATE {$this->db_prefix}config
            {$sql_set}
            WHERE config_id  = :config_id
        ";
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     * Generic deletes a record based on the id provided.
     * @param int $config_id
     * @return bool
     */
    public function delete($config_id = -1)
    {
        if ($config_id == '') {
            return false;
        }
        if ($this->read(['config_id' => $config_id]) === false) {
            return false; // config doesn't exist
        }
        $sql = "
            DELETE FROM {$this->db_prefix}config
            WHERE config_id = :config_id
        ";
        return $this->o_db->delete($sql, array('config_id' => $config_id), true);
    }

    # Specialized CRUD methods #
    /**
     * Creates all the configs based on the fallback constants file.
     * @pre the fallback_constants_array.php file exists and has the desired constants.
     * @return bool
     */
    public function createNewConfigs()
    {
        $a_constants = include APP_CONFIG_PATH . '/fallback_constants_array.php';
        if ($this->o_db->startTransaction()) {
            if ($this->tableExists() === false) {
                if ($this->createTable() === false) {
                    $this->o_db->rollbackTransaction();
                    return false;
                }
            }
            if ($this->createConfigRecords($a_constants) === true) {
                if ($this->o_db->commitTransaction() === false) {
                    $this->logIt("Could not commit new configs", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                }
                return true;

            }
            else {
                $this->o_db->rollbackTransaction();
                $this->logIt("Could not Insert new configs", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
            }
        }
        else {
            $this->logIt("Could not start transaction.", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
        return false;
    }
    /**
     * Creates the database table to store the configs.
     * @return bool
     */
    public function createTable()
    {
        $db_type = $this->o_db->getDbType();
        switch ($db_type) {
            case 'pgsql':
                $sql_table = "
                    CREATE TABLE IF NOT EXISTS {$this->db_prefix}config (
                        config_id integer NOT NULL DEFAULT nextval('config_id_seq'::regclass),
                        config_name character varying(64),
                        config_value character varying(64)
                    )
                ";
                $sql_sequence = "
                    CREATE SEQUENCE config_id_seq
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
                    CREATE TABLE IF NOT EXISTS {$this->db_prefix}config (
                        config_id INTEGER PRIMARY KEY ASC,
                        config_name TEXT,
                        config_value TEXT
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
                    CREATE TABLE IF NOT EXISTS `{$this->db_prefix}config` (
                        `config_id` int(11) NOT NULL AUTO_INCREMENT,
                        `config_name` varchar(64) NOT NULL,
                        `config_value` varchar(64) NOT NULL,
                        PRIMARY KEY (`config_id`),
                        UNIQUE KEY `config_key` (`config_name`)
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
     *  Create the records in the config table.
     *  @param array $a_constants must have at least one record.
     *      array is in the form of [['key' => 'value'],['key' => 'value']]
     *  @return bool
     */
    public function createConfigRecords(array $a_constants = array())
    {
        if ($a_constants == array()) { return false; }
        $query = "
            INSERT INTO {$this->db_prefix}config (config_name, config_value)
            VALUES (?, ?)";
        return $this->o_db->insert($query, $a_constants, "{$this->db_prefix}config");
    }
    /**
     * Selects the configuration records.
     * @return array|bool
     */
    public function selectConfigList()
    {
        $select_query = "
            SELECT config_name, config_value
            FROM {$this->db_prefix}config
            ORDER BY config_name
        ";
        return $this->o_db->search($select_query);
    }
    /**
     * Checks to see if the table exists.
     * @return bool
     */
    public function tableExists()
    {
        $db_prefix = $this->o_db->getDbPrefix();
        $a_tables = $this->o_db->selectDbTables();
        if (array_search("{$db_prefix}config", $a_tables, true) === false) {
            return false;
        }
        return true;
    }

    ### Utility Methods ###
    /**
     *  Changes the string to be a valid config name.
     *  @param $config_name
     *  @return string
     **/
    public function makeValidName($config_name = '')
    {
        $config_name = $this->o_strings->removeTags($config_name);
        $config_name = preg_replace("/[^a-zA-Z_ ]/", '', $config_name);
        $config_name = preg_replace('/(\s+)/i', '_', $config_name);
        return strtoupper($config_name);
    }

    ### SETters and GETters ###
    public function setDb(DbModel $o_db)
    {
        $this->o_db = $o_db;
    }
}
