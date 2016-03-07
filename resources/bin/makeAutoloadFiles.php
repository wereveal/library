<?php
/**
 * Generates the autoload_classmap.php file that sits in the /app/config directory.
 * This file should sit in the /app/bin directory run from there, e.g., php makeAutoloadFiles.php.
 */
namespace Ritc\Library\Helper;

ini_set('date.timezone', 'America/Chicago');
$bin_path    = getcwd();
$app_path    = str_replace('/bin', '', $bin_path);
$src_path    = $app_path . '/src';
$config_path = $app_path . '/config';
require $src_path . '/Ritc/Library/Helper/AutoloadMapper.php';

$a_dirs = [
    'app_path'    => $app_path, 
    'config_path' => $config_path, 
    'src_path'    => $src_path];
$o_cm = new AutoloadMapper($a_dirs);
if (!is_object($o_cm)) {
    die("Could not instance AutoloadMapper");
}
// print $o_cm->getAppPath() . "\n";
// print $o_cm->getConfigPath() . "\n";
// print $o_cm->getSrcPath() . "\n";
$o_cm->generateMapFiles();
?>
