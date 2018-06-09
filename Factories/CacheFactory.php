<?php
/**
 * Class CacheFactory
 * @package Ritc_Library
 */
namespace Ritc\Library\Factories;

use Ritc\Library\Traits\LogitTraits;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\ChainCache;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Cache\Simple\PdoCache;
use Symfony\Component\Cache\Simple\PhpArrayCache;
use Symfony\Component\Cache\Simple\PhpFilesCache;
use Symfony\Component\Cache\Simple\Psr6Cache;
use Symfony\Component\Cache\Simple\RedisCache;

/**
 * Class CacheFactory - creates one of several different Symfony\Component\Cache objects.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2018-05-12 21:42:20
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-12 wer
 */
class CacheFactory
{
    use LogitTraits;

    /** @var array  */
    private static $instance = [];
    /** @var ArrayAdapter|ChainAdapter|FilesystemAdapter|PdoAdapter|PhpArrayAdapter|PhpFilesAdapter|RedisAdapter|ArrayCache|ChainCache|FilesystemCache|PdoCache|PhpArrayCache|PhpFilesCache|Psr6Cache|RedisCache  */
    private $o_cache;

    /**
     * CacheFactory constructor.
     * @param array $a_cache_config
     */
    private function __construct(array $a_cache_config = [])
    {
        $cache_type = empty($a_cache_config['cache_type'])
            ? 'SimplePhpFiles'
            : $a_cache_config['cache_type'];
        $lifetime = empty($a_cache_config['lifetime'])
            ? defined('CACHE_TTL') ? CACHE_TTL : 604800
            : $a_cache_config['lifetime'];
        $namespace = empty($a_cache_config['namespace'])
            ? 'Ritc'
            : $a_cache_config['namespace'];
        $directory = empty($a_cache_config['directory'])
            ? BASE_PATH . '/cache'
            : $a_cache_config['directory']
        ;
        $o_cache = NULL;
        switch ($cache_type) {
            case 'Array':
                $o_cache = new ArrayAdapter($lifetime, $a_cache_config['store_serialized']);
                break;
            case 'Chain':
                $o_cache = new ChainAdapter($a_cache_config['caches'], $lifetime);
                break;
            case 'Filesystem':
                $o_cache = new FilesystemAdapter($namespace, $lifetime, $directory);
                break;
            case 'Pdo':
                $o_cache = new PdoAdapter($a_cache_config['connOrDns'], $namespace, $lifetime, $a_cache_config['options']);
                break;
            case 'PhpArray':
                $o_cache = new PhpArrayAdapter($a_cache_config['file'], $a_cache_config['fallback_pool']);
                break;
            case 'PhpFiles':
                try {
                    $o_cache = new PhpFilesAdapter($namespace, $lifetime, $directory);
                }
                catch (CacheException $e) {
                    error_log("Unable to create PhpFilesAdapter instance: " . $e->getMessage());
                }
                break;
            case 'Redis':
                $o_cache = new RedisAdapter($a_cache_config['redis_client'], $namespace, $lifetime);
                break;
            case 'SimpleArray':
                $o_cache = new ArrayCache($lifetime, $a_cache_config['store_serialized']);
                break;
            case 'SimpleChain':
                $o_cache = new ChainCache($a_cache_config['caches'], $lifetime);
                break;
            case 'SimpleFilesystem':
                $o_cache = new FilesystemCache($namespace, $lifetime, $directory);
                break;
            case 'SimplePdo':
                $o_cache = new PdoCache($a_cache_config['connOrDns'], $namespace, $lifetime, $a_cache_config['options']);
                break;
            case 'SimplePhpArray':
                $o_cache = new PhpArrayCache($a_cache_config['file'], $a_cache_config['fallback_pool']);
                break;
            case 'SimplePsr6':
                $o_cache = new Psr6Cache($a_cache_config['pool_interface']);
                break;
            case 'SimpleRedis':
                $o_cache = new RedisCache($a_cache_config['redis_client'], $namespace, $lifetime);
                break;
            case 'SimplePhpFiles':
            default:
                try {
                    $o_cache = new PhpFilesCache($namespace, $lifetime, $directory);
                }
                catch (CacheException $e) {
                    error_log('Could not create the PhpFilesCache instance: ' . $e->getMessage());
                }
        }
        $this->o_cache = $o_cache;
    }

    /**
     * @param array  $a_cache_config Possible keys with defaults, depends on cache_type [
     *                               'cache_type'       => 'cache_type
     *                               'default_lifetime' => 0
     *                               'store_serialized' => true
     *                               'namespace'        => 'Ritc'
     *                               'redis_client'     => $redis_client (Redis|RedisArray|RedisCluster|Client)
     *                               'directory'        => BASE_PATH . '/cache/'
     *                               'caches'           => []
     *                               'connOrDns'        => $o_pdo or $dns_string
     *                               'cache_file'       => 'some_file'
     *                               'fallback_pool'    => 'fallback_pool' (CacheInterface)
     *                               'pool_interface'   => $something (CacheItemPoolInterface)
     * @param string $name
     * @return mixed
     */
    public static function start(array $a_cache_config = [], string $name = 'main') {
        if (!isset(self::$instance[$name])) {
            if (empty($a_cache_config['cache_type'])) {
                $a_cache_config = [
                    'cache_type' => 'SimplePhpFiles',
                    'directory'  => BASE_PATH . '/cache/',
                    'lifetime'   => 0
                ];
            }
            self::$instance[$name] = new CacheFactory($a_cache_config);
        }
        return self::$instance[$name]->o_cache;
    }
}
