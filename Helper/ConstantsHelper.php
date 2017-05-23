<?php
/**
 * @brief     Create Constants from the constants database
 * @ingroup   lib_helper
 * @file      Ritc/Library/Helper/ConstantsHelper.php
 * @namespace Ritc\Library\Helper
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   4.0.2
 * @date      2017-01-27 10:59:55
 * @note <b>Change Log</b>
 *   v4.0.2 - bug fix.                                                              - 2017-01-27 wer
 * - v4.0.1 - bug fix. Wondering if another is still here.                          - 02/22/2016 wer
 * - v4.0.0 - renamed to reflect what it was doing. Since it isn't                  - 01/17/2015 wer
 *              a service, moved it Ritc\Library\Helper namespace.
 * - v3.3.0 - moved some contant definitions into this class                        - 12/10/2014 wer
 *              the constants.php file was doing these definitions but
 *              it seemed that this should be done here. Also, moved a
 *              couple constant names into the database.
 * - v3.2.0 - changed to use DI/IOC                                                 - 12/10/2014 wer
 * - v3.1.5 - moved to the Services Namespace in the Library                        - 11/15/2014 wer
 * - v3.1.4 - changed to match changes in ConstantsModel                            - 11/13/2014 wer
 * - v3.1.3 - changed to implment the changes in Base class                         - 09/23/2014 wer
 * - v3.1.2 - bug fixes                                                             - 09/18/2014 wer
 * - v3.1.1 - made it so the constants table name will be assigned from the         - 02/24/2014 wer
 *              the db_prefix variable set from the db confuration
 *              (created in PdoFactory, passed on to DbModel).
 * - v3.1.0 - made it so it will create the constants table if it does not exist.   - 01/31/2014 wer
 *            Other changes to adjust to not having a theme based app.
 * - v3.0.3 - package change                                                        - 12/19/2013 wer
 * - v3.0.2 - bug fixes, minor changes                                              - 2013-11-08 wer
 * - v3.0.1 - refactoring for database class change                                 - 2013-11-06 wer
 * - v3.0.0 - Modified for new framework file hierarchy                             - 2013-04-30 wer
 * - v2.3.0 - mostly changes for FIG-standards
 */
namespace Ritc\Library\Helper;

use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class ConstantsHelper
 * @class   ConstantsHelper
 * @package Ritc\Library\Helper
 */
class ConstantsHelper
{
    use LogitTraits;

    /** @var bool */
    private $created = false;
    /** @var ConstantsHelper */
    private static $instance;
    /** @var \Ritc\Library\Models\ConstantsModel */
    private $o_constants_model;

    /**
     * ConstantsHelper constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    private function __construct(Di $o_di)
    {
        $meth = __METHOD__ . '.';
        $o_db = $o_di->get('db');
        $this->o_constants_model = new ConstantsModel($o_db);
        if (defined('DEVELOPER_MODE')) {
            if (DEVELOPER_MODE) {
                $this->o_elog = $o_di->get('elog');
                $this->o_constants_model->setElog($this->o_elog);
            }
        }
        $this->created = $this->createConstants();
        if ($this->created === false) {
            $this->logIt("Could not create constants from db.", LOG_ALWAYS, $meth . __LINE__);
            if (defined('SRC_CONFIG_PATH')) {
                if(file_exists(SRC_CONFIG_PATH . '/fallback_constants.php')) {
                    include_once SRC_CONFIG_PATH . '/fallback_constants.php';
                }
                else {
                    $this->logIt("File: " . SRC_CONFIG_PATH . '/fallback_constants.php does not exist.', LOG_ALWAYS);
                    die ('A fatal error has occured. Please contact your web site administrator.');
                }
            }
            else {
                $this->logIt("SRC_CONFIG_PATH is not defined.", LOG_ALWAYS, $meth . __LINE__);
                die ('A fatal error has occured. Please contact your web site administrator.');
            }
            $this->o_constants_model->createNewConstants();
        }
        $this->createThemeConstants();
    }

    /**
     * Constants class is a singleton and this gets it started.
     * This is in my mind a legit use of a singleton as
     * Never should more than one instance of the constants ever be allowed to be created
     *
     * @param Di $o_di
     * @return object - instance of Constants
     */
    public static function start(Di $o_di)
    {
        if (!isset(self::$instance)) {
            self::$instance = new ConstantsHelper($o_di);
        }
        return self::$instance;
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->created;
    }

    /**
     * @return bool
     */
    private function createConstants()
    {
        $meth = __METHOD__ . '.';
        if ($this->created === false) {
            $a_constants = $this->o_constants_model->selectConstantsList();
            $this->logIt('Constants List -- ' . var_export($a_constants, TRUE), LOG_OFF, $meth . __LINE__);
            if (!empty($a_constants)) {
                foreach ($a_constants as $row) {
                    $key = strtoupper($row['const_name']);
                    if (!defined("{$key}")) {
                        switch ($row['const_value']) {
                            case 'true':
                                define("{$key}", true);
                                break;
                            case 'false':
                                define("{$key}", false);
                                break;
                            case null:
                            case 'null':
                                define("{$key}", null);
                                break;
                            default:
                                $value = $row['const_value'];
                                define("{$key}", "{$value}");
                        }
                    }
                }
                if (!defined('PRIVATE_DIR_NAME')) {
                    define('PRIVATE_DIR_NAME', 'private');
                }
                if (!defined('TMP_DIR_NAME')) {
                    define('TMP_DIR_NAME', 'tmp');
                }
                if (!defined('TMP_PATH')) {
                    if (file_exists(BASE_PATH . '/' . TMP_DIR_NAME)) {
                        define('TMP_PATH', BASE_PATH . '/' . TMP_DIR_NAME);
                    }
                    elseif (file_exists(PUBLIC_PATH . '/' . TMP_DIR_NAME)) {
                        define('TMP_PATH', PUBLIC_PATH . '/' . TMP_DIR_NAME);
                    }
                    else {
                        define('TMP_PATH', '/tmp');
                    }
                }
                if (!defined('PRIVATE_PATH')) {
                    if (file_exists(BASE_PATH . '/' . PRIVATE_DIR_NAME)) {
                        define('PRIVATE_PATH', BASE_PATH . '/' . PRIVATE_DIR_NAME);
                    }
                    elseif (file_exists(PUBLIC_PATH . '/' . PRIVATE_DIR_NAME)) {
                        define('PRIVATE_PATH', PUBLIC_PATH . '/' . PRIVATE_DIR_NAME);
                    }
                    else {
                        define('PRIVATE_PATH', '');
                    }
                }

                if (!defined('PUBLIC_DIR')) { // not sure why this would be true but here just in case
                    define('PUBLIC_DIR', '');
                }
                if (!defined('PUBLIC_PATH')) { // not sure why this would be true but here just in case
                    define('PUBLIC_PATH', $_SERVER['DOCUMENT_ROOT']);
                }
                if (!defined('ADMIN_DIR') && defined('ADMIN_DIR_NAME')) {
                    define('ADMIN_DIR',   PUBLIC_DIR . '/' . ADMIN_DIR_NAME);
                }
                if (!defined('ADMIN_PATH') && defined('ADMIN_DIR')) {
                    define('ADMIN_PATH',  PUBLIC_PATH . ADMIN_DIR);
                }
                if (!defined('ASSETS_DIR') && defined('ASSETS_DIR_NAME')) {
                    define('ASSETS_DIR',  PUBLIC_DIR . '/' . ASSETS_DIR_NAME);
                }
                if (!defined('ASSETS_PATH') && defined('ASSETS_DIR')) {
                    define('ASSETS_PATH', PUBLIC_PATH . ASSETS_DIR);
                }
                return true;
            }
            else {
                $log_message = 'Error:  ' . var_export($this->o_constants_model->getErrorMessage(), TRUE);
                $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Creates constants referring to the main assets for the primary (single) theme.
     * A theme may be unnamed, i.e. there is no theme. It uses the basic
     *     assets directory for everything. If there is a defined THEMES_DIR,
     *     that overrides the assets directory e.g. /themes
     */
    private function createThemeConstants()
    {
        if (!defined('ASSETS_DIR')) {
            return;
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
        define('THEME_PATH', PUBLIC_PATH . THEME_DIR);
        define('CSS_PATH',   PUBLIC_PATH . CSS_DIR);
        define('FILES_PATH', PUBLIC_PATH . FILES_DIR);
        define('HTML_PATH',  PUBLIC_PATH . HTML_DIR);
        define('IMAGE_PATH', PUBLIC_PATH . IMAGE_DIR);
        define('JS_PATH',    PUBLIC_PATH . JS_DIR);
        if (defined('THUMBS_DIR_NAME')) {
            define('THUMBS_DIR', IMAGE_DIR . '/' . THUMBS_DIR_NAME);
            define('THUMBS_PATH', PUBLIC_PATH . THUMBS_DIR);
        }
        if (defined('STAFF_DIR_NAME')) {
            define('STAFF_DIR', IMAGE_DIR . '/' . STAFF_DIR_NAME);
            define('STAFF_PATH', PUBLIC_PATH . STAFF_DIR);
        }
        if (defined('LIBS_DIR_NAME')) {
            define('LIBS_DIR', THEME_DIR . '/' . LIBS_DIR_NAME);
            define('LIBS_PATH', PUBLIC_PATH . LIBS_DIR);
        }
    }

    ### Magic Method fix
    /**
     * Clone is not allowed.
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}
