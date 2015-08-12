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
if (!defined('VENDOR_PATH')) {
    define('VENDOR_PATH', BASE_PATH . '/vendor');
}
if (!defined('SRC_PATH')) {
    define('SRC_PATH', APP_PATH . '/src');
}
if (!defined('APP_CONFIG_PATH')) {
    define('APP_CONFIG_PATH', APP_PATH . '/config');
}
if (!defined('SITE_URL')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
    }
    else {
        define('SITE_URL', 'localhost');
    }
}

/**
 * Variables used by the classes Elog.
 * Needs to be set early.
 * USE_PHP_LOG should be false normally in Production sites
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
