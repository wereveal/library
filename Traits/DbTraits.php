<?php
/**
 * @brief     Common functions that would be used in several database classes.
 * @ingroup   lib_traits
 * @file      DbTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.0
 * @date      2016-03-18 15:02:27
 * @note <b>Change Log</b>
 *   v2.0.0   - Moved a couple methods from DbModel to here         - 2016-03-18 wer
 * - v1.0.0   - first working version                               - 11/27/2015 wer
 * - v1.0.0ß1 - initial version                                     - 08/19/2015 wer
 */
namespace Ritc\Library\Traits;

/**
 * Class DbTraits
 * @class DbTraits
 * @package Ritc\Library\Traits
 */
trait DbTraits {
    /** @var array */
    private $a_db_config;
    /** @var string */
    private $db_prefix;
    /** @var string */
    private $db_type;

    /**
     * Creates the class properties of a_db_config, db_type and db_prefix from config file or array if passed in.
     * Prefer config file but array is allowed so this can be called without a config file.
     * @param string|array $config_file
     * @return null
     */
    private function createDbParms($config_file = 'db_config.php')
    {
        if (is_array($config_file)) {
            $a_required_keys = ['driver', 'host', 'name', 'user', 'password'];
            if ($this->findMissingKeys($a_required_keys, $config_file) == array()) {
                $a_db = $config_file;
                if (!isset($a_db['prefix'])) {
                    $a_db['prefix'] = '';
                }
                if (!isset($a_db['persist'])) {
                    $a_db['persist'] = true;
                }
                if (!isset($a_db['port'])) {
                    $a_db['port'] = '';
                }
                if (!isset($a_db['userro'])) {
                    $a_db['userro'] = $a_db['user'];
                }
                if (!isset($a_db['passro'])) {
                    $a_db['passro'] = $a_db['password'];
                }
            }
            else {
                $a_db = $this->retrieveDbConfig('db_config.php');
            }
        }
        else {
            $a_db = $this->retrieveDbConfig($config_file);
        }
        $this->a_db_config = $a_db;
        $this->db_prefix   = $a_db['prefix'];
        $this->db_type     = $a_db['driver'];
    }

    /**
     * Determines if any required keys are missing
     * @param array $a_required_keys required
     * @param array $a_check_values required
     * @return array $a_missing_keys
     */
    public function findMissingKeys(array $a_required_keys = array(), array $a_check_values = array())
    {
        if ($a_required_keys == array() || $a_check_values == array()) { return array(); }
        $a_missing_keys = array();
        foreach ($a_required_keys as $key) {
            if (
                array_key_exists($key, $a_check_values)
                ||
                array_key_exists(':' . $key, $a_check_values)
                ||
                array_key_exists(str_replace(':', '', $key), $a_check_values)
            ) {
                // we are happy
            }
            else {
                $a_missing_keys[] = $key;
            }
        }
        return $a_missing_keys;
    }

    /**
     * Changes array keys to be compatible with prepared statements.
     * @param array $array required associative array, named keys
     * @return array fixed key names
     */
    public function prepareKeys(array $array = array())
    {
        $a_new = array();
        if (Arrays::isAssocArray($array)) {
            foreach ($array as $key=>$value) {
                $new_key = strpos($key, ':') === 0 ? $key : ':' . $key;
                $a_new[$new_key] = $value;
            }
            return $a_new;
        }
        elseif (Arrays::isAssocArray($array[0])) {
            foreach ($array as $a_keys) {
                $results = $this->prepareKeys($a_keys);
                if ($results === false) {
                    return false;
                }
                $a_new[] = $results;
            }
            return $a_new;
        }
        else {
            $this->logIt('The array must be an associative array to fix.', LOG_OFF, __METHOD__);
            return false;
        }
    }

    /**
     * Changes array values to help build a prepared statement primarily the WHERE.
     * @param array $array key/value pairs to fix
     * @return array fixed where needed
     */
    public function prepareValues(array $array)
    {
        $a_new = array();
        if (Arrays::isAssocArray($array)) {
            foreach ($array as $key => $value) {
                $new_key = strpos($key, ':') === 0 ? $key : ':' . $key;
                $a_new[$key] = $new_key;
            }
            return $a_new;
        }
        elseif (Arrays::isAssocArray($array[0])) {
            return $this->prepareValues($array[0]);
        }
        else {
            $this->logIt('The array must be an associative array to fix.', LOG_OFF, __METHOD__);
            return false;
        }
    }

    /**
     * @param string $config_file
     * @return bool|array
     */
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
        if (is_array($a_db)) {
            return $a_db;
        }
        else {
            return false;
        }
    }

}
