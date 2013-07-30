<?php
/**
 *  Create Constants from the configuration database
 *  @file Config.php
 *  @namespace Ritc\Library\Core
 *  @class Config
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @ingroup ritc_library library
 *  @version  3.0.0
 *  @date 2013-04-30 11:47:24
 *  @par Change Log
 *      v3.0.0 Modified for new framework file hierarchy
 *      v2.3.0 mostly changes for FIG-standards
 *  @par RITC Library v4.0.0
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstract\Base;
use Ritc\Library\Core\Database;
use Ritc\Library\Core\Elog;

class Config extends Base
{
    private $created = false;
    protected $current_page;
    private static $instance;
    private $o_db;
    protected $o_elog;
    protected $private_properties;
    private function __construct()
    {
        $this->o_elog = Elog::start();
        $this->setPrivateProperties();
        $this->o_db = Database::start();
        $this->o_db->connect();
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
     *  Config class is a singleton and this gets it started
     *  @return obj - instance of Config
    **/
    public static function start()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
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
        $select_query = 'SELECT config_name, config_value FROM wer_config ORDER BY config_name';
        return $this->o_db->search($select_query);
    }
    private function createNewConfigs()
    {
        include_once APP_CONFIG_PATH . '/fallback_constants_array.php';
        if ($this->o_db->startTransaction()) {
            $query = "
                INSERT INTO wer_config (config_name, config_value)
                VALUES (?, ?)";
            if ($this->o_db->insert($query, $a_constants, 'sm_config')) {
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
