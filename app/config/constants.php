<?php
/**
 *  Define Constants that will be used throughout the website.
 *  @file constants.php
 *  @note <pre>
 *      _PATH = Full server path
 *      _DIR  = Path in web site (URI)
 *      _NAME = Name of item without any path information
 *  @ingroup wer_framework configs
**/

// Empty some global vars we don't use and don't want to have values in
namespace Wer;
if (!defined('APP_PATH')) exit('This file cannot be called directly');
if (!isset($allow_get) || $allow_get === false) {
    $_GET = array();
}
$_REQUEST = array();
$HTTP_GET_VARS = array();

define('ADMIN_DIR_NAME',     'admin');
define('ASSETS_DIR_NAME',    'assets');
define('CONFIG_DIR_NAME',    'config');
define('CSS_DIR_NAME',       'css');
define('HTML_DIR_NAME',      'html');
define('IMAGE_DIR_NAME',     'images');
define('JS_DIR_NAME',        'js');
define('LIBS_DIR_NAME',      'Library');
define('TEMPLATES_DIR_NAME', 'templates');
define('STD_CONTENT_TPL',    'content.tpl');

define('PRIVATE_DIR_NAME',   'private');
define('TMP_DIR_NAME',       'tmp');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
$private_w_path = BASE_PATH . '/' . PRIVATE_DIR_NAME;
$tmp_w_path = BASE_PATH . '/' . TMP_DIR_NAME;
if (file_exists($tmp_w_path)) {
    define('TMP_PATH', $tmp_w_path);
} else {
    define('TMP_PATH', '/tmp');
}
if (file_exists($private_w_path)) {
    define('PRIVATE_PATH', $private_w_path);
} else {
    define('PRIVATE_PATH', '');
}

define('ADMIN_DIR',            '/' . ADMIN_DIR_NAME);
define('APP_CONFIG_PATH',      APP_PATH  . '/config');
define('ASSETS_DIR',           '/' . ASSETS_DIR_NAME);
define('FILES_DIR',            ASSETS_DIR . '/files');
define('MEDIA_DIR',            ASSETS_DIR . '/media');
define('THEMES_DIR',           ASSETS_DIR . '/themes');
define('IMAGES_DIR',           ASSETS_DIR . '/' . IMAGE_DIR_NAME);
define('IMAGES_THUMBS_DIR',    IMAGES_DIR . '/thumbs');
define('EVENTS_IMAGES_DIR',    IMAGES_DIR . '/events');
define('STAFF_IMAGES_DIR',     IMAGES_DIR . '/staff');
define('EVENTS_IMAGES_PATH',   SITE_PATH . EVENTS_IMAGES_DIR);
define('FILES_PATH',           SITE_PATH . FILES_DIR);
define('IMAGES_PATH',          SITE_PATH . IMAGES_DIR);
define('STAFF_IMAGES_PATH',    SITE_PATH . STAFF_IMAGES_DIR);
define('THEMES_PATH',          SITE_PATH . THEMES_DIR);
define('ADMIN_PATH',           SITE_PATH . ADMIN_DIR);

/** Variables used by the classes Elog and Show_Global_Vars.
    For Production Sites, only USE_PHP_LOG could be true
    but it can slow things a bit. The class Elog has a
    method that allows temporary overrides of these global
    settings in the class (not the constants themselves of course).
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
