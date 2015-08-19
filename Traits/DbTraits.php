<?php
/**
 *  @brief Common functions that would be used in several database classes.
 *  @file DbTraits.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Traits
 *  @class DbTraits
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0ß1
 *  @date 2015-08-19 09:53:24
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0ß1 - initial version - 08/19/2015 wer
 *  </pre>
 */
namespace Ritc\Library\Traits;

trait DbTraits {
    private function retrieveDbConfig($config_file = 'db_config.php')
    {
        $config_w_apppath  = '';
        $config_w_privpath = '';
        $config_w_sitepath = '';
        $default_path     = $_SERVER['DOCUMENT_ROOT'] . '/config/' . $config_file;
        if (defined('APP_PATH')) {
            $config_w_apppath = APP_PATH . '/config/' . $config_file;
        }
        if (defined('PRIVATE_PATH')) {
            $config_w_privpath = PRIVATE_PATH . '/' . $config_file;
        }
        if (defined('SITE_PATH')) {
            $config_w_sitepath = SITE_PATH . '/config/' . $config_file;
        }
        if ($config_w_privpath != '' && file_exists($config_w_privpath)) {
            $config_w_path = $config_w_privpath;
        }
        elseif ($config_w_apppath != '' && file_exists($config_w_apppath)) {
            $config_w_path = $config_w_apppath;
        }
        elseif ($config_w_sitepath != '' && file_exists($config_w_sitepath)) {
            $config_w_path = $config_w_sitepath;
        }
        else {
            $config_w_path = $default_path;
        }
        if (!file_exists($config_w_path)) {
            return false;
        }
        $a_db = include $config_w_path;
        return $a_db;
    }
}
