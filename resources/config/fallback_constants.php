<?php
/**
 * @brief     Defines some required constants.
 * @details   Used only if the class Constants could not create them from the database.
 * @file      /app/src/Ritc/Library/resources/config/fallback_constants.php
 * @ingroup   ritc_library configs
 * @note NOTE:
 * - _PATH = Full server path
 * - _DIR  = Path in web site (URI)
 * - _NAME = Name of item without any path information
 */
namespace Ritc;

define('DISPLAY_DATE_FORMAT', 'm/d/Y');
define('DISPLAY_PHONE_FORMAT', 'XXX-XXX-XXXX');
define('EMAIL_DOMAIN', 'replaceme.com');
define('EMAIL_FORM_TO', 'me@replaceme.com');
define('ERROR_EMAIL_ADDRESS', 'webmaster@revealitconsulting.com');
define('PAGE_TEMPLATE', 'index.twig');
define('TWIG_PREFIX', '@app_');
define('LIB_TWIG_PREFIX', '@lib_');
define('THEME_NAME', '');
define('ADMIN_THEME_NAME', '');
define('THEMES_DIR', '');
define('CSS_DIR_NAME', 'css');
define('HTML_DIR_NAME', 'html');
define('JS_DIR_NAME', 'js');
define('IMAGE_DIR_NAME', 'images');
define('ADMIN_DIR_NAME', 'manager');
define('ASSETS_DIR_NAME', 'assets');
define('FILES_DIR_NAME', 'files');
define('PRIVATE_DIR_NAME', 'private');
define('TMP_DIR_NAME', 'tmp');
define('RIGHTS_HOLDER', 'Reveal IT Consulting');
define('SESSION_IDLE_TIME', 1800);
if (!defined('DEVELOPER_MODE')) {
    define('DEVELOPER_MODE', true);
}
