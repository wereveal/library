<?php
/**
 * @brief     Common functions that could be used in several database classes.
 * @ingroup   lib_traits
 * @file      DbTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.2.0
 * @date      2017-01-13 10:22:05
 * @note <b>Change Log</b>
 * - v2.2.0   - added new property lib_prefix and fixed bug         - 2017-01-13 wer
 * - v2.1.0   - Refactoring of DbCommonTraits reflected here        - 2016-09-23 wer
 * - v2.0.0   - Moved a several methods from DbModel to here        - 2016-03-18 wer
 * - v1.0.0   - first working version                               - 11/27/2015 wer
 * - v1.0.0ß1 - initial version                                     - 08/19/2015 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\Arrays;

/**
 * Class DbTraits
 * @class DbTraits
 * @package Ritc\Library\Traits
 */
trait DbTraits
{
    use DbCommonTraits;

    /** @var array */
    private $a_db_config;
    /** @var string */
    protected $db_prefix;
    /** @var string */
    protected $db_type;
    /** @var  string */
    protected $error_message;
    /** @var  string */
    protected $lib_prefix;

    ### Db Config Utilities ###
    /**
     * Creates the class properties of a_db_config, db_type and db_prefix from config file or array if passed in.
     * Prefer config file but array is allowed so this can be called without a config file.
     * @param string|array $config_file
     */
    private function createDbParams($config_file = 'db_config.php')
    {
        $a_required_keys = ['driver', 'host', 'name', 'user', 'password'];
        if (is_array($config_file)) {
            $a_db = $config_file;
        }
        else {
            $a_db = $this->retrieveDbConfig($config_file);
        }
        if (!Arrays::hasRequiredKeys($a_required_keys, $a_db)) {
            $a_db = [
                'driver'   => 'mysql',
                'host'     => 'localhost',
                'name'     => '',
                'user'     => '',
                'password' => ''
            ];
        }

        if (!isset($a_db['prefix'])) {
            $a_db['prefix'] = '';
        }
        if (!isset($a_db['persist'])) {
            $a_db['persist'] = false;
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
        if (!isset($a_db['lib_prefix'])) {
            $a_db['lib_prefix'] = 'ritc_';
        }
        $this->a_db_config = $a_db;
        $this->db_prefix   = $a_db['prefix'];
        $this->lib_prefix  = $a_db['lib_prefix'];
        $this->db_type     = $a_db['driver'];
    }

    /**
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
     * 'prefix'     => 'app_' (prefix to the database tables)
     * 'lib_prefix' => 'ritc_' (prefix to the database tables that are specific to the Ritc\Libraryj
     * \endcode
     * @param string $special_path Optional path to use instead of the standard locations.
     * @return array
     */
    private function retrieveDbConfig($config_file = 'db_config.php', $special_path = '')
    {
        $config_w_apppath  = '';
        $config_w_privpath = '';
        $config_w_sitepath = '';
        $config_w_path     = $_SERVER['DOCUMENT_ROOT'] . '/config/' . $config_file;
        if (defined('APP_PATH')) {
            $config_w_apppath = APP_PATH . '/config/' . $config_file;
        }
        if (defined('PRIVATE_PATH')) {
            $config_w_privpath = PRIVATE_PATH . '/' . $config_file;
        }
        if (defined('SITE_PATH')) {
            $config_w_sitepath = SITE_PATH . '/config/' . $config_file;
        }
        if ($special_path != '') {
            $config_w_special_path = $special_path . '/' . $config_file;
            if (file_exists($config_w_special_path)) {
                $config_w_path = $config_w_special_path;
            }
        }
        elseif ($config_w_privpath != '' && file_exists($config_w_privpath)) {
            $config_w_path = $config_w_privpath;
        }
        elseif ($config_w_apppath != '' && file_exists($config_w_apppath)) {
            $config_w_path = $config_w_apppath;
        }
        elseif ($config_w_sitepath != '' && file_exists($config_w_sitepath)) {
            $config_w_path = $config_w_sitepath;
        }
        if (!file_exists($config_w_path)) {
            return [];
        }
        $a_db = include $config_w_path;
        if (is_array($a_db)) {
            return $a_db;
        }
        else {
            return [];
        }
    }

    ### General Utilities ###

    ### GETters and SETters ###
    /**
     * Gets the array $a_db_config which holds the config for the db.
     * @return array
     */
    public function getDbConfig()
    {
        return $this->a_db_config;
    }

    /**
     * @return mixed
     */
    public function getDbPrefix()
    {
        return $this->db_prefix;
    }

    /**
     * @return mixed
     */
    public function getDbType()
    {
        return $this->db_type;
    }

    /**
     * Returns the SQL error message
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * Returns the class property lib_prefix.
     * @return string
     */
    public function getLibPrefix()
    {
        return $this->lib_prefix;
    }
    
    /**
     * @param string $value This method does nothing, intentionally.
     * @return null
     */
    protected function setDbConfig($value)
    {
        unset($value);
        return null; // db_config can only be set privately
    }

    /**
     * @param string $value
     * @return null
     */
    protected function setDbPrefix($value)
    {
        unset($value);
        return null; // db prefix can only be set privately
    }

    /**
     * @param string $value
     * @return null
     */
    protected function setDbType($value = '')
    {
        unset($value);
        return null; // db type can only be set privately
    }

    /**
     * @param string $value This method does nothing, intentionally.
     * @return null
     */
    protected function setLibPrefix($value)
    {
        unset($value);
        return null; // $this->lib_prefix can only be set privately
    }
}
