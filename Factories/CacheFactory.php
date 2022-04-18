<?php
/**
 * Class CacheFactory
 * @package Ritc_Library
 */
namespace Ritc\Library\Factories;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Exceptions\FactoryException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\CacheApcu;
use Ritc\Library\Services\CacheByFile;
use Ritc\Library\Services\CacheByFileJson;
use Ritc\Library\Services\CacheByFilePhp;
use Ritc\Library\Services\CacheByFilePhpArray;
use Ritc\Library\Services\CacheDb;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class CacheFactory - creates one of several cache service objects.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.3
 * @date    2022-03-08 19:30:06
 * @change_log
 * - v1.0.0-alpha.3 - removed all Symfony cache adapters, tired of having   - 2022-03-08 wer
 *                    to change everything everytime they chaned something.
 *                    Will slowly add cache service classes.
 * - v1.0.0-alpha.2 - Changed to latest version of Symfony\Component\Cache  - 2020-09-20 wer
 * - v1.0.0-alpha.0 - Initial version                                       - 2018-05-12 wer
 */
class CacheFactory
{
    use LogitTraits;

    private static array $instance = [];
    private object $o_cache;

    /**
     * CacheFactory constructor.
     *
     * @param Di    $o_di
     * @param array $a_cache_config
     * @throws CacheException
     */
    private function __construct(Di $o_di, array $a_cache_config)
    {
        if (!defined('CACHE_TYPE')) {
            define('CACHE_TYPE', 'Db');
        }
        if (!defined('CACHE_PATH')) {
            define('CACHE_PATH', BASE_PATH . '/cache');
        }
        if (!defined('CACHE_TTL')) {
            define('CACHE_TTL', 604800);
        }
        $cache_type = $a_cache_config['cache_type'] ??  CACHE_TYPE;
        $a_cache_config['cache_type'] = $cache_type;
        $a_cache_config['ttl']        = $a_cache_config['ttl'] ?? CACHE_TTL;
        $a_cache_config['cache_path'] = $a_cache_config['cache_path'] ?? CACHE_PATH;
        $o_cache = null;
        switch ($cache_type) {
            case 'APCu':
                // todo create a CacheByApcu service class
                $o_cache = new CacheApcu($a_cache_config);
                break;
            case 'File':
                // todo create a CacheByFile service class
                $a_cache_config['file_ext'] = 'txt';
                try {
                    $o_cache = new CacheByFile($a_cache_config);
                }
                catch (CacheException $e) {
                    throw new CacheException($e->getMessage(), $e->getCode(), $e);
                }
                break;
            case 'Json':
                // todo create a CacheByFileJson service class
                /* extends CacheByFile service class */
                $a_cache_config['file_ext'] = 'json';
                $o_cache = new CacheByFileJson($a_cache_config);
                break;
            case 'PhpArray':
                // todo create a CacheByFilePhpArray service class
                /* extends CacheByFile service class */
                $a_cache_config['file_ext'] = 'php';
                $o_cache = new CacheByFilePhpArray($a_cache_config);
                break;
            case 'PhpFile':
                // todo create a CacheByFilePhp service class
                /* extends CacheByFile service class */
                $a_cache_config['file_ext'] = 'php';
                $o_cache = new CacheByFilePhp($a_cache_config);
                break;
            case 'Redis':
                // todo Someday do a CacheByRedis service class
                break;
            case 'Db':
            default:
                $o_cache = new CacheDb($o_di, $a_cache_config);
        }
        $this->o_cache = $o_cache;
    }

    /**
     * Creates a single instance of a cache object of a specific name.
     *
     * @param Di     $o_di
     * @param array  $a_cache_config Possible keys with defaults, depends on cache_type [
     *                               'cache_type'       => 'cache_type
     *                               'ttl'              => 0
     *                               'redis_client'     => $redis_client (Redis|RedisArray|RedisCluster|Client)
     *                               'directory'        => BASE_PATH . '/cache/'
     * @param string $name           Allows one to create multiple persistent cache methods
     * @return object|null
     * @throws FactoryException
     */
    public static function start(Di $o_di, array $a_cache_config = [], string $name = 'main'): null|object
    {
        if (!isset(self::$instance[$name])) {
            try {
                self::$instance[$name] = new CacheFactory($o_di, $a_cache_config);
            }
            catch (CacheException $e) {
                throw new FactoryException($e->getMessage(), ExceptionHelper::getCodeNumberFactory('start'));
            }
        }
        return self::$instance[$name]->o_cache;
    }
}
