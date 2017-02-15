<?php
/**
 * @brief     Define Constants that will be used throughout the website.
 * @file      /src/apps/Ritc/Library/resources/config/constants.php
 * @note NOTE:
 * - _PATH = Full server path
 * - _DIR  = Path in web site (URI)
 * - _NAME = Name of item without any path information
 * @ingroup lib_configs
 */
namespace Ritc;

if (!defined('PUBLIC_PATH')) {
    exit('This file cannot be called directly'); // should be defined in the setup.php file
}
if (!defined('BASE_PATH')) {
    exit('This file cannot be called directly'); // should be defined in the setup.php file
}
if (!defined('PUBLIC_DIR')) {
    define('PUBLIC_DIR', '');
}
if (!defined('SRC_PATH')) {
    define('SRC_PATH', BASE_PATH . '/src');
}
if (!defined('VENDOR_PATH')) {
    define('VENDOR_PATH', BASE_PATH . '/vendor');
}
if (!defined('APPS_PATH')) {
    define('APPS_PATH', SRC_PATH . '/apps');
}
if (!defined('SRC_CONFIG_PATH')) {
    define('SRC_CONFIG_PATH', SRC_PATH . '/config');
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
if (!defined('RODB')) {
    define('RODB', false);
}
if (!defined('LIBRARY_PATH')) {
    if(file_exists(APPS_PATH . '/Ritc/Library')) {
        define('LIBRARY_PATH', APPS_PATH . '/Ritc/Library');
        if (file_exists(LIBRARY_PATH . '/resources/config')) {
            define('LIBRARY_CONFIG_PATH', LIBRARY_PATH . '/resources/config');
        }
        else {
            define('LIBRARY_CONFIG_PATH', SRC_CONFIG_PATH);
        }
    }
    else {
        define('LIBRARY_PATH', '');
    }
}
