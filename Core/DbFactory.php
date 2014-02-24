<?php
/**
 *  @brief A Database Factory.
 *  @details This ends up being a two step process always. The first step is to start the factory.
 *  The factory reads in the configuration specified (defaults to a default config) so that it knows
 *  what to connect to. After that you connect to the databse using the factory object.
 *  Connecting to the database returns a \PDO object.
 *  @file DbFactory.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class DbFactory
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-02-24 16:17:31
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - figured it was time to take this out of beta, with one addition. - 02/24/2014 wer
 *      v0.1.2 - minor package change required minor modification - 12/19/2013 wer
 *      v0.1.1 - added two additional places the config files can exist - 2013-11-08
 *      v0.1.0 - initial file creation - 2013-11-06
 *  </pre>
**/
namespace Ritc\Library\Core;


class DbFactory extends Base
{
    private static $instance_rw = array();
    private static $instance_ro = array();
    private $db_name;
    private $db_host;
    private $db_pass;
    private $db_passro;
    private $db_persist;
    private $db_port;
    private $db_type;
    private $db_user;
    private $db_userro;
    private $db_prefix;
    private $dsn;
    private $o_db;
    protected $o_elog;
    protected $private_properties;
    protected $sql_error_message;

    private function __construct($config_file = 'db_config.php', $read_type = 'rw')
    {
        $this->setPrivateProperties();
        $this->o_elog = Elog::start();
        $this->setDbParameters($config_file);
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
            $this->o_elog->write('The database is already connected.', LOG_OFF);
            return $this->o_db;
        }
        $this->setDsn();
        try {
            if ($this->read_type == 'ro') {
                $this->o_db = new \PDO(
                    $this->dsn,
                    $this->db_userro,
                    $this->db_passro,
                    array(\PDO::ATTR_PERSISTENT=>$this->db_persist));
            }
            else {
                $this->o_db = new \PDO(
                    $this->dsn,
                    $this->db_user,
                    $this->db_pass,
                    array(\PDO::ATTR_PERSISTENT=>$this->db_persist));
            }
            $this->o_elog->write("The dsn is: $this->dsn", LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->o_elog->write('Connect to db success.', LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $this->o_db;
        }
        catch(\PDOException $e) {
            $this->o_elog->write('Error! Could not connect to database: ' . $e->getMessage(), LOG_ALWAYS);
            return false;
        }
    }

    ### Getters and Setters
    /**
     *  Get the value of the property specified.
     *  @param $var_name (str)
     *  @return mixed - value of the property
     *  @note - this is normally set to private so not to be used
    **/
    public function getVar($var_name)
    {
        return $this->$var_name;
    }
    public function getDb()
    {
        return $this->o_db;
    }
    public function getDbType()
    {
        return $this->db_type;
    }
    public function getDbPrefix()
    {
        return $this->db_prefix;
    }
    public function getSqlErrorMessage()
    {
        return $this->sql_error_message;
    }

    /**
     *  Sets the class properties used for connecting to the database.
     *  @param string $config_file - must be in one of three places
     *      The default place is in /app/config/
     *      The next place is in the /private directory
     *      Finally, it can be found in the config directory inside the web site itself (worse place).
     *  @return bool
     */
    private function setDbParameters($config_file = 'db_config.php')
    {
        $config_w_path = APP_PATH . '/config/' . $config_file;
        if (!file_exists($config_w_path)) {
            $config_w_path = PRIVATE_PATH . '/' . $config_file;
            if (!file_exists($config_w_path)) {
                $config_w_path = SITE_PATH . '/config/' . $config_file;
            }
        }
        if (!file_exists($config_w_path)) {
            return false;
        }
        $a_database = require_once $config_w_path;
        $this->db_type    = $a_database['driver'];
        $this->db_host    = $a_database['host'];
        $this->db_port    = $a_database['port'];
        $this->db_name    = $a_database['name'];
        $this->db_user    = $a_database['user'];
        $this->db_pass    = $a_database['password'];
        $this->db_userro  = isset($a_database['userro'])  ? $a_database['userro']  : '';
        $this->db_passro  = isset($a_database['passro'])  ? $a_database['passro']  : '';
        $this->db_persist = isset($a_database['persist']) ? $a_database['persist'] : false;
        $this->db_prefix  = isset($a_database['prefix'])  ? $a_database['prefix']  : '';
        return true;
    }
    public function setDbName($value = '')
    {
        if ($value !== '') {
            $this->db_name = $value;
        }
    }
    public function setDbHost($value = '')
    {
        if ($value !== '') {
            $this->db_host = $value;
        }
    }
    public function setDbPass($value = '')
    {
        if ($value !== '') {
            $this->db_pass = $value;
        }
    }
    public function setDbPassro($value = '')
    {
        if ($value !== '') {
            $this->db_passro = $value;
        }
    }
    public function setDbPersist($value = '')
    {
        if (($value !== '') && (is_bool($value) !== false)) {
            $this->db_type = $value;
        }
    }
    public function setDbPrefix($value = '')
    {
        $this->db_prefix = $value;
    }
    public function setDbType($value = '')
    {
        $a_allowed_types = array('mysql', 'sqlite', 'pgsql');
        if (($value !== '') && (array_search($value, $a_allowed_types) !== false)) {
            $this->db_type = $value;
        }
    }
    public function setDbUser($value = '')
    {
        if ($value !== '') {
            $this->db_user = $value;
        }
    }
    public function setDbUserro($value = '')
    {
        if ($value !== '') {
            $this->db_userro = $value;
        }
    }
    public function setDsn($value = '')
    {
        if ($value != '') {
            $this->dsn = $value;
        } else {
            if ($this->db_port != '' && $this->db_port !== null) {
                $this->dsn = $this->db_type . ':host=' . $this->db_host . ';port=' . $this->db_port . ';dbname=' . $this->db_name;
            } else {
                $this->dsn = $this->db_type . ':host=' . $this->db_host . ';dbname=' . $this->db_name;
            }
        }
    }
    public function setSqlErrorMessage($value = '')
    {
        $this->sql_error_message = $value;
    }

    ### Magic Method fix
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}
