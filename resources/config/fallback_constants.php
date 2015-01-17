<?php
/**
 *  Defines some required constants.
 *  Used only if the class Constants could not create them from the database.
 *  @file just_in_case.php
 *  @ingroup sm configs
 *  _PATH = Full server path
 *  _DIR = Path in web site (URI)
 *  _NAME = Name of item without any path information
**/
namespace Ritc;

define('DISPLAY_DATE_FORMAT', 'm/d/Y');
define('DISPLAY_PHONE_FORMAT', 'XXX-XXX-XXXX');
define('EMAIL_DOMAIN', 'replaceme.com');
define('EMAIL_FORM_TO', 'me@replaceme.com');
define('ERROR_EMAIL_ADDRESS', 'webmaster@revealitconsulting.com');
define('PAGE_TEMPLATE', 'base.twig');
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
define('DEVELOPER_MODE', false);
define('ENCRYPT_TYPE', 'cleartext');
define('FTP_BASE_PATH', '/srv/websites');
define('SYSTEM_USER_ID', 1001);
define('SYSTEM_GROUP_ID', 81);
