<?php
/**
 * Trait DbTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\Arrays;

/**
 * Common functions that could be used in several database classes.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v4.0.0
 * @date    2021-11-30 17:48:35
 * @change_log
 * - v4.0.0   - updated to php8 (only)                              - 2021-11-30 wer
 * - v3.1.0   - moved retrieveDbConfig to DbCommonTraits            - 2017-01-25 wer
 * - v3.0.0   - changed the db prefix way of working                - 2017-01-14 wer
 * - v2.2.0   - added new property lib_prefix and fixed bug         - 2017-01-13 wer
 * - v2.1.0   - Refactoring of DbCommonTraits reflected here        - 2016-09-23 wer
 * - v2.0.0   - Moved a several methods from DbModel to here        - 2016-03-18 wer
 * - v1.0.0   - first working version                               - 11/27/2015 wer
 * - v1.0.0ß1 - initial version                                     - 08/19/2015 wer
 */
trait DbTraits
{
    use DbCommonTraits;

    /** @var array */
    private array $a_db_config;
    /** @var  array */
    protected array $a_prefix;
    /** @var  string */
    protected string $db_prefix;
    /** @var string */
    protected string $db_type;
    /** @var  string */
    protected string $error_message;

    ### Db Config Utilities ###
    /**
     * Creates the class properties of a_db_config, db_type and db_prefix from config file or array if passed in.
     * Prefer config file but array is allowed so this can be called without a config file.
     *
     * @param array|string $config_file
     */
    private function createDbParams(array|string $config_file = 'db_config.php'):void
    {
        $a_required_keys = ['driver', 'host', 'name', 'user', 'password'];
        if (is_array($config_file)) {
            $a_db = $config_file;
        }
        else {
            $a_db = $this->retrieveDbConfig($config_file);
        }
        if (!Arrays::hasRequiredKeys($a_db, $a_required_keys)) {
            $a_db = [
                'driver'   => 'mysql',
                'host'     => 'localhost',
                'name'     => '',
                'user'     => '',
                'password' => ''
            ];
        }
        $a_prefix = [];
        foreach ($a_db as $key => $value) {
            if (str_ends_with($key, '_prefix')) {
                $short_key = str_replace('_prefix', '', $key);
                $a_prefix[$short_key] = $value;
            }
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
        if (!isset($a_prefix['db'])) {
            $a_prefix['db'] = $a_db['prefix'] ?? '';
            $a_db['db_prefix'] = $a_db['prefix'] ?? '';
        }
        if (!isset($a_prefix['lib'])) {
            $a_prefix['lib'] = 'ritc_';
            $a_db['lib_prefix'] = 'ritc_';
        }
        $this->a_db_config = $a_db;
        $this->db_type     = $a_db['driver'];
        $this->db_prefix   = $a_prefix['db'];
        $this->a_prefix    = $a_prefix;
    }

    ### General Utilities ###

    /**
     * Reloads Db Config file.
     *
     * @param string $config_file
     */
    public function reloadDbConfig(string $config_file = 'db_config.php'):void
    {
        $this->createDbParams($config_file);
    }

    ### GETters and SETters ###
    /**
     * Gets the array $a_db_config which holds the config for the db.
     *
     * @return array
     */
    public function getDbConfig():array
    {
        return $this->a_db_config;
    }

    /**
     * @param string $prefix optional, default to db
     * @return mixed
     */
    public function getDbPrefix(string $prefix = 'db'): mixed
    {
        return $this->a_prefix[$prefix];
    }

    /**
     * @return string
     */
    public function getDbType(): string
    {
        return $this->db_type;
    }

    /**
     * Returns the SQL error message.
     *
     * @return string
     */
    public function getErrorMessage():string
    {
        return $this->error_message;
    }

    /**
     * Returns the class property lib_prefix.
     *
     * @return string
     */
    public function getLibPrefix():string
    {
        return $this->a_prefix['lib'];
    }

    /**
     * Standard Getter.
     *
     * @return array
     */
    public function getPrefixArray():array
    {
        return $this->a_prefix;
    }

    /**
     * Allows one to set a db prefix without it being in the db_config file.
     *
     * @param string $name
     * @param string $value
     */
    protected function setDbPrefix(string $name = 'db' , string $value = 'ritc_'):void
    {
        $this->a_prefix[$name] = $value;
    }
}
