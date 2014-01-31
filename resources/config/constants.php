<?php
/**
 *  @brief Define Constants that will be used throughout the website.
 *  @file constants.php
 *  @note <pre>
 *      _PATH = Full server path
 *      _DIR  = Path in web site (URI)
 *      _NAME = Name of item without any path information
 *  @ingroup ritc_library configs
**/
namespace Ritc;

if (!defined('SITE_PATH')) exit('This file cannot be called directly');

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(SITE_PATH));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
if (!defined('VENDOR_PATH')) {
    define('VENDOR_PATH', BASE_PATH . '/vendor');
}
if (!defined('SRC_PATH')) {
    define('SRC_PATH', APP_PATH . '/src');
}

if (!isset($allow_get) || $allow_get === false) {
    $_GET = array();
}
// Empty some global vars we don't use and don't want to have values in
$_REQUEST = array();

define('ADMIN_DIR_NAME',     'admin');
define('ASSETS_DIR_NAME',    'assets');
define('CONFIG_DIR_NAME',    'config');
define('CSS_DIR_NAME',       'css');
define('FILES_DIR_NAME',     'files');
define('HTML_DIR_NAME',      'html');
define('IMAGE_DIR_NAME',     'images');
define('JS_DIR_NAME',        'js');
define('LIBS_DIR_NAME',      'Library');
define('PRIVATE_DIR_NAME',   'private');
define('TEMPLATES_DIR_NAME', 'templates');
define('TMP_DIR_NAME',       'tmp');
if (isset($_SERVER['HTTP_HOST'])) {
    define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
}
else {
    define('SITE_URL', 'localhost');
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

define('ADMIN_DIR',       '/' . ADMIN_DIR_NAME);
define('APP_CONFIG_PATH', APP_PATH  . '/' . CONFIG_DIR_NAME);
define('ASSETS_DIR',      '/' . ASSETS_DIR_NAME);
define('FILES_DIR',       ASSETS_DIR . '/' . FILES_DIR_NAME);
define('IMAGES_DIR',      ASSETS_DIR . '/' . IMAGE_DIR_NAME);
define('FILES_PATH',      SITE_PATH . FILES_DIR);
define('IMAGES_PATH',     SITE_PATH . IMAGES_DIR);
define('ADMIN_PATH',      SITE_PATH . ADMIN_DIR);

/**
 *  Variables used by the classes Elog and Show_Global_Vars.
 *  For Production Sites, only USE_PHP_LOG could be true
 *  but it can slow things a bit. The class Elog has a
 *  method that allows temporary overrides of these global
 *  settings in the class (not the constants themselves of course).
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
