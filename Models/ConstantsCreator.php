<?php
/**
 * @brief     Create Constants from the constants database
 * @ingroup   lib_helper
 * @file      Ritc/Library/Models/ConstantsCreator.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   5.1.1
 * @date      2018-04-09 10:45:22
 * @note <b>Change Log</b>
 * - v5.1.1 - Bug fix                                                               - 2018-04-09 wer
 * - v5.1.0 - Bug fixes, added additional constants for Lib and Admin               - 2018-04-03 wer
 *            Added method to build TWIG_PREFIXES on the fly from the
 *            twig_prefix table
 * - v5.0.0 - Renamed, moved, and added ModelException throwing.                    - 2017-07-16 wer
 * - v4.1.0 - Refactor based on refactoring of ConstantsModel                       - 2017-06-20 wer
 * - v4.0.2 - bug fix.                                                              - 2017-01-27 wer
 * - v4.0.1 - bug fix. Wondering if another is still here.                          - 02/22/2016 wer
 * - v4.0.0 - renamed to reflect what it was doing. Since it isn't                  - 01/17/2015 wer
 *            a service, moved it Ritc\Library\Helper namespace.
 * - v3.3.0 - moved some contant definitions into this class                        - 12/10/2014 wer
 *            the constants.php file was doing these definitions but
 *            it seemed that this should be done here. Also, moved a
 *            couple constant names into the database.
 * - v3.2.0 - changed to use DI/IOC                                                 - 12/10/2014 wer
 * - v3.1.5 - moved to the Services Namespace in the Library                        - 11/15/2014 wer
 * - v3.1.4 - changed to match changes in ConstantsModel                            - 11/13/2014 wer
 * - v3.1.3 - changed to implment the changes in Base class                         - 09/23/2014 wer
 * - v3.1.2 - bug fixes                                                             - 09/18/2014 wer
 * - v3.1.1 - made it so the constants table name will be assigned from the         - 02/24/2014 wer
 *            the db_prefix variable set from the db confuration
 *            (created in PdoFactory, passed on to DbModel).
 * - v3.1.0 - made it so it will create the constants table if it does not exist.   - 01/31/2014 wer
 *            Other changes to adjust to not having a theme based app.
 * - v3.0.3 - package change                                                        - 12/19/2013 wer
 * - v3.0.2 - bug fixes, minor changes                                              - 2013-11-08 wer
 * - v3.0.1 - refactoring for database class change                                 - 2013-11-06 wer
 * - v3.0.0 - Modified for new framework file hierarchy                             - 2013-04-30 wer
 * - v2.3.0 - mostly changes for FIG-standards
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class ConstantsCreator
 * @class   ConstantsCreator
 * @package Ritc\Library\Helper
 */
class ConstantsCreator
{
    use LogitTraits;

    /** @var bool */
    private $created = false;
    /** @var ConstantsCreator */
    private static $instance;
    /** @var \Ritc\Library\Services\DbModel $o_db */
    private $o_db;

    /**
     * ConstantsCreator constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    private function __construct(Di $o_di)
    {
        $this->o_db = $o_di->get('db');
        $this->o_elog = $o_di->get('elog');
    }

    /**
     * Constants class is a singleton and this gets it started.
     * This is in my mind a legit use of a singleton as
     * Never should more than one instance of the constants ever be allowed to be created
     * @param Di $o_di
     * @return object - instance of Constants
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public static function start(Di $o_di)
    {
        if (!isset(self::$instance)) {
            try {
                self::$instance = new ConstantsCreator($o_di);
            }
            catch (\Error $e) {
                throw new ModelException('Unable to create an instance of ConstantsCreator: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    /**
     * Public function to create the constants.
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function defineConstants()
    {
        $meth = __METHOD__ . '.';
        try {
            $results = $this->createConstants();
            if ($results) {
                $this->createAssetsConstants();
                $this->createTwigConstants();
            }
            else {
                if (defined('SRC_CONFIG_PATH')) {
                    if(file_exists(SRC_CONFIG_PATH . '/fallback_constants.php')) {
                        include_once SRC_CONFIG_PATH . '/fallback_constants.php';
                    }
                    else {
                        $this->logIt("File: " . SRC_CONFIG_PATH . '/fallback_constants.php does not exist.', LOG_ALWAYS);
                        throw new ModelException('A fatal error has occured. Please contact your web site administrator.');
                    }
                }
                else {
                    $this->logIt("SRC_CONFIG_PATH is not defined.", LOG_ALWAYS, $meth . __LINE__);
                    throw new ModelException('A fatal error has occured. Please contact your web site administrator.');
                }
                try {
                    $o_const_model = new ConstantsModel($this->o_db);
                }
                catch (\Error $e) {
                    $message = 'Unable to create an instance of the ConstantsModel: ' . $e->getMessage();
                    $this->logIt($message, LOG_ALWAYS, $meth . __LINE__);
                    throw new ModelException($message, 800);
                }
                try {
                    $o_const_model->createNewConstants();
                    $this->createAssetsConstants();
                }
                catch (ModelException $e) {
                    $this->logIt($e->errorMessage(), LOG_ALWAYS, $meth . __LINE__);
                    throw new ModelException('A fatal error has occurred. Please contact your web site administrator.');
                }
            }
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to create constants' . $e->errorMessage(), 10);
        }
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->created;
    }

    /**
     * Creates the constants used throughout the website based on database values.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    private function createConstants()
    {
        $meth = __METHOD__ . '.';
        if (!defined('BASE_PATH')) {
            throw new ModelException('BASE_PATH is not defined', ExceptionHelper::getCodeNumberModel('missing value'));
        }
        if (!defined('PUBLIC_PATH')) { // not sure why this would be true but here just in case
            throw new ModelException('PUBLIC_PATH is not defined', ExceptionHelper::getCodeNumberModel('missing value'));
        }
        try {
            $o_const_model = new ConstantsModel($this->o_db);
        }
        catch (\Error $e) {
            $message = 'Unable to create an instance of the ConstantsModel: ' . $e->getMessage();
            $this->logIt($message, LOG_ALWAYS, $meth . __LINE__);
            throw new ModelException($message, 800);
        }
        $o_const_model->setElog($this->o_elog);
        if ($this->created === false) {
            try {
                $a_constants = $o_const_model->selectConstantsList();
            }
            catch (ModelException $e) {
                $log_message = 'Error:  ' . $e->errorMessage();
                $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
                throw new ModelException($log_message, ExceptionHelper::getCodeNumberModel('records_not_found'));

            }
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
                if (!defined('PUBLIC_DIR')) { // not sure why this would be true but here just in case
                    define('PUBLIC_DIR', '');
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
                $log_message = 'Error: Unable to retrieve the list of constants.';
                $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
                throw new ModelException($log_message, ExceptionHelper::getCodeNumberModel('records_not_found'));
            }
        }
        else {
            return true;
        }
    }

    /**
     * Creates constants referring to the main assets for the primary (single) theme.
     * A theme may be unnamed, i.e. there is no theme. It uses the basic
     *     assets directory for everything. If there is a defined THEMES_DIR,
     *     that overrides the assets directory e.g. /themes
     */
    private function createAssetsConstants()
    {
        if (!defined('ASSETS_DIR')) {
            return;
        }
        if (!defined('CSS_DIR_NAME')) {
            define('CSS_DIR_NAME', 'css');
        }
        if (!defined('FILES_DIR_NAME')) {
            define('FILES_DIR_NAME', 'files');
        }
        if (!defined('FONTS_DIR_NAME')) {
            define('FONTS_DIR_NAME', 'fonts');
        }
        if (!defined('HTML_DIR_NAME')) {
            define('HTML_DIR_NAME', 'html');
        }
        if (!defined('IMAGE_DIR_NAME')) {
            define('IMAGE_DIR_NAME', 'images');
        }
        if (!defined('JS_DIR_NAME')) {
            define('JS_DIR_NAME', 'js');
        }
        if (!defined('SCSS_DIR_NAME')) {
            define('SCSS_DIR_NAME', 'scss');
        }
        if (!defined('VENDOR_DIR_NAME')) {
            define('VENDOR_DIR_NAME', 'vendor');
        }
        define('CSS_DIR',       ASSETS_DIR . '/' . CSS_DIR_NAME);
        define('FILES_DIR',     ASSETS_DIR . '/' . FILES_DIR_NAME);
        define('FONTS_DIR',     ASSETS_DIR . '/' . FONTS_DIR_NAME);
        define('HTML_DIR',      ASSETS_DIR . '/' . HTML_DIR_NAME);
        define('IMAGE_DIR',     ASSETS_DIR . '/' . IMAGE_DIR_NAME);
        define('JS_DIR',        ASSETS_DIR . '/' . JS_DIR_NAME);
        define('SCSS_DIR',      ASSETS_DIR . '/' . SCSS_DIR_NAME);
        define('VENDOR_ASSETS', ASSETS_DIR . '/' . VENDOR_DIR_NAME);
        define('CSS_PATH',   PUBLIC_PATH . CSS_DIR);
        define('FILES_PATH', PUBLIC_PATH . FILES_DIR);
        define('FONTS_PATH', PUBLIC_PATH . FONTS_DIR);
        define('HTML_PATH',  PUBLIC_PATH . HTML_DIR);
        define('IMAGE_PATH', PUBLIC_PATH . IMAGE_DIR);
        define('JS_PATH',    PUBLIC_PATH . JS_DIR);
        define('SCSS_PATH',  PUBLIC_PATH . SCSS_DIR);
        if (defined('THUMBS_DIR_NAME')) {
            define('THUMBS_DIR', IMAGE_DIR . '/' . THUMBS_DIR_NAME);
            define('THUMBS_PATH', PUBLIC_PATH . THUMBS_DIR);
        }
        if (defined('STAFF_DIR_NAME')) {
            define('STAFF_DIR', IMAGE_DIR . '/' . STAFF_DIR_NAME);
            define('STAFF_PATH', PUBLIC_PATH . STAFF_DIR);
        }
    }

    /**
     * Creates the global constants for twig prefixes used in rendering templates.
     */
    private function createTwigConstants()
    {
        $o_twig_prefix = new TwigPrefixModel($this->o_db);
        $default_twig_prefix = 'site_';
        try {
            $a_results = $o_twig_prefix->read();
        }
        catch (ModelException $e) {
            error_log('Unable to create TwigPrefixModel instance.');
        }
        if (!empty($a_results)) {
            foreach ($a_results as $a_record) {
                $twig_prefix = $a_record['tp_prefix'];
                $const_name = strtoupper($a_record['tp_prefix']) . 'TWIG_PREFIX';
                if (!defined($const_name)) {
                    define($const_name, $twig_prefix);
                }
                if ($a_record['tp_default'] == 'true') {
                    $default_twig_prefix = $twig_prefix;
                }
            }
        }
        if (!defined('TWIG_PREFIX')) {
            define('TWIG_PREFIX', $default_twig_prefix);
        }
        if (!defined('LIB_TWIG_PREFIX')) {
            define('LIB_TWIG_PREFIX', 'lib_');
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
