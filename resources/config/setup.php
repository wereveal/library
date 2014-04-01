<?php
/**
 *  This file sets up the App.
 *  Required to get the entire framework to work.
 *  @file setup.php
 *  @namespace Ritc
 *  @defgroup ritc_library
 *  @{
 *      @version 5.0.0
 *      @defgroup configs Configuration files
 *      @ingroup ritc_library
 *      @defgroup core Core files of the library
 *      @ingroup ritc_library
 *      @defgroup abstracts abstract definition of classes
 *      @ingroup ritc_library
 *      @defgroup interfaces interface definition of classes
 *      @ingroup ritc_library
 *      @defgroup helper classes that do helper things
 *      @ingroup ritc_library
 *  }
 *  @note <pre>
 *  NOTE: _path and _PATH indicates a full server path
 *        _dir and _DIR indicates the path in the site (URI)
 *        Both do not end with a slash
 *  </pre>
 */
namespace Ritc;

use Ritc\Library\Core\Config;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\DbFactory;
use Ritc\Library\Core\Elog;

if (!defined('SITE_PATH')) {
    define('SITE_PATH', $_SERVER['DOCUMENT_ROOT']);
}
if (!defined('BASE_PATH')) {
    if (!isset($app_in)) {
        $app_in = 'site';
    }
    if ($app_in == 'site' || $app_in == 'htdocs' || $app_in == 'html') {
        define('BASE_PATH', dirname(SITE_PATH));
    }
    else {
        define('BASE_PATH', dirname(dirname(__FILE__)));
    }
}
require_once BASE_PATH . '/app/config/constants.php';

$loader = require_once VENDOR_PATH . '/autoload.php';
$my_classmap = require_once APP_PATH . '/config/autoload_classmap.php';
$loader->addClassMap($my_classmap);

$o_elog = Elog::start();
$o_default_dbf = DbFactory::start('db_config.php', 'rw');
$o_default_pdo = $o_default_dbf->connect();

if ($o_default_pdo !== false) {
    $o_default_db = new DbModel($o_default_pdo, 'db_config.php');
    if (!Config::start($o_default_db)) {
        $o_elog->write("Couldn't create the constants\n", LOG_ALWAYS);
        require_once APP_PATH . '/config/fallback_constants.php';
    }
}
else {
    $o_elog->write("Couldn't connect to database\n", LOG_ALWAYS);
    require_once APP_PATH . '/config/fallback_constants.php';
}
