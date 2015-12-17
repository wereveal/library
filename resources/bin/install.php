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

$short_opts = "a:n:h:t:d:u:p:f:";
$long_opts  = [
    "appname:",
    "namespace:",
    "dbhost:",
    "dbtype:",
    "dbname:",
    "dbuser:",
    "dbpass:",
    "dbprefix:"
];

$a_options = getopt($short_opts, $long_opts);

if (count($a_options) < 5) {
   die("The options are \nnamespace (n), appname (a), dbhost (h), \ndbtype (t), dbname (d), dbuser (u), \ndbpass (p), dbprefix (f)\n"); 
}

$app_name  = '';
$namespace = '';
$db_host   = 'localhost';
$db_type   = 'mysql';
$db_name   = '';
$db_user   = '';
$db_pass   = '';
$db_prefix = '';

foreach ($a_options as $option => $value) {
    switch ($option) {
        case "a":
        case "appname":
            $app_name = $value;
            break;
        case "n":
        case "namespace":
            $namespace = $value;
            break;
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

if ($app_name == '') {
    $missing_params .= $missing_params == '' ? "App Name" : ", App Name";
}
if ($namespace == '') {
    $missing_params .= $missing_params == '' ? "Namespace" : ", Namespace";
}
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

define('DEVELOPER_MODE', true);
define('SITE_PATH', __DIR__);
define('BASE_PATH', dirname(SITE_PATH));

require_once BASE_PATH . '/app/config/constants.php';

if (!file_exists(SRC_PATH . '/Ritc/Library')) {
    die("You must clone the Ritc/Library in the src dir first and any other desired apps.\n");
}

### generate classmap so autoloader will work ###
require_once SRC_PATH . '/Ritc/Library/Helper/ClassMapper.php'; 
$a_dirs = ['app_path' => APP_PATH, 'config_path' => APP_CONFIG_PATH, 'src_path' => SRC_PATH];
$o_cm = new ClassMapper($a_dirs);
$o_cm->generateClassMap();

$db_config_file = "db_config_setup.php";
$db_config_file_text =<<<EOT
<?php
return array(
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
EOT;

file_put_contents(APP_CONFIG_PATH . '/' . $db_config_file, $db_config_file_text);

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
$o_db->commitTransaction();

$app_path = SRC_PATH . '/' . $namespace. '/' . $app_name;
$a_new_dirs = ['Abstracts', 'Controllers', 'Entities', 'Interfaces', 'Models',
'Tests', 'Traits', 'Views', 'resources', 'resources/config', 'resources/sql',
'resources/templates', 'resources/themes', 'resources/templates/default',
'resources/templates/elements', 'resources/templates/pages', 
'resources/templates/snippets', 'resources/templates/tests'];

$index_file_text = '<?php
header("Location: http://$_SERVER["SERVER_NAME"]/");
?>';

$tpl_text = "<h3>An Error Has Occurred</h3>";

if (!file_exists($app_path)) {
    mkdir($app_path, 0755, true);
    foreach($a_new_dirs as $dir) {
        $new_dir = $app_path . '/' . $dir;
        $new_file = $new_dir . '/' . 'index.php';
        $new_tpl = $new_dir . '/' . 'no_file.twig';
        mkdir($app_path . '/' . $dir, 0755, true);
        if (strpos($dir, 'resources') === false) {
            file_put_contents($new_file, $index_file_text);
        }
        else {
            file_put_contents($new_tpl, $tpl_text);
        }
    }
}
?>
