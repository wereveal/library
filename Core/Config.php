<?php
/**
 *  @brief Create Constants from the configuration database
 *  @file Config.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Config
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version  3.1.1
 *  @date 2014-02-24 16:39:56
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v3.1.1 - made it so the config table name will be assigned from the - 02/24/2014 wer
 *               the db_prefix variable set from the db confuration
 *               (created in DbFactory, passed on to DbModel).
 *      v3.1.0 - made it so it will create the config table if it does not exist.
 *               Other changes to adjust to not having a theme based app. - 01/31/2014 wer
 *      v3.0.3 - package change - 12/19/2013 wer
 *      v3.0.2 - bug fixes, minor changes - 2013-11-08 wer
 *      v3.0.1 - refactoring for database class change - 2013-11-06 wer
 *      v3.0.0 - Modified for new framework file hierarchy - 2013-04-30 wer
 *      v2.3.0 - mostly changes for FIG-standards
 *  </pre>
**/
namespace Ritc\Library\Core;

class Config extends Base
{
    private $created = false;
    protected $current_page;
    private $db_prefix;
    private static $instance;
    private $o_db;
    protected $o_elog;
    protected $private_properties;

    private function __construct(DbModel $o_db)
    {
        $this->o_db = $o_db;
        $this->o_elog = Elog::start();
        $this->setPrivateProperties();
        $this->db_prefix = $o_db->getDbPrefix();
        $this->created = $this->createConstants();
        if ($this->created === false) {
            $this->o_elog->write("Could not create constants from db.", LOG_OFF, __METHOD__ . '.' . __LINE__);
            if (defined('APP_CONFIG_PATH')) {
                if(file_exists(APP_CONFIG_PATH . '/fallback_constants.php')) {
                    include_once APP_CONFIG_PATH . '/fallback_constants.php';
                }
                else {
                    die ('A fatal error has occured. Please contact your web site administrator.');
                }
            }
            else {
                die ('A fatal error has occured. Please contact your web site administrator.');
            }
            $this->createNewConfigs();
        }
        $this->createThemeConstants();
    }

    /**
     * Config class is a singleton and this gets it started.
     * This is in my mind a legit use of a singleton as
     * Never should more than one instance of the config ever be allowed to be created
     *
     * @param DbModel $o_db
     *
     * @return object - instance of Config
     */
    public static function start(DbModel $o_db)
    {
        if (!isset(self::$instance)) {
            self::$instance = new Config($o_db);
        }
        return self::$instance;
    }
    public function getSuccess()
    {
        return $this->created;
    }
    private function createConstants()
    {
        if ($this->created === false) {
            $a_config = $this->selectConfigList();
            if (is_array($a_config) && count($a_config) > 0) {
                $this->o_elog->write("List of Configs: " . var_export($a_config, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                foreach ($a_config as $row) {
                    $key   = strtoupper($row['config_name']);
                    if (!defined("{$key}")) {
                        switch ($row['config_value']) {
                            case 'true':
                                define("{$key}", true);
                                break;
                            case 'false':
                                define("{$key}", false);
                                break;
                            default:
                                $value = $row['config_value'];
                                define("{$key}", "{$value}");
                        }
                    }
                }
                return true;
            }
            else {
                $this->o_elog->write($this->o_db->getSqlErrorMessage(), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     *  Creates constants referring to the main assets for the primary (single) theme.
     *  A theme may be unnamed, i.e. there is no theme. It uses the basic
     *      assets directory for everything. If there is a defined THEMES_DIR,
     *      that overrides the assets directory e.g. /themes
    **/
    private function createThemeConstants()
    {
        if (!defined('ASSETS_DIR_NAME')) {
            define('ASSETS_DIR_NAME', 'assets');
        }
        if (!defined('ASSETS_DIR')) {
            define('ASSETS_DIR', '/' . ASSETS_DIR_NAME);
        }
        if (!defined('THEMES_DIR')) {
            define('THEMES_DIR', ASSETS_DIR);
        }
        if (defined('THEME_NAME')) {
            if (THEME_NAME == '') {
                if (THEMES_DIR == '') {
                    define('THEME_DIR', ASSETS_DIR);
                }
                else {
                    define('THEME_DIR', THEMES_DIR);
                }
            }
            else {
                if (THEMES_DIR == '') {
                    define('THEME_DIR', ASSETS_DIR . '/themes/' . THEME_NAME);
                }
                else {
                    define('THEME_DIR', THEMES_DIR . '/' . THEME_NAME);
                }
            }
        }
        else {
            define('THEME_NAME', '');
            if (THEMES_DIR == '') {
                define('THEME_DIR', ASSETS_DIR);
            }
            else {
                define('THEME_DIR', THEMES_DIR);
            }
        }
        if (!defined('CSS_DIR_NAME')) {
            define('CSS_DIR_NAME', 'css');
        }
        if (!defined('HTML_DIR_NAME')) {
            define('HTML_DIR_NAME', 'html');
        }
        if (!defined('JS_DIR_NAME')) {
            define('JS_DIR_NAME', 'js');
        }
        if (!defined('IMAGE_DIR_NAME')) {
            define('IMAGE_DIR_NAME', 'images');
        }
        if (!defined('FILES_DIR_NAME')) {
            define('FILES_DIR_NAME', 'files');
        }
        define('CSS_DIR',    THEME_DIR . '/' . CSS_DIR_NAME);
        define('FILES_DIR',  THEME_DIR . '/' . FILES_DIR_NAME);
        define('HTML_DIR',   THEME_DIR . '/' . HTML_DIR_NAME);
        define('IMAGE_DIR',  THEME_DIR . '/' . IMAGE_DIR_NAME);
        define('JS_DIR',     THEME_DIR . '/' . JS_DIR_NAME);
        define('THEME_PATH', SITE_PATH . THEME_DIR);
        define('CSS_PATH',   SITE_PATH . CSS_DIR);
        define('FILES_PATH', SITE_PATH . FILES_DIR);
        define('HTML_PATH',  SITE_PATH . HTML_DIR);
        define('IMAGE_PATH', SITE_PATH . IMAGE_DIR);
        define('JS_PATH',    SITE_PATH . JS_DIR);
        if (defined('THUMBS_DIR_NAME')) {
            define('THUMBS_DIR', IMAGES_DIR . '/' . THUMBS_DIR_NAME);
            define('THUMBS_PATH', SITE_PATH . THUMBS_DIR);
        }
        if (defined('STAFF_DIR_NAME')) {
            define('STAFF_DIR', IMAGES_DIR . '/' . STAFF_DIR_NAME);
            define('STAFF_PATH', SITE_PATH . STAFF_DIR);
        }
        if (defined('LIBS_DIR_NAME')) {
            define('LIBS_DIR', THEME_DIR . '/' . LIBS_DIR_NAME);
            define('LIBS_PATH', SITE_PATH . LIBS_DIR);
        }
    }
    private function selectConfigList()
    {
        $select_query = "
            SELECT config_name, config_value
            FROM {$this->db_prefix}config
            ORDER BY config_name
        ";
        return $this->o_db->search($select_query);
    }
    private function createNewConfigs()
    {
        $a_constants = require_once APP_CONFIG_PATH . '/fallback_constants_array.php';
        $a_tables = $this->o_db->selectDbTables();
        if ($this->o_db->startTransaction()) {
            if (array_search("{$this->db_prefix}config", $a_tables) === false) {
                $db_type = $this->o_db->getDbType();
                switch ($db_type) {
                    case 'pgsql':
                        $sql = "
                            CREATE TABLE IF NOT EXISTS {$this->db_prefix}config (
                                config_id integer NOT NULL,
                                config_name character varying(64),
                                config_value character varying(64)
                            )
                        ";
                        $sql_sequence = "
                            CREATE SEQUENCE {$this->db_prefix}config_config_id_seq
                                START WITH 1
                                INCREMENT BY 1
                                NO MINVALUE
                                NO MAXVALUE
                                CACHE 1
                            ";
                        $results = $this->o_db->query($sql);
                        if ($results !== false) {
                            $results2 = $this->o_db->query($sql_sequence);
                            if ($results2 === false) {
                                return;
                            }
                        }
                        break;
                    case 'sqlite':
                        $sql = "
                            CREATE TABLE IF NOT EXISTS {$this->db_prefix}config (
                                config_id INTEGER PRIMARY KEY ASC,
                                config_name TEXT,
                                config_value TEXT
                            )
                        ";
                        $results = $this->o_db->query($sql);
                        if ($results === false) {
                            return;
                        }
                        break;
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
                        $results = $this->o_db->query($sql);
                        if ($results === false) {
                            return;
                        }
                    // end default
                }
            }
            $query = "
                INSERT INTO {$this->db_prefix}config (config_name, config_value)
                VALUES (?, ?)";
            if ($this->o_db->insert($query, $a_constants, "{$this->db_prefix}config")) {
                if ($this->o_db->commitTransaction() === false) {
                    $this->o_elog->write("Could not commit new configs", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                }
            }
            else {
                $this->o_db->rollbackTransaction();
                $this->o_elog->write("Could not Insert new configs", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
            }
        }
        else {
            $this->o_elog->write("Could not start transaction.", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
    }

    ### Magic Method fix
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}
