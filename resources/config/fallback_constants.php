<?php
/**
 *  Defines some required constants.
 *  Used only if the class Config could not create them from the database.
 *  @file just_in_case.php
 *  @ingroup sm configs
 *  _PATH = Full server path
 *  _DIR = Path in web site (URI)
 *  _NAME = Name of item without any path information
**/
namespace Wer;

define('DISPLAY_DATE_FORMAT', 'm/d/Y');
define('EMAIL_DOMAIN', 'replaceme.com');
define('EMAIL_FORM_TO', 'me@replaceme.com');
define('ERROR_EMAIL_ADDRESS', 'webmaster@revealitconsulting.com');
define('PAGE_META_DESCRIPTION', 'My Company');
define('PAGE_META_KEYWORDS', 'My Company');
define('PAGE_TEMPLATE', 'index.tpl');
define('PAGE_TITLE', 'My Company');
define('THEME_NAME', 'default');
