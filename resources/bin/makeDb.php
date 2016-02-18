<?php
/**
 *  @brief     This file sets up standard stuff for the Framework.
 *  @details   This creates the database config and some standard directories.
 *  @file      install.php
 *  @namespace Ritc
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @date      2015-11-27 15:23:44
 *  @version   1.0.0
*/
namespace Ritc;

use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Factories\TwigFactory;
use Ritc\Library\Helper\ClassMapper;
use Ritc\Library\Helper\ConstantsHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;

define('DEVELOPER_MODE', true);
define('BASE_PATH', dirname(dirname(__DIR__)));
define('SITE_PATH', BASE_PATH . '/public');

require_once BASE_PATH . '/app/config/constants.php';

if (!file_exists(SRC_PATH . '/Ritc/Library')) {
    die("You must clone the Ritc/Library in the src dir first and any other desired apps.\n");
}

if (file_exists(APP_CONFIG_PATH . "/db_config_setup.php")) {
    $db_config_file = "db_config_setup.php";
    $a_db_config = include APP_CONFIG_PATH . "/db_config_setup.php";
} 
elseif (file_exists(APP_CONFIG_PATH . "/db_config.php")) {
    $db_config_file = "db_config.php";
    $a_db_config = include APP_CONFIG_PATH . "/db_config.php";
    if ($a_db_config['name'] == 'REPLACE_ME') {
        die("Please configure the database config file first");
    }
}
else {
    die("A database config file must exists and have valid entries");
}
$db_prefix = $a_db_config['prefix'];
$db_type   = $a_db_config['driver'];

$o_loader = require_once VENDOR_PATH . '/autoload.php';
$my_classmap = require_once APP_CONFIG_PATH . '/autoload_classmap.php';
$o_loader->addClassMap($my_classmap);

$o_elog = Elog::start();
$o_elog->setIgnoreLogOff(true); // turns on logging globally ignoring LOG_OFF when set to true

$o_di = new Di();
$o_di->set('elog',    $o_elog);

$o_pdo = PdoFactory::start($db_config_file, 'rw', $o_di);

if ($o_pdo !== false) {
    $o_db = new DbModel($o_pdo, $db_config_file);
    if (!is_object($o_db)) {
        $o_elog->write("Could not create a new DbModel\n", LOG_ALWAYS);
        die("Could not get the database to work");
    }
    else {
        $o_di->set('db', $o_db);
    }
}
else {
    $o_elog->write("Couldn't connect to database\n", LOG_ALWAYS);
    die("Could not connect to the database");
}

switch ($db_type) {
    case 'pgsql':
        $a_sql = include LIBRARY_PATH . '/resources/sql/default_setup_postgresql.php';
        break;
    case 'sqlite':
        $a_sql = array();
        break;
    case 'mysql':
    default:
        $a_sql = include LIBRARY_PATH . '/resources/sql/default_setup_mysql.php';
}

$o_db->startTransaction();
foreach ($a_sql as $sql) {
    $sql = str_replace('{dbPrefix}', $db_prefix, $sql);
    if ($o_db->rawQuery($sql) === false) {
        $error_message = $o_db->getSqlErrorMessage();
        $o_db->rollbackTransaction();
        die("Database failure\n" . var_export($o_pdo->errorInfo(), true) . "\n");
    }
}
if($o_db->commitTransaction()) {
    print "Success!";
} 
else {
    print "Failure!";
}

?>
