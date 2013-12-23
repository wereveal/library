<?php
/**
 *  @brief Create Constants from the configuration database
 *  @file Config.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Config
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version  3.0.3
 *  @date 2013-12-19 07:58:56
 *  @note A part of the RITC Library v4
 *  @note <pre><b>Change Log</b>
 *      v3.0.3 - package change
 *      v3.0.2 - bug fixes, minor changes - 2013-11-08
 *      v3.0.1 - refactoring for database class change - 2013-11-06
 *      v3.0.0 - Modified for new framework file hierarchy - 2013-04-30
 *      v2.3.0 - mostly changes for FIG-standards
 *  </pre>
**/
namespace Ritc\Library\Core;


class Config extends namespace\Base
{
    private $created = false;
    protected $current_page;
    private static $instance;
    private $o_db;
    protected $o_elog;
    protected $private_properties;
    private function __construct(DbModel $o_db)
    {
        $this->o_db = $o_db;
        $this->o_elog = Elog::start();
        $this->setPrivateProperties();
        $this->created = $this->createConstants();
        if ($this->created === false) {
            $this->o_elog->write("Could not create constants from db.", LOG_OFF, __METHOD__ . '.' . __LINE__);
            if(file_exists(APP_CONFIG_PATH . '/fallback_constants.php')) {
                include_once APP_CONFIG_PATH . '/fallback_constants.php';
            } else {
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
                return true;
            } else {
                $this->o_elog->write($this->o_db->getSqlErrorMessage(), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            }
        } else {
            return true;
        }
    }
    private function createThemeConstants()
    {
        if (defined('THEME_NAME')) {
            if (defined('THEMES_DIR')) {
                define('THEME_DIR', THEMES_DIR . '/' . THEME_NAME);
            } else {
                define('THEME_DIR', '/themes' . '/' . THEME_NAME);
            }
        } elseif (defined('THEMES_DIR')) {
            define('THEME_DIR', THEMES_DIR . '/default');
        } else {
            define('THEME_DIR', '/assets/themes/default');
        }
        define('CSS_DIR',          THEME_DIR . '/' . CSS_DIR_NAME);
        define('HTML_DIR',         THEME_DIR . '/' . HTML_DIR_NAME);
        define('JS_DIR',           THEME_DIR . '/' . JS_DIR_NAME);
        define('THEME_IMAGE_DIR',  THEME_DIR . '/' . IMAGE_DIR_NAME);
        define('THEME_PATH',       SITE_PATH . THEME_DIR);
        define('CSS_PATH',         SITE_PATH . CSS_DIR);
        define('HTML_PATH',        SITE_PATH . HTML_DIR);
        define('JS_PATH',          SITE_PATH . JS_DIR);
        define('THEME_IMAGE_PATH', SITE_PATH . THEME_IMAGE_DIR);
    }
    private function selectConfigList()
    {
        $select_query = 'SELECT config_name, config_value FROM app_config ORDER BY config_name';
        return $this->o_db->search($select_query);
    }
    private function createNewConfigs()
    {
        $a_constants = require_once APP_CONFIG_PATH . '/fallback_constants_array.php';
        if ($this->o_db->startTransaction()) {
            $query = "
                INSERT INTO app_config (config_name, config_value)
                VALUES (?, ?)";
            if ($this->o_db->insert($query, $a_constants, 'ritc_config')) {
                if ($this->o_db->commitTransaction() === false) {
                    $this->o_elog->write("Could not commit new configs", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
                }
            } else {
                $this->o_db->rollbackTransaction();
                $this->o_elog->write("Could not Insert new configs", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
            }
        } else {
            $this->o_elog->write("Could not start transaction.", LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
    }

    ### Magic Method fix
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}
