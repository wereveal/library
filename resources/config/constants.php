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
if (!defined('DEVELOPER_MODE')) {
    define('DEVELOPER_MODE', false);
}
if (!defined('ENTITIES_CODING')) {
    define('ENTITIES_CODING', ENT_QUOTES);
}
if (!defined('RODB')) {
    define('RODB', false);
}
if (!defined('LIBRARY_PATH')) {
    if(file_exists(SRC_PATH . '/Ritc/Library')) {
        define('LIBRARY_PATH', SRC_PATH . '/Ritc/Library');
        if (file_exists(LIBRARY_PATH . '/resources/config')) {
            define('LIBRARY_CONFIG_PATH', LIBRARY_PATH . '/resources/config');
        }
        else {
            define('LIBRARY_CONFIG_PATH', APP_CONFIG_PATH);
        }
    }
    else {
        define('LIBRARY_PATH', '');
    }
}
