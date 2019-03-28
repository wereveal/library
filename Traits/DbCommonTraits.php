<?php
/**
 * Trait DbCommonTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\Arrays;

/**
 * Common methods for the db traits.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-beta.2
 * @date    2017-01-25 14:12:15
 * @change_log
 * - v1.0.0-beta.2  - Cleared up that they work with array of assoc arrays      - 2017-06-16 wer
 * - v1.0.0-beta.1  - Moved method from DbTraits to here and moved into beta    - 2017-01-25 wer
 * - v1.0.0-alpha.0 - Initial version                                           - 2016-03-19 wer
 */
trait DbCommonTraits
{
    /**
     * Changes array keys to be compatible with prepared statements.
     * @param array $array required associative array or list of assoc arrays.
     * @return array fixed key names
     */
    public function prepareKeys(array $array = array()):array
    {
        $a_new = array();
        if (Arrays::isAssocArray($array)) {
            foreach ($array as $key=>$value) {
                $new_key = strpos($key, ':') === 0 ? $key : ':' . $key;
                $a_new[$new_key] = $value;
            }
            return $a_new;
        }

        if (Arrays::isArrayOfAssocArrays($array)) {
            foreach ($array as $a_keys) {
                $results = $this->prepareKeys($a_keys);
                if ($results === false) {
                    return $array;
                }
                $a_new[] = $results;
            }
            return $a_new;
        }

        return $array;
    }

    /**
     * Changes array values to help build a prepared statement primarily the WHERE.
     * @param array $array key/value pairs to fix, assoc array or array of assoc arrays.
     * @return array fixed where needed
     */
    public function prepareValues(array $array):array
    {
        $a_new = array();
        if (Arrays::isAssocArray($array)) {
            foreach ($array as $key => $value) {
                $new_key = strpos($key, ':') === 0 ? $key : ':' . $key;
                $a_new[$key] = $new_key;
            }
            return $a_new;
        }

        if (Arrays::isArrayOfAssocArrays($array)) {
            return $this->prepareValues($array[0]);
        }
        return $array;
    }

    /**
     * Looks for the config file and includes it into an array.
     * There are several different places the config file could be: SRC_PATH, PRIVATE_PATH, PUBLIC_PATH and
     * $_SERVER['DOCUMENT_ROOT'] . /config. The config file name can also include a path to the file
     * not in the standard locations. It it exists it will be used otherwise the path is stripped off
     * and the file name is looked for in the standard places.
     * @param string $config_file  A file which returns an array with the following key=>value pairs:
     * \code
     * 'driver'     => 'mysql' || 'pgsql',
     * 'host'       => 'localhost',
     * 'port'       => '3306',
     * 'name'       => 'db_name',
     * 'user'       => 'example_user',
     * 'password'   => 'letmein',
     * 'userro'     => 'example_read_only_user', (required only if db is set to ro)
     * 'passro'     => 'letmein', (required only if db is set to ro)
     * 'persist'    => false, (debate over if persist should be true)
     * 'errmode'    => 'exception', (one of three types of PDO error modes)
     * 'prefix'     => 'app_' (prefix to the database tables)
     * 'db_prefix'  => same as 'prefix'
     * 'lib_prefix' => 'ritc_' (prefix to the database tables that are specific to the Ritc\Library
     * \endcode
     * @return array
     */
    protected function retrieveDbConfig($config_file = 'db_config.php'):array
    {
        if (strpos($config_file, '/') !== false) {
            if (file_exists($config_file)) {
                /** @noinspection PhpIncludeInspection */
                $a_db = include $config_file;
                if (is_array($a_db)) {
                    return $a_db;
                }
                return [];
            }
            $config_file = substr($config_file, strrpos($config_file, '/') + 1);
        }
        $config_w_apppath  = '';
        $config_w_privpath = '';
        $config_w_pubpath = '';
        $config_w_path     = $_SERVER['DOCUMENT_ROOT'] . '/config/' . $config_file;
        if (defined('SRC_PATH')) {
            $config_w_apppath = SRC_PATH . '/config/' . $config_file;
        }
        if (defined('PRIVATE_PATH')) {
            $config_w_privpath = PRIVATE_PATH . '/' . $config_file;
        }
        if (defined('PUBLIC_PATH')) {
            $config_w_pubpath = PUBLIC_PATH . '/config/' . $config_file;
        }
        if ($config_w_privpath !== '' && file_exists($config_w_privpath)) {
            $config_w_path = $config_w_privpath;
        }
        elseif ($config_w_apppath !== '' && file_exists($config_w_apppath)) {
            $config_w_path = $config_w_apppath;
        }
        elseif ($config_w_pubpath !== '' && file_exists($config_w_pubpath)) {
            $config_w_path = $config_w_pubpath;
        }
        if (!file_exists($config_w_path)) {
            return [];
        }
        /** @noinspection PhpIncludeInspection */
        $a_db = include $config_w_path;
        if (is_array($a_db)) {
            return $a_db;
        }
        return [];
    }
}
