<?php
/**
 *  This file sets up the WER Framework.
 *  Required to get the entire framework to work.
 *  @file setup.php
 *  @namespace Wer
 *  @defgroup wer_framework
 *  @{
 *      Previously named sitemanager. Was cut down to be just a basic framework
 *      @version 4.0
 *      @defgroup configs Configuration files
 *      @ingroup wer_framework
 *      @defgroup core The core framework files
 *      @ingroup wer_framework
 *  }
 *  @defgroup guide
 *  @{
 *      @version 1.0
 *      @defgroup controllers controller files
 *      @ingroup guide
 *      @defgroup views classes that create views
 *      @ingroup guide
 *      @defgroup forms files that define and create forms
 *      @ingroup views
 *      @defgroup model files that do database operations
 *      @ingroup guide
 *      @defgroup tests unitTesting
 *      @ingroup guide
 *  }
 *  @note <pre>
 *  NOTE: _path and _PATH indicates a full server path
 *        _dir and _DIR indicates the path in the site (URI)
 *        Both do not end with a slash
 *  </pre>
*/
namespace Wer\Framework\Library;

use Wer\Framework\Library\Config;

if (!defined('SITE_PATH')) {
    define('SITE_PATH', $_SERVER['DOCUMENT_ROOT']);
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(SITE_PATH));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
if (!defined('VENDOR_PATH')) {
    define('VENDOR_PATH', BASE_PATH . '/vendor');
}
require_once APP_PATH . '/autoload.php';
require_once APP_PATH . '/config/constants.php';
if (!Config::start()) {
    error_log("Couldn't create the constants\n\n");
    require_once APP_PATH . '/config/fallback_constants.php';
}
