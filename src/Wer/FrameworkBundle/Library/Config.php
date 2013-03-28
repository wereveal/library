<?php
/**
 *  Create Constants from the configuration database
 *  @file Config.php
 *  @class Config
 *  @author William Reveal  <wer@wereveal.com>
 *  @ingroup wer_framework classes
 *  @version  2.3.0
 *  @date 2013-03-27 15:45:27
 *  @par Change Log
 *      v2.3.0 mostly changes for FIG-standards
 *  @par Wer Framework v4.0.0
**/
namespace Wer\FrameworkBundle\Library;

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
            include_once SM_CONFIGS_PATH . '/fallback_constants.php';
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
                    $key   = strtoupper($row['name']);
                    switch ($row['value']) {
                        case 'true':
                            define("{$key}", true);
                            break;
                        case 'false':
                            define("{$key}", false);
                            break;
                        default:
                            $value = $row['value'];
                            define("{$key}", "{$value}");
                    }
                }
                return true;
            } else {
                $this->o_elog->setFrom(basename(__FILE__), __METHOD__);
                $this->o_elog->write($this->o_db->getSqlErrorMessage());
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
            define('THEME_DIR', '/themes/default');
        }
        if (defined('SM_THEME_NAME')) {
            if (defined('SM_THEMES_DIR')) {
                define('SM_THEME_DIR', SM_THEMES_DIR . '/' . SM_THEME_NAME);
            } else {
                define('SM_THEME_DIR', SM_DIR . '/themes/' . SM_THEME_NAME);
            }
        } elseif (defined('SM_THEMES_DIR')) {
            define('SM_THEME_DIR', SM_THEMES_DIR . '/default');
        } else {
            define('SM_THEME_DIR', SM_DIR . '/themes/default');
        }
        define('CSS_DIR',          THEME_DIR . '/' . CSS_DIR_NAME);
        define('HTML_DIR',         THEME_DIR . '/' . HTML_DIR_NAME);
        define('JS_DIR',           THEME_DIR . '/' . JS_DIR_NAME);
        define('TEMPLATES_DIR',    THEME_DIR . '/' . TEMPLATES_DIR_NAME);
        define('THEME_IMAGE_DIR',  THEME_DIR . '/' . IMAGE_DIR_NAME);
        define('SM_CSS_DIR',         SM_THEME_DIR . '/' . CSS_DIR_NAME);
        define('SM_HTML_DIR',        SM_THEME_DIR . '/' . HTML_DIR_NAME);
        define('SM_JS_DIR',          SM_THEME_DIR . '/' . JS_DIR_NAME);
        define('SM_TEMPLATES_DIR',   SM_THEME_DIR . '/' . TEMPLATES_DIR_NAME);
        define('SM_THEME_IMAGE_DIR', SM_THEME_DIR . '/' . IMAGE_DIR_NAME);
        define('THEME_PATH',          SITE_PATH . THEME_DIR);
        define('SM_THEME_PATH',       SITE_PATH . SM_THEME_DIR);
        define('CSS_PATH',            SITE_PATH . CSS_DIR);
        define('HTML_PATH',           SITE_PATH . HTML_DIR);
        define('JS_PATH',             SITE_PATH . JS_DIR);
        define('TEMPLATES_PATH',      SITE_PATH . TEMPLATES_DIR);
        define('THEME_IMAGE_PATH',    SITE_PATH . THEME_IMAGE_DIR);
        define('SM_CSS_PATH',         SITE_PATH . SM_CSS_DIR);
        define('SM_HTML_PATH',        SITE_PATH . SM_HTML_DIR);
        define('SM_JS_PATH',          SITE_PATH . SM_JS_DIR);
        define('SM_TEMPLATES_PATH',   SITE_PATH . SM_TEMPLATES_DIR);
        define('SM_THEME_IMAGE_PATH', SITE_PATH . SM_THEME_IMAGE_DIR);
    }
    private function selectConfigList()
    {
        $select_query = 'SELECT name, value FROM sm_config ORDER BY name';
        return $this->o_db->search($select_query);
    }
    private function createNewConfigs()
    {
        include_once SM_CONFIGS_PATH . '/fallback_constants_array.php';
        if ($this->o_db->startTransaction()) {
            $query = "
                INSERT INTO sm_config (name, value)
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
