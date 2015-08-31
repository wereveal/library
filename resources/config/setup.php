<?php
/**
 *  @brief This file sets up the App.
 *  @description Required to get the entire framework to work. The only thing
 *  that changes primarily is the defgroup in this comment for Doxygen.
 *  @file setup.php
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
 *  @defgroup ftpadmin
 *  @{
 *      @version 1.0
 *      @defgroup ftp_configs
 *      @ingroup ftpadmin
 *      @defgroup ftp_controllers controller files
 *      @ingroup ftpadmin
 *      @defgroup ftp_views classes that create views
 *      @ingroup ftpadmin
 *      @defgroup ftp_models files that do database operations
 *      @ingroup ftpadmin
 *      @defgroup ftp_tests unit Testing
 *      @ingroup ftpadmin
 *  }
 *  @note <pre>
 *  NOTE: _path and _PATH indicates a full server path
 *        _dir and _DIR indicates the path in the site (URI)
 *        Both do not end with a slash
 *  </pre>
*/
namespace Ritc;

use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Factories\TwigFactory;
use Ritc\Library\Helper\ConstantsHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;

if (!defined('SITE_PATH')) {
    define('SITE_PATH', $_SERVER['DOCUMENT_ROOT']);
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__FILE__)));
}

require_once BASE_PATH . '/app/config/constants.php';

if (!isset($db_config_file)) {
    $db_config_file = 'db_config.php';
}

$o_loader = require_once VENDOR_PATH . '/autoload.php';
$my_classmap = require_once APP_CONFIG_PATH . '/autoload_classmap.php';
$o_loader->addClassMap($my_classmap);

$o_elog    = Elog::start();
$o_elog->setIgnoreLogOff(false); // turns on logging globally ignoring LOG_OFF when set to true

$o_session = Session::start();

$o_di      = new Di();
$o_di->set('elog',    $o_elog);
$o_di->set('session', $o_session);

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
        $a_constants = get_defined_constants(true);
        $o_elog->write(var_export($a_constants['user'], true), LOG_OFF);
        $o_router = new Router($o_di);
        $o_twig   = TwigFactory::getTwig('twig_config.php');
        $o_di->set('router',  $o_router);
        $o_di->set('twig',    $o_twig);
    }
}
else {
    $o_elog->write("Couldn't connect to database\n", LOG_ALWAYS);
    die("Could not connect to the database");
}
