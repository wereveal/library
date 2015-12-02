<?php
/**
 *  @brief     A PDO Factory.
 *  @details   The factory returns a \PDO object.
 *  @ingroup   ritc_library Services
 *  @file      PdoFactory.php
 *  @namespace Ritc\Library\Services
 *  @class     PdoFactory
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   2.0.0
 *  @date      2015-08-28 08:01:33
 *  @note <pre><b>Change Log</b>
 *      v2.0.0 - realized a stupid error in thinking, this should produce         - 08/28/2015 wer
 *               an instance of the PDO not an instance of the factory itself duh!
 *               I believe this was a result of not thinking how to do it correctly.
 *               Renamed class to match what the factory produces.
 *      v1.6.0 - no longer extends Base class, uses DbTraits and LogitTraits      - 08/19/2015 wer
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

use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbTraits;
use Ritc\Library\Traits\LogitTraits;

class PdoFactory
{
    use DbTraits, LogitTraits;

    private static $factory_rw_instance = array();
    private static $factory_ro_instance = array();
    private $config_file;
    private $o_db;
    private $read_type;

    private function __construct($config_file, $read_type, $o_di)
    {
        $this->config_file = $config_file;
        $this->read_type = $read_type;
        /* Need to inject the elog instance here since it is needed before
           it can be injected via the trait method, setElog() */
        if (DEVELOPER_MODE && is_object($o_di)) {
            $this->o_elog = $o_di->get('elog');
        }
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
     *  @param Di     $o_di
     *  @return object PDO object
    **/
    public static function start($config_file = 'db_config.php', $read_type = 'rw', Di $o_di)
    {
        list($name, $extension) = explode('.', $config_file);
        if ($extension != 'php' && $extension != 'cfg') { return false; }
        if ($read_type == 'ro') {
            if (!isset(self::$factory_ro_instance[$name])) {
                self::$factory_ro_instance[$name] = new PdoFactory($config_file, 'ro', $o_di);
            }
            return self::$factory_ro_instance[$name]->createPdo();
        }
        else {
            if (!isset(self::$factory_rw_instance[$name])) {
                self::$factory_rw_instance[$name] = new PdoFactory($config_file, 'rw', $o_di);
            }
            return self::$factory_rw_instance[$name]->createPdo();
        }
    }

    /**
     *  Creates the \PDO instance
     *  @return \PDO
     */
    private function createPdo()
    {
        if (is_object($this->o_db)) {
            $this->logIt('The database is already connected.', LOG_OFF);
            return $this->o_db;
        }
        if ($this->config_file == '') {
            $this->config_file = 'db_config.php';
        }
        $a_db = $this->retrieveDbConfig($this->config_file);
        $a_db['dsn'] = $this->createDsn($a_db);
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
            $this->a_db_config = $a_db;
            return $this->o_db;
        }
        catch(\PDOException $e) {
            $this->logIt('Error! Could not connect to database: ' . $e->getMessage(), LOG_ALWAYS);
            $this->logIt(var_export($a_db, true), LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
            return false;
        }
    }
    /**
     *  Creates the DSN string from the db config array.
     *  Needed to connect to the database.
     *  @param array $a_db ['port', 'driver', 'host', 'name']
     *  @return string
     */
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
