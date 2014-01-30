<?php
/**
 *  @brief This file sets up a website.
 *  @details Required to get the entire website to work. Needs the Ritc Library.
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
require_once SITE_PATH . '/../app/config/constants.php';

$loader = require_once VENDOR_PATH . '/autoload.php';
$my_classmap = require_once APP_PATH . '/config/autoload_classmap.php';
$loader->addClassMap($my_classmap);

$o_elog = Elog::start();
$o_default_dbf = DbFactory::start('db_config.php', 'rw');
$o_default_pdo = $o_default_dbf->connect();

if ($o_default_pdo !== false) {
    $o_default_db = new DbModel($o_default_pdo);
    if (!Config::start($o_default_db)) {
        $o_elog->write("Couldn't create the constants\n", LOG_ALWAYS);
        require_once APP_PATH . '/config/fallback_constants.php';
    }
}
else {
    $o_elog->write("Couldn't connect to database\n", LOG_ALWAYS);
    require_once APP_PATH . '/config/fallback_constants.php';
}
