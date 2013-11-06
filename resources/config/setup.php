<?php
/**
 *  This file sets up the RITC Framework.
 *  Required to get the entire framework to work.
 *  @file setup.php
 *  @namespace Ritc
 *  @defgroup ritc_library
 *  @{
 *      Previously named sitemanager. Was cut down to be just a basic library
 *      @version 4.0
 *      @defgroup configs Configuration files
 *      @ingroup ritc_library
 *      @defgroup core The core library files
 *      @ingroup ritc_library
 *  }
 *  @note <pre>
 *  NOTE: _path and _PATH indicates a full server path
 *        _dir and _DIR indicates the path in the site (URI)
 *        Both do not end with a slash
 *  </pre>
*/
namespace Ritc\Library\Core;

use Ritc\Library\Core\Config;
use Ritc\Library\Core\DbFactory;

if (!defined('SITE_PATH')) {
    define('SITE_PATH', $_SERVER['DOCUMENT_ROOT']);
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(SITE_PATH));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
require_once APP_PATH . '/autoload.php';
require_once APP_PATH . '/config/constants.php';

$o_default_dbf = DbFactory::start('db_config.php', 'rw');
$o_default_pdo = $o_default_dbf->connect();
if ($o_default_pdo !== false) {
    $o_default_db = new Database($o_default_pdo);
    if (!Config::start($o_default_db)) {
        error_log("Couldn't create the constants\n\n");
    }
}
else {
    require_once APP_PATH . '/config/fallback_constants.php';
}
