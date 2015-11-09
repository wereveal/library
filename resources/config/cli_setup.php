<?php
/**
 *  This file sets up the app when doing things at the command line
 *  Required to get the entire app to work.
 *  @file cli_setup.php
 *  @namespace Ritc
 *  @defgroup ritc_library
 *  @{
 *      @version 5.0
 *      @defgroup abstracts classes that are extended by other classes
 *      @ingroup ritc_library
 *      @defgroup basic stuff that doesn't have another place
 *      @ingroup ritc_library
 *      @defgroup configs place for configurations
 *      @ingroup ritc_library
 *      @defgroup controllers controllers in the app
 *      @ingroup ritc_library
 *      @defgroup entities defines the tables in the database
 *      @ingroup ritc_library
 *      @defgroup factories classes that create objects
 *      @ingroup ritc_library
 *      @defgroup helper classes that do helper things
 *      @ingroup ritc_library
 *      @defgroup interfaces files that define what a class should have
 *      @ingroup ritc_library
 *      @defgroup models classes that do database calls
 *      @ingroup ritc_library
 *      @defgroup services classes that are normally injected into other classes
 *      @ingroup ritc_library
 *      @defgroup tests classes that test other classes
 *      @ingroup ritc_library
 *      @defgroup traits functions that are common to multiple classes
 *      @ingroup ritc_library
 *      @defgroup views classes that provide the end user experience
 *      @ingroup ritc_library
 *  }
 *  @defgroup main_app_name
 *  @{
 *      @version 1.0
 *      @defgroup app_abstracts abstract class files
 *      @ingroup main_app_name
 *      @defgroup app_controllers controller files
 *      @ingroup main_app_name
 *      @defgroup app_entities defines the tables in the database
 *      @ingroup main_app_name
 *      @defgroup app_interfaces files that define what a class should have
 *      @ingroup main_app_name
 *      @defgroup app_models classes that do database calls
 *      @ingroup main_app_name
 *      @defgroup app_tests classes that test other classes
 *      @ingroup main_app_name
 *      @defgroup app_traits functions that are common to multiple classes
 *      @ingroup main_app_name
 *      @defgroup app_views classes that provide the end user experience
 *      @ingroup main_app_name
 *  }
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
