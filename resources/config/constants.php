<?php
/**
 *  @brief Define Constants that will be used throughout the website.
 *  @file constants.php
 *  @note <pre>
 *      _PATH = Full server path
 *      _DIR  = Path in web site (URI)
 *      _NAME = Name of item without any path information
 *  @ingroup ritc_framework configs
 **/
namespace Ritc;

if (!isset($allow_get) || $allow_get === false) {
    $_GET = array();
}
// Empty some global vars we don't use and don't want to have values in
$_REQUEST = array();

if (!defined('SITE_PATH')) {
    exit('This file cannot be called directly'); // should be defined in the setup.php file
}
if (!defined('BASE_PATH')) {
    exit('This file cannot be called directly'); // should be defined in the setup.php file
}
if (!defined('PUBLIC_DIR')) {
    define('PUBLIC_DIR', '');
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
if (!defined('SRC_PATH')) {
    define('SRC_PATH', APP_PATH . '/src');
}
if (!defined('APP_CONFIG_PATH')) {
    define('APP_CONFIG_PATH', APP_PATH . '/config');
}
if (!defined('VENDOR_PATH')) {
    define('VENDOR_PATH', BASE_PATH . '/vendor');
}
if (!defined('ADMIN_DIR_NAME')) {
    define('ADMIN_DIR_NAME',     'admin');
}
if (!defined('ASSETS_DIR_NAME')) {
    define('ASSETS_DIR_NAME',    'assets');
}
if (!defined('PRIVATE_DIR_NAME')) {
    define('PRIVATE_DIR_NAME', 'private');
}
if (!defined('TMP_DIR_NAME')) {
    define('TMP_DIR_NAME', 'tmp');
}
if (!defined('SITE_URL')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
    }
    else {
        define('SITE_URL', 'localhost');
    }
}

$private_w_path = BASE_PATH . '/' . PRIVATE_DIR_NAME;
$tmp_w_path = BASE_PATH . '/' . TMP_DIR_NAME;
if (file_exists($tmp_w_path)) {
    define('TMP_PATH', $tmp_w_path);
}
else {
    define('TMP_PATH', '/tmp');
}
if (file_exists($private_w_path)) {
    define('PRIVATE_PATH', $private_w_path);
}
else {
    define('PRIVATE_PATH', '');
}

define('ADMIN_DIR',   PUBIC_DIR . '/' . ADMIN_DIR_NAME);
define('ASSETS_DIR',  PUBIC_DIR . '/' . ASSETS_DIR_NAME);
define('ASSETS_PATH', SITE_PATH . ASSETS_DIR);
define('ADMIN_PATH',  SITE_PATH . ADMIN_DIR);

/**
 * Variables used by the classes Elog and Show_Global_Vars.
 * For Production Sites, only USE_PHP_LOG could be true
 * but it can slow things a bit. The class Elog has a
 * method that allows temporary overrides of these global
 * settings in the class (not the constants themselves of course).
 **/
define('USE_PHP_LOG',  true);
define('USE_TEXT_LOG', false);
define('LOG_OFF', 0);
define('LOG_ON', 1);
define('LOG_PHP', 1);
define('LOG_BOTH', 2);
define('LOG_EMAIL', 3);
define('LOG_ALWAYS', 4);
define('USE_DEBUG_SGV',  false);
