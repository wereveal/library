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
    $missing_params .= $missing_params == '' ? "App Name (-a --appname)" : ", App Name (-a --appname)";
}
if ($namespace == '') {
    $missing_params .= $missing_params == '' ? "Namespace (-n --namespace)" : ", Namespace (-n --namespace)";
}

if ($missing_params != '') {
    die("Missing argument(s): {$missing_params}\n");
}
define('DEVELOPER_MODE', true);
define('BASE_PATH', dirname(dirname(__DIR__)));
define('PUBLIC_PATH', BASE_PATH . '/public');

require_once BASE_PATH . '/src/config/constants.php';
$app_path = APPS_PATH . '/' . $namespace. '/' . $app_name;
$a_new_dirs = ['Abstracts', 'Controllers', 'Entities', 'Interfaces', 'Models',
'Tests', 'Traits', 'Views', 'resources/config', 'resources/sql',
'resources/templates', 'resources/themes', 'resources/templates/default',
'resources/templates/elements', 'resources/templates/pages',
'resources/templates/snippets', 'resources/templates/tests'];

$htaccess_text =<<<EOF
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order deny,allow
    Deny from all
</IfModule>
EOF;

$keep_me_text =<<<EOF
Place Holder
EOF;

$no_tpl_text =<<<EOF
<h3>An Error Has Occurred</h3>
EOF;

if (!file_exists($app_path)) {
    mkdir($app_path, 0755, true);
    file_put_contents($app_path . '/.htaccess', $htaccess_text);
    foreach($a_new_dirs as $dir) {
        $new_dir = $app_path . '/' . $dir;
        $new_file = $new_dir . '/.keepme';
        $new_tpl_file = $new_dir . '/no_file.twig';
        mkdir($new_dir, 0755, true);
        if (strpos($dir, 'templates') !== false) {
            file_put_contents($new_tpl_file, $no_tpl_text);
        }
        else {
            file_put_contents($new_file, $keep_me_text);
        }
    }
}
?>
