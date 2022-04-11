<?php
/**
 * Class CacheFactory
 * @package Ritc_Library
 */
namespace Ritc\Library\Factories;

use Ritc\Library\Services\CacheDb;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class CacheFactory - creates one of several cache service objects.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.3
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
     */
    private function __construct(Di $o_di, array $a_cache_config = [])
    {
        $a_cache_config['cache_type'] = $a_cache_config['cache_type'] ?? defined('CACHE_TYPE') ? CACHE_TYPE : 'Db';
        $a_cache_config['ttl']        = $a_cache_config['ttl']        ?? defined('CACHE_TTL') ? CACHE_TTL : 604800;
        $a_cache_config['namespace']  = $a_cache_config['namespace']  ?? 'Ritc';
        $a_cache_config['directory']  = $a_cache_config['directory']  ?? BASE_PATH . '/cache';
        $o_cache   = NULL;
        switch ($a_cache_config['cache_type']) {
            case 'Json':
                // todo create a CacheByFileJson service class
                // extends CacheByFile service class
            case 'PhpArray':
                // todo create a CacheByFilePhpArray service class
                // extends CacheByFile service class
            break;
            case 'PhpFile':
                // todo create a CacheByFilePhp service class
                // extends CacheByFile service class
                break;
            case 'Redis':
                // todo Someday do a CacheByRedis service class
                break;
            case 'File':
                // todo create a CacheByFile service class
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
     *                               'store_serialized' => true
     *                               'namespace'        => 'Ritc'
     *                               'redis_client'     => $redis_client (Redis|RedisArray|RedisCluster|Client)
     *                               'directory'        => BASE_PATH . '/cache/'
     *                               'caches'           => []
     *                               'connOrDns'        => $o_pdo or $dns_string
     *                               'cache_file'       => 'some_file'
     *                               'fallback_pool'    => 'fallback_pool' (CacheInterface)
     *                               'pool_interface'   => $something (CacheItemPoolInterface)
     * @param string $name           Allows one to create multiple persistent cache methods
     * @return object|null
     */
    public static function start(Di $o_di, array $a_cache_config = [], string $name = 'main'): null|object
    {
        if (!isset(self::$instance[$name])) {
            if (empty($a_cache_config['cache_type'])) {
                $a_cache_config = [
                    'cache_type' => 'Db',
                    'ttl'   => CACHE_TTL ?? 33600,
                    'namespace'  => 'Ritc'
                ];
            }
            self::$instance[$name] = new CacheFactory($o_di, $a_cache_config);
        }
        return self::$instance[$name]->o_cache;
    }
}
