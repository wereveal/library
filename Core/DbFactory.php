<?php
/**
 *  A Database Factory.
 *  For read/write access to the database based on PDO.
 *  @file DbFactory.php
 *  @namespace Ritc\Library\Core
 *  @class DbFactory
 *  @ingroup ritc_library library
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2013-11-06 11:37:50
 *  @par Change Log
 *      v1.0.0 - initial file creation
 *  @par RITC Library v4.0.0
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Core\Elog;

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
    private $dsn;
    private $o_db;
    protected $o_elog;
    protected $private_properties;
    private function __construct($config_file = 'db_config.php', $read_type = 'rw')
    {
        $this->setPrivateProperties();
        $this->o_elog = Elog::start();
        $this->setDatabaseParameters($config_file);
        $this->read_type = $read_type;
    }
    /**
     *  Starts a Singleton.
     *  This needs to be changed to a factory or something. At this point, this
     *  Class can only access one database. It needs to be able to access multiple databases.
     *  Temporarily, one can change the __construct method to be public to by-pass this problem when needed.
     *  @param str $read_type Default rw
     *  @param bool $config_file default '' specifies exact location of config file
     *      if not specified, the Files class should be use to locate the config file
     *  @return obj - reference the the database object created
    **/
    public static function start($config_file = 'db_config.php', $read_type = 'rw')
    {
        list($name, $extension) = explode('.', $config_file);
        if ($read_type == 'ro') {
            if (!isset(self::$instance_ro[$name])) {
                self::$instance_ro[$name] = new DbFactory($config_file, 'ro');
            }
            return self::$instance_ro[$name];
        } else {
            if (!isset(self::$instance_rw[$name])) {
                self::$instance_rw[$name] = new DbFactory($config_file, 'rw');
            }
            return self::$instance_rw[$name];
        }
    }

    public function connect()
    {
        $this->o_elog->setFromMethod(__METHOD__);
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
            } else {
                $this->o_db = new \PDO(
                    $this->dsn,
                    $this->db_user,
                    $this->db_pass,
                    array(\PDO::ATTR_PERSISTENT=>$this->db_persist));
            }
            $this->o_elog->write("The dsn is: $this->dsn", LOG_OFF);
            $this->o_elog->write('Connect to db success.', LOG_OFF);
            return this->o_db;
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
    public function getSqlErrorMessage()
    {
        return $this->sql_error_message;
    }
    private function setDatabaseParameters($config_file = 'db_config.php')
    {
        $config_w_path = APP_PATH . '/config/' . $config_file;
        if (!file_exists($config_w_path)) {
            $config_w_path = 'app/config/' . $config_file;
        }
        if (!file_exists($config_w_path)) {
            return false;
        }
        $a_database = require $config_w_path;
        $this->db_type    = $a_database['driver'];
        $this->db_host    = $a_database['host'];
        $this->db_port    = $a_database['port'];
        $this->db_name    = $a_database['name'];
        $this->db_user    = $a_database['user'];
        $this->db_pass    = $a_database['password'];
        $this->db_userro  = isset($a_database['userro'])  ? $a_database['userro']  : '';
        $this->db_passro  = isset($a_database['passro'])  ? $a_database['passro']  : '';
        $this->db_persist = isset($a_database['persist']) ? $a_database['persist'] : false;
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
    public function setSqlErrorMessage($type = 'pdo')
    {
        $a_error_stuff = $type == 'pdos' ? $this->o_pdo_statement->errorInfo() : $this->o_db->errorInfo() ;
        $this->sql_error_message = 'SQLSTATE Error Code: ' . $a_error_stuff[0] . ' Driver Error Code: ' . $a_error_stuff[1] . ' Driver Error Message: ' . $a_error_stuff[2];
    }

    ### Magic Method fix
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}
