<?php
/**
 * @brief     This file sets up the App.
 * @details   Required to get the entire framework to work. The only thing
 *            that changes primarily is the defgroup in this comment for Doxygen.
 * @file      /src/apps/Ritc/Library/resources/config/setup.php
 * @namespace Ritc
 * @note NOTE:
 * - _path and _PATH indicates a full server path
 * - _dir and _DIR indicates the path in the site (URI)
 * - Both paths do not end with a slash.
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

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', $_SERVER['DOCUMENT_ROOT']);
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__FILE__)));
}

require_once BASE_PATH . '/src/config/constants.php';

if (!isset($db_config_file)) {
    $db_config_file = 'db_config.php';
}

$o_loader = require_once VENDOR_PATH . '/autoload.php';

### PSR-4 autoload method
$my_namespaces = require_once SRC_CONFIG_PATH . '/autoload_namespaces.php';
foreach ($my_namespaces as $psr4_prefix => $psr0_paths) {
    $o_loader->addPsr4($psr4_prefix, $psr0_paths);
}
###

### classmap method of autoload ###
# $my_classmap = require_once SRC_CONFIG_PATH . '/autoload_classmap.php';
# $o_loader->addClassMap($my_classmap);
###

$o_elog = Elog::start();
$o_elog->setIgnoreLogOff(false); // turns on logging globally ignoring LOG_OFF when set to true
// set_error_handler([$o_elog, 'errorHandler'], E_USER_WARNING | E_USER_NOTICE | E_USER_ERROR);
$o_elog->setErrorHandler(E_USER_WARNING | E_USER_NOTICE | E_USER_ERROR);

$o_session = Session::start();

$o_di = new Di();
$o_di->set('elog',    $o_elog);
$o_di->set('session', $o_session);

$o_pdo = PdoFactory::start($db_config_file, 'rw', $o_di);

if ($o_pdo !== false) {
    $o_db = new DbModel($o_pdo, $db_config_file);
    $o_db->setElog($o_elog);

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
            require_once SRC_CONFIG_PATH . '/fallback_constants.php';
        }
        $o_session->setIdleTime(SESSION_IDLE_TIME);
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
