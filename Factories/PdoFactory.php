<?php
/**
 * Class PdoFactory
 * @package Ritc_Library
 */
namespace Ritc\Library\Factories;

use Error;
use PDO;
use PDOException;
use Ritc\Library\Exceptions\FactoryException;
use Ritc\Library\Exceptions\FactoryException as FactoryExceptionAlias;
use Ritc\Library\Traits\DbCommonTraits;

/**
 * Class PdoFactory - The factory returns a \PDO object.
 * Several different \PDO objects can be created based on config files specified.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 4.1.0
 * @date    2021-12-08 18:23:34
 * @change_log
 * - 4.1.0 - Removed Elog from factory                                                  - 2021-12-08 wer
 * - 4.0.0 - updated for php8                                                           - 2021-11-26 wer
 * - 3.0.0 - Changed to set the \PDO::ATTR_ERRMODE, defaults to \PDO::ERRMODE_EXCEPTION - 2017-06-13 wer
 * - 2.1.0 - Simplified the Factory                                                     - 2017-01-25 wer
 * - 2.0.2 - fixed potential bug                                                        - 2017-01-13 wer
 * - 2.0.1 - refactoring of DbTraits reflected here (caused strict standards error).    - 2016-03-19 wer
 * - 2.0.0 - realized a stupid error in thinking, this should produce                   - 08/28/2015 wer
 *            an instance of the PDO not an instance of the factory itself duh!
 *            I believe this was a result of not thinking how to do it correctly.
 *            Renamed class to match what the factory produces.
 * - 1.6.0 - no longer extends Base class, uses DbTraits and LogitTraits                - 08/19/2015 wer
 * - 1.5.3 - moved to the Factories namespace                                           - 01/27/2015 wer
 * - 1.5.2 - moved to Services namespace                                                - 11/15/2014 wer
 * - 1.5.1 - changed to implement the changes to the Base class                         - 09/23/2014 wer
 * - 1.5.0 - massive change to the factory, cutting out the fat                         - 03/25/2014 wer
 * - 1.0.0 - figured it was time to take this out of beta, with one addition.           - 02/24/2014 wer
 * - 0.1.2 - minor package change required minor modification                           - 12/19/2013 wer
 * - 0.1.1 - added two additional places the config files can exist                     - 2013-11-08 wer
 * - 0.1.0 - initial file creation                                                      - 2013-11-06 wer
 */
class PdoFactory
{
    use DbCommonTraits;

    /** @var array */
    private array $a_db_config;
    /** @var array */
    private static array $factory_rw_instance = [];
    /** @var array */
    private static array $factory_ro_instance = [];
    /** @var PDO */
    private PDO $o_db;

    /**
     * Starts a Singleton object for the specific database config file
     * or returns the existing object if it is already started.
     * It can be noted then that two objects can be created for each
     * config file, read/write and read only and multiple configs can
     * be used to create all kinds of database connections - even if
     * to the same database simply by using different config file name.
     *
     * @param string  $config_file default 'db_config.php'
     * @param string  $read_type   Default rw
     * @return PDO
     * @throws FactoryExceptionAlias
     */
    public static function start(string $config_file = 'db_config.php', string $read_type = 'rw'): PDO
    {
        $org_config_file = $config_file;
        if (str_contains($config_file, '/')) {
            $a_parts = explode('/', $config_file);
            $config_file = $a_parts[count($a_parts) - 1];
        }
        [$name, $extension] = explode('.', $config_file);
        if ($extension !== 'php' && $extension !== 'cfg') {
            throw new FactoryException('Invalid file type for configuration.', 30);
        }
        if ($read_type === 'ro') {
            if (!isset(self::$factory_ro_instance[$name])) {
                try {
                    self::$factory_ro_instance[$name] = new self();
                }
                catch (Error $e) {
                    throw new FactoryException($e->getMessage(), 10, $e);
                }
            }
            try {
                return self::$factory_ro_instance[$name]->createPdo($org_config_file, $read_type);
            }
            catch (FactoryException $e) {
                throw new FactoryException($e->errorMessage(), $e->getCode(), $e);
            }
        }
        else {
            if (!isset(self::$factory_rw_instance[$name])) {
                try {
                    self::$factory_rw_instance[$name] = new self();
                    self::$factory_rw_instance[$name] = new self();
                }
                catch (Error $e) {
                    throw new FactoryException($e->getMessage(), 10, $e);
                }
            }
            try {
                return self::$factory_rw_instance[$name]->createPdo($org_config_file, $read_type);
            }
            catch (FactoryException $e) {
                throw new FactoryException($e->errorMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * Creates the PDO instance
     *
     * @param string $config_file
     * @param string $read_type
     * @return PDO
     * @throws FactoryExceptionAlias
     */
    private function createPdo(string $config_file = 'db_config.php', string $read_type = 'rw'): PDO
    {
        if (!empty($this->o_db)) {
            return $this->o_db;
        }
        $a_db = $this->retrieveDbConfig($config_file);
        if (empty($a_db)) {
            $message = 'Could not retrieve the db config file.';
            throw new FactoryException($message, 40);
        }
        $a_db['dsn'] = $this->createDsn($a_db);
        $errmode     = match ($a_db['errmode']) {
            'silent'  => PDO::ERRMODE_SILENT,
            'warning' => PDO::ERRMODE_WARNING,
            default   => PDO::ERRMODE_EXCEPTION,
        };
        try {
            if ($read_type === 'ro') {
                $this->o_db = new PDO(
                    $a_db['dsn'],
                    $a_db['userro'],
                    $a_db['passro'],
                    [
                        PDO::ATTR_PERSISTENT => $a_db['persist'],
                        PDO::ATTR_ERRMODE    => $errmode
                    ]
                );
            }
            else {
                $this->o_db = new PDO(
                    $a_db['dsn'],
                    $a_db['user'],
                    $a_db['password'],
                    [
                        PDO::ATTR_PERSISTENT => $a_db['persist'],
                        PDO::ATTR_ERRMODE    => $errmode
                    ]
                );
            }
            return $this->o_db;
        }
        catch (PDOException $e) {
            throw new FactoryException('Error! Could not connect to database: ' . $e->getMessage(), 100);
        }
    }

    /**
     * Creates the DSN string from the db config array.
     * Needed to connect to the database.
     *
     * @param array $a_db ['port', 'driver', 'host', 'name']
     * @return string
     */
    private function createDsn(array $a_db = array()): string
    {
        if (empty($a_db)) {
            $return_this = '';
        }
        elseif ($a_db['driver'] === 'sqlite') {
            $return_this = $a_db['driver'] .
                           ':' . BASE_PATH .
                           '/' . PRIVATE_DIR_NAME .
                           '/' .  $a_db['host'];
        }
        elseif (!empty($a_db['port']) && $a_db['port'] !== '3306' && $a_db['port'] !== '5432') {
            $return_this = $a_db['driver'] .
                           ':host='   . $a_db['host'] .
                           ';port='   . $a_db['port'] .
                           ';dbname=' . $a_db['name'];
        }
        else {
            $return_this = $a_db['driver'] .
                           ':host=' . $a_db['host'] .
                           ';dbname=' . $a_db['name'];
        }
        return $return_this;
    }

    ### Magic Method fixes
    /**
     * Magic Method Fix
     *
     * @throws FactoryExceptionAlias
     */
    public function __clone()
    {
        throw new FactoryException('Clone is not allowed.', 20);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'PdoFactory';
    }
}
