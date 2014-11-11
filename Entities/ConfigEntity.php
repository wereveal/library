<?php
/**
 *  @brief Creates a entity object.
 *  @file ConfigEntity.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Entities
 *  @class ConfigEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1
 *  @date 2014-09-11 12:53:16
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
 *      v1.0.1 - minor change to the comments 09/11/2014 wer
 *      v1.0.0 - Initial version 04/01/2014 wer</pre>
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;
use Ritc\Library\Abstracts\Base;
use Ritc\Library\Core\DbModel;

class ConfigEntity extends Base implements EntityInterface
{
    protected $a_configs;
    protected $o_db;
    protected $o_elog;
    protected $private_properties;

    public function __contruct(DbModel $o_db)
    {
        $this->o_db = $o_db;
        $this->a_configs = $this->selectConfigList();
    }

    ### Database Functions ###
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
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8
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
     *  @param array $a_values must have at least one record.
     *      array is in the form of [['key' => 'value'],['key' => 'value']]
     *  @return bool
     */
    public function createConfigRecords(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $query = "
            INSERT INTO {$this->db_prefix}config (config_name, config_value)
            VALUES (?, ?)";
        return $this->o_db->insert($query, $a_constants, "{$this->db_prefix}config"));
    }
    public function selectConfigList()
    {
        $select_query = "
            SELECT config_name, config_value
            FROM {$this->db_prefix}config
            ORDER BY config_name
        ";
        return $this->o_db->search($select_query);
    }
    public function tableExists()
    {
        $db_prefix = $this->o_db->getDbPrefix();
        $a_tables = $this->o_db->selectDbTables();
        if (array_search("{$db_prefix}config", $a_tables, true) === false) {
            return false;
        }
        return true;
    }

    ### SETters and GETters ###
    public function setDb(DbModel $o_db)
    {
        $this->o_db = $o_db;
    }
    /**
     *  returns an array of the properties
     *  @return array
    **/
    public function getAllProperties()
    {
        return ['a_configs' => $this->a_config];
    }
    /**
     *  Sets the values of all the entity properties.
     *  @param array $a_entity e.g., array('config_id'=>'', 'config_name'=>'', 'config_value'=>'')
     *  @return bool success or failure
    **/
    public function setAllProperties(array $a_configs = array())
    {
        if (count($a_configs) > 0) {
            $this->a_configs = $a_configs;
        }
    }
}
