<?php
/**
 *  @brief This file sets up the Framework.
 *  @description Required to get the entire framework to work. The only thing
 *  that changes primarily is the defgroup in this comment for Doxygen.
 *  @file setup.php
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
 *      @defgroup traits functions that are common to multiple classes
 *      @ingroup ritc_library
 *      @defgroup tests classes that test other classes
 *      @ingroup ritc_library
 *      @defgroup views classes that provide the end user experience
 *      @ingroup ritc_library
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

$db_config_file = "db_setup_config.php";
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
'Tests', 'Traits', 'Views', 'resources/config', 'resources/sql',
'resources/templates', 'resources/themes'];
$index_file_text = '<?php
header("Location: http://$_SERVER["SERVER_NAME"]/");
?>';

if (!file_exists($app_path)) {
    mkdir($app_path, 0755, true);
    foreach($a_new_dirs as $dir) {
        $new_dir = $app_path . '/' . $dir;
        $new_file = $new_dir . '/' . 'index.php';
        mkdir($app_path . '/' . $dir, 0755, true);
        file_put_contents($new_file, $index_file_text);
    }
}
?>
