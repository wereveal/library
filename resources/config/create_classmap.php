<?php
/**
 * Generates the autoload_classmap.php file that sits in the /app/config directory.
 * This file should sit in the /app/config directory too and run from there.
 */
namespace Ritc\Library\Helper;

ini_set('date.timezone', 'America/Chicago');
$config_dir = getcwd();
$app_dir    = str_replace('/config', '', $config_dir);
$src_dir    = $app_dir . '/src';
require $src_dir . '/Ritc/Library/Helper/ClassMapper.php';

$a_dirs = ['app_dir' => $app_dir, 'config_dir' => $config_dir, 'src_dir' => $src_dir];
$o_cm = new ClassMapper($a_dirs);
$o_cm->generateClassMap();
?>
