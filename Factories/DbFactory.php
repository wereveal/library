<?php
/**
 *  @brief A Database Factory.
 *  @details This ends up being a two step process always. The first step is to start the factory.
 *  The factory reads in the configuration specified (defaults to a default config) so that it knows
 *  what to connect to. After that you connect to the databse using the factory object.
 *  Connecting to the database returns a \PDO object.
 *  Nothing else should be needed from the factory. As such, version 1.5 was born.
 *  @file DbFactory.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Services
 *  @class DbFactory
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.5.3
 *  @date 2015-01-27 16:25:28
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.5.3 - moved to the Factories namespace                                 - 01/27/2015 wer
 *      v1.5.2 - moved to Services namespace                                      - 11/15/2014 wer
 *      v1.5.1 - changed to implement the changes to the Base class               - 09/23/2014 wer
 *      v1.5.0 - massive change to the factory, cutting out the fat               - 03/25/2014 wer
 *      v1.0.0 - figured it was time to take this out of beta, with one addition. - 02/24/2014 wer
 *      v0.1.2 - minor package change required minor modification                 - 12/19/2013 wer
 *      v0.1.1 - added two additional places the config files can exist           - 2013-11-08
 *      v0.1.0 - initial file creation - 2013-11-06
 *  </pre>
**/
namespace Ritc\Library\Factories;

use Ritc\Library\Abstracts\Base;

class DbFactory extends Base
{
    private static $instance_rw = array();
    private static $instance_ro = array();
    private $config_file;
    private $o_db;

    private function __construct($config_file = 'db_config.php', $read_type = 'rw')
    {
        $this->setPrivateProperties();
        $this->config_file = $config_file;
        $this->read_type = $read_type;
    }
    /**
     *  Starts a Singleton object for the specific database config file
     *  or returns the existing object if it is already started.
     *  It can be noted then that two objects can be created for each
     *  config file, read/write and read only and multiple configs can
     *  be used to create all kinds of database connections - even if
     *  to the same database simply by using different config file name.
     *  @param string $config_file default 'db_config.php'
     *  @param string $read_type Default rw
     *  @return object - reference the the database object created
    **/
    public static function start($config_file = 'db_config.php', $read_type = 'rw')
    {
        list($name, $extension) = explode('.', $config_file);
        if ($extension != 'php' && $extension != 'cfg') { return false; }
        if ($read_type == 'ro') {
            if (!isset(self::$instance_ro[$name])) {
                self::$instance_ro[$name] = new DbFactory($config_file, 'ro');
            }
            return self::$instance_ro[$name];
        }
        else {
            if (!isset(self::$instance_rw[$name])) {
                self::$instance_rw[$name] = new DbFactory($config_file, 'rw');
            }
            return self::$instance_rw[$name];
        }
    }

    public function connect()
    {
        if (is_object($this->o_db)) {
            $this->logIt('The database is already connected.', LOG_OFF);
            return $this->o_db;
        }
        $a_db = $this->retrieveDbParameters();
        try {
            if ($this->read_type == 'ro') {
                $this->o_db = new \PDO(
                    $a_db['dsn'],
                    $a_db['userro'],
                    $a_db['passro'],
                    array(\PDO::ATTR_PERSISTENT=>$a_db['persist']));
            }
            else {
                $this->o_db = new \PDO(
                    $a_db['dsn'],
                    $a_db['user'],
                    $a_db['password'],
                    array(\PDO::ATTR_PERSISTENT=>$a_db['persist']));
            }
            $this->logIt("The dsn is: {$a_db['dsn']}", LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->logIt('Connect to db success.', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $this->o_db;
        }
        catch(\PDOException $e) {
            $this->logIt('Error! Could not connect to database: ' . $e->getMessage(), LOG_ALWAYS);
            $this->logIt(var_export($a_db, true), LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
            return false;
        }
    }

    /**
     *  Turns the config file into an array that is used to connect to the database.
     *  @internal $config_file - must be in one of three places
     *      The default place is in /app/config/
     *      The next place is in the /private directory
     *      Finally, it can be found in the config directory inside the web site itself (worse place).
     *  @return mixed array|false
    **/
    private function retrieveDbParameters()
    {
        $config_file = $this->config_file;
        $config_w_path = APP_PATH . '/config/' . $config_file;
        $this->logIt($config_w_path, LOG_OFF, __METHOD__ . '.' . __LINE__);
        if (!file_exists($config_w_path)) {
            $config_w_path = PRIVATE_PATH . '/' . $config_file;
            if (!file_exists($config_w_path)) {
                $config_w_path = SITE_PATH . '/config/' . $config_file;
            }
        }
        $this->logIt($config_w_path, LOG_OFF, __METHOD__ . '.' . __LINE__);
        if (!file_exists($config_w_path)) {
            return false;
        }
        $a_db = require_once $config_w_path;
        $a_db['dsn'] = $this->createDsn($a_db);
        return $a_db;
    }
    private function createDsn(array $a_db = array())
    {
        if ($a_db == array()) {
            return '';
        }
        else {
            if ($a_db['port'] != '' && $a_db['port'] !== null) {
                return $a_db['driver']
                    . ':host='   . $a_db['host']
                    . ';port='   . $a_db['port']
                    . ';dbname=' . $a_db['name'];
            }
            else {
                return $a_db['driver']
                    . ':host='   . $a_db['host']
                    . ';dbname=' . $a_db['name'];
            }
        }
    }

    ### Magic Method fix
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}
