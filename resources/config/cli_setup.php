<?php
/**
 * @brief     This file sets up the App.
 * @details   Required to get the entire framework to work. The only thing
 *            that changes primarily is the defgroup in this comment for Doxygen.
 * @file      setup.php
 * @namespace Ritc
 * @defgroup ritc
 * @{
 *      @defgroup ritc_library Library basic group of classes used to build other apps.
 *      @ingroup ritc
 *      @{
 *          @namespace Ritc\Library
 *          @version 5.5.0
 *          @defgroup abstracts Abstracts - Semi-Classes that are extended by other classes
 *          @ingroup ritc_library
 *          @defgroup lib_basic Basic Classes - Stuff that doesn't have another place
 *          @ingroup ritc_library
 *          @defgroup lib_configs Configs - Place for configurations
 *          @ingroup ritc_library
 *          @defgroup lib_controllers Controllers
 *          @ingroup ritc_library
 *          @defgroup lib_entities Entities - Defines the tables in the database
 *          @ingroup ritc_library
 *          @defgroup lilb_factories Factories - Classes that create objects
 *          @ingroup ritc_library
 *          @defgroup lib_helper Helpers - Classes that do helper things
 *          @ingroup ritc_library
 *          @defgroup lib_interfaces Interfaces - Files that define what a class should have
 *          @ingroup ritc_library
 *          @defgroup lib_models Models - Classes that do database calls
 *          @ingroup ritc_library
 *          @defgroup lib_services Services - Classes that are normally injected into other classes
 *          @ingroup ritc_library
 *          @defgroup lib_tests Tests - Classes that test other classes
 *          @ingroup ritc_library
 *          @defgroup lib_traits Traits - Functions that are common to multiple classes
 *          @ingroup ritc_library
 *          @defgroup lib_views Views - Classes that provide the end user experience
 *          @ingroup ritc_library
 *      @}
 *  @}
 *  @note <pre>
 *  NOTE: _path and _PATH indicates a full server path
 *        _dir and _DIR indicates the path in the site (URI)
 *        Both do not end with a slash
 *  </pre>
*/
namespace Ritc;

use Ritc\Library\Helper\ConstantsHelper;
use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Factories\TwigFactory;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;

if (!defined('SITE_PATH')) {
    define('SITE_PATH', __DIR__);
}
if (!defined('BASE_PATH')) {
    if (!isset($app_is_in)) {
        $app_is_in = 'external';
    }
    if ($app_is_in == 'site' || $app_is_in == 'htdocs' || $app_is_in == 'html') {
        define('BASE_PATH', SITE_PATH);
    }
    else {
        define('BASE_PATH', dirname(dirname(__FILE__)));
    }
}
if (!isset($rodb)) {
    $rodb = false;
}

require_once BASE_PATH . '/app/config/constants.php';

$loader = require_once VENDOR_PATH . '/autoload.php';
$my_classmap = require_once APP_PATH . '/config/autoload_classmap.php';
$loader->addClassMap($my_classmap);

$o_elog = Elog::start();
$o_elog->setIgnoreLogOff(false); // turns on logging globally ignoring LOG_OFF when set to true

$o_session = Session::start();

$o_di      = new Di();
$o_di->set('elog',    $o_elog);
$o_di->set('session', $o_session);

if ($_SERVER['SERVER_NAME'] == 'w3.qca.net') {
    $db_config_file = 'db_config.php';
}
else {
    $db_config_file = 'db_local_config.php';
}

$o_pdo = PdoFactory::start($db_config_file, 'rw', $o_di);

if ($o_pdo !== false) {
    $o_db = new DbModel($o_pdo, $db_config_file);
    if (!is_object($o_db)) {
        $o_elog->write("Could not create a new DbModel\n", LOG_ALWAYS);
        die("Could not get the database to work");
    }
    else {
        $o_di->set('db', $o_db);
        if (RODB) {
            $o_pdo_ro = PdoFactory::start($db_config_file, 'ro', $o_di);
            if ($o_pdo_ro !== false) {
                $o_db_ro = new DbModel($o_pdo_ro, $db_config_file);
                if (!is_object($o_db_ro)) {
                    $o_elog->write("Could not create a new DbModel for read only\n", LOG_ALWAYS);
                    die("Could not get the database to work");
                }
                $o_di->set('rodb', $o_db_ro);
            }
        }

        if (!ConstantsHelper::start($o_di)) {
            $o_elog->write("Couldn't create the constants\n", LOG_ALWAYS);
            require_once APP_CONFIG_PATH . '/fallback_constants.php';
        }
        /*
        $a_constants = get_defined_constants(true);
        $o_elog->write(var_export($a_constants['user'], true), LOG_OFF);
        */
        $o_router = new Router($o_di);
        $o_twig   = TwigFactory::getTwig('twig_config.php');
        $o_di->set('router',  $o_router);
        $o_di->set('tpl',     $o_twig);
    }
}
else {
    $o_elog->write("Couldn't connect to database\n", LOG_ALWAYS);
    die("Could not connect to the database");
}
