<?php
/**
 *  This file sets up the app when doing things at the command line
 *  Required to get the entire app to work.
 *  @file cli_setup.php
 *  @namespace Ritc
 *  @defgroup ritc_library
 *  @{
 *      @version 5.0
 *      @defgroup abstracts
 *      @ingroup ritc_library
 *      @defgroup basic
 *      @ingroup ritc_library
 *      @defgroup configs
 *      @ingroup ritc_library
 *      @defgroup controllers
 *      @ingroup ritc_library
 *      @defgroup entities
 *      @ingroup ritc_library
 *      @defgroup helper classes that do helper things
 *      @ingroup ritc_library
 *      @defgroup interfaces
 *      @ingroup ritc_library
 *      @defgroup models
 *      @ingroup ritc_library
 *      @defgroup services
 *      @ingroup ritc_library
 *      @defgroup tests
 *      @ingroup ritc_library
 *      @defgroup views
 *      @ingroup ritc_library
 *  }
 *  @defgroup main_app_name
 *  @{
 *      @version 1.0
 *      @defgroup controllers controller files
 *      @ingroup main_app_name
 *      @defgroup views classes that create views
 *      @ingroup main_app_name
 *      @defgroup forms files that define and create forms
 *      @ingroup views
 *      @defgroup model files that do database operations
 *      @ingroup main_app_name
 *      @defgroup tests unitTesting
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
use Ritc\Library\Factories\DbFactory;
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
    if (!isset($app_in)) {
        $app_in = 'external';
    }
    if ($app_in == 'site' || $app_in == 'htdocs' || $app_in == 'html') {
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

$o_dbf = DbFactory::start($db_config_file, 'rw');
$o_dbf->setElog($o_elog);
$o_elog->setIgnoreLogOff(false); // turns on logging globally ignoring LOG_OFF when set to true

$o_pdo = $o_dbf->connect();

if ($o_pdo !== false) {
    $o_db = new DbModel($o_pdo, $db_config_file);
    if (!is_object($o_db)) {
        $o_elog->write("Could not create a new DbModel\n", LOG_ALWAYS);
        die("Could not get the database to work");
    }
    else {
        $o_di->set('db', $o_db);
        if (!ConstantsHelper::start($o_di)) {
            $o_elog->write("Couldn't create the constants\n", LOG_ALWAYS);
            require_once APP_CONFIG_PATH . '/fallback_constants.php';
        }
        $a_constants = get_defined_constants(true);
        $o_elog->write(var_export($a_constants['user'], true), LOG_OFF);
        $o_router = new Router($o_di);
        $o_tpl    = TwigFactory::getTwig('twig_config.php');
        $o_di->set('router',  $o_router);
        $o_di->set('tpl',     $o_tpl);
        if ($rodb) {
            $o_dbf_ro = DbFactory::start($db_config_file, 'ro');
            $o_pdo_ro = $o_dbf_ro->connect();
            if ($o_pdo_ro !== false) {
                $o_db_ro = new DbModel($o_pdo_ro, $db_config_file);
                if (!is_object($o_db_ro)) {
                    $o_elog->write("Could not create a new DbModel for read only\n", LOG_ALWAYS);
                    die("Could not get the database to work");
                }
                $o_di->set('db', $o_db_ro);
                $o_di->set('db_rw', $o_db);
            }
        }
    }
}
else {
    $o_elog->write("Couldn't connect to database\n", LOG_ALWAYS);
    die("Could not connect to the database");
}
