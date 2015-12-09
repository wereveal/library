<?php
namespace Ritc;

use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Factories\TwigFactory;
use Ritc\Library\Helper\ConstantsHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;

$short_opts = "a:n:";
$long_opts  = [
    "appname:",
    "namespace:"
];

$a_options = getopt($short_opts, $long_opts);

$app_name  = '';
$namespace = '';

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
    }
}

$missing_params = '';

if ($app_name == '') {
    $missing_params .= $missing_params == '' ? "App Name" : ", App Name";
}
if ($namespace == '') {
    $missing_params .= $missing_params == '' ? "Namespace" : ", Namespace";
}

if ($missing_params != '') {
    die("Missing argument(s): {$missing_params}\n");
}
define('DEVELOPER_MODE', true);
define('SITE_PATH', __DIR__);
define('BASE_PATH', dirname(SITE_PATH));

require_once BASE_PATH . '/app/config/constants.php';
$app_path = SRC_PATH . '/' . $namespace. '/' . $app_name;
$a_new_dirs = ['Abstracts', 'Controllers', 'Entities', 'Interfaces', 'Models',
'Tests', 'Traits', 'Views', 'resources/config', 'resources/sql',
'resources/templates', 'resources/themes', 'resources/templates/default',
'resources/templates/elements', 'resources/templates/pages', 
'resources/templates/snippets', 'resources/templates/tests'];

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
