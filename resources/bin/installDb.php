<?php
/**
 *  @brief     Creates the database tables.
 *  @details   This is run in the Library.
 *  @file      installDb.php
 *  @namespace Ritc
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @date      2015-12-09 17:42:36 
 *  @version   1.0.0
*/
namespace Ritc;

use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;

require_once $base_path . '/Helper/Arrays.php';
require_once $base_path . '/Services/Elog.php';
require_once $base_path . '/Services/Di.php';
require_once $base_path . '/Traits/DbTraits.php';
require_once $base_path . '/Traits/LogitTraits.php';
require_once $base_path . '/Factories/PdoFactory.php';
require_once $base_path . '/Services/DbModel.php';

$short_opts = "h:t:d:u:p:f:";
$long_opts  = [
    "dbhost:",
    "dbtype:",
    "dbname:",
    "dbuser:",
    "dbpass:",
    "dbprefix:"
];

$a_options = getopt($short_opts, $long_opts);

if (count($a_options) < 5) {
   die("The options are \ndbhost (h),\ndbtype (t),\ndbname (d),\ndbuser (u),\ndbpass (p),\ndbprefix (f)\n"); 
}

$db_host   = 'localhost';
$db_type   = 'mysql';
$db_name   = '';
$db_user   = '';
$db_pass   = '';
$db_prefix = '';

foreach ($a_options as $option => $value) {
    switch ($option) {
        case "h":
        case "dbhost":
            $db_host = $value;
            break;
        case "t":
        case "dbtype":
            $db_type = $value;
            break;
        case "d":
        case "dbname":
            $db_name = $value;
            break;
        case "u":
        case "dbuser":
            $db_user = $value;
            break;
        case "p":
        case "dbpass":
            $db_pass = $value;
            break;
        case "f":
        case "dbprefix":
            $db_prefix = $value;
    }
}

$missing_params = '';

if ($db_name == '') {
    $missing_params .= $missing_params == '' ? "DB Name" : ", DB Name";
}
if ($db_user == '') {
    $missing_params .= $missing_params == '' ? "DB User" : ", DB User";
}
if ($db_pass == '') {
    $missing_params .= $missing_params == '' ? "DB Password" : ", DB Password";
}

if ($missing_params != '') {
    die("Missing argument(s): {$missing_params}\n");
}

$base_path   = dirname(dirname(__DIR__));
$config_path = $base_path . '/resources/config';
$sql_path    = $base_path . '/resources/sql';

### generate classmap so autoloader will work ###


$o_elog = Elog::start();
$o_elog->setIgnoreLogOff(true); // turns on logging globally ignoring LOG_OFF when set to true

$o_di = new Di();
$o_di->set('elog', $o_elog);

$db_config = array(
    'driver'   => '{$db_type}',
    'host'     => '{$db_host}',
    'port'     => '',
    'name'     => '{$db_name}',
    'user'     => '{$db_user}',
    'password' => '{$db_pass}',
    'userro'   => '{$db_user}',
    'passro'   => '{$db_pass}',
    'persist'  => true,
    'prefix'   => '{$db_prefix}'
);

$dsn = $db_type . ':host=' . $db_host . ';dbname=' . $db_name;
$o_pdo = new \PDO($dsn, $db_user, $db_pass, array(\POD::ATTR_PERSISTENT => true));

if ($o_pdo !== false) {
    $o_db = new DbModel($o_pdo, $db_config);
    if (!is_object($o_db)) {
        die("Could not get the database to work");
    }
    else {
        $o_di->set('db', $o_db);
    }
}
else {
    die("Could not connect to the database");
}

switch ($db_type) {
    case 'pgsql':
        $a_sql = include $sql_path . '/default_setup_postgresql.php';
        break;
    case 'sqlite':
        $a_sql = array();
        break;
    case 'mysql':
    default:
        $a_sql = include $sql_path . '/default_setup_mysql.php';
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
$o_db->commitTransaction();

?>
