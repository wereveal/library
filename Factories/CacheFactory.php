<?php
/**
 * Class CacheFactory
 * @package Ritc_Library
 */
namespace Ritc\Library\Factories;

use DomainException;
use PDO;
use PDOException;
use Ritc\Library\Traits\LogitTraits;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

/**
 * Class CacheFactory - creates one of several different Symfony\Component\Cache objects.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.2
 * @date    2020-09-20 15:55:27
 * @change_log
 * - v1.0.0-alpha.2 - Changed to latest version of Symfony\Component\Cache  - 2020-09-20 wer
 * - v1.0.0-alpha.0 - Initial version                                       - 2018-05-12 wer
 */
class CacheFactory
{
    use LogitTraits;

    /** @var array  */
    private static $instance = [];
    /** @var ArrayAdapter|ChainAdapter|FilesystemAdapter|FilesystemTagAwareAdapter|PdoAdapter|PhpArrayAdapter|PhpFilesAdapter|RedisAdapter  */
    private $o_cache;

    /**
     * CacheFactory constructor.
     *
     * @param array $a_cache_config
     */
    private function __construct(array $a_cache_config = [])
    {
        /** @noinspection NestedTernaryOperatorInspection */
        $cache_type = empty($a_cache_config['cache_type'])
            ? defined('CACHE_TYPE') ? CACHE_TYPE : 'PhpFiles'
            : $a_cache_config['cache_type'];
        /** @noinspection NestedTernaryOperatorInspection */
        $lifetime  = empty($a_cache_config['lifetime'])
            ? defined('CACHE_TTL') ? CACHE_TTL : 604800
            : $a_cache_config['lifetime'];
        $namespace = empty($a_cache_config['namespace'])
            ? 'Ritc'
            : $a_cache_config['namespace'];
        $directory = empty($a_cache_config['directory'])
            ? BASE_PATH . '/cache'
            : $a_cache_config['directory']
        ;
        $o_cache   = NULL;
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
            case 'FilesystemTagAware':
                $o_cache = new FilesystemTagAwareAdapter($namespace,$lifetime, $directory);
                break;
            case 'Pdo':
                if ($a_cache_config['connOrDns'] instanceof PDO) {
                    try {
                        $o_cache = new PdoAdapter($a_cache_config['connOrDns'], $namespace, $lifetime, $a_cache_config['options']);
                        try {
                            $o_cache->createTable();
                        }
                        catch(PDOException $e) {
                            // table must exist don't do anything
                        }
                        catch(DomainException $e) {
                            // something not set right
                            $o_cache = null;
                        }
                    }
                    catch (InvalidArgumentException $e) {
                        $o_cache = null;
                    }
                }
                else {
                    $o_cache = null;
                }
                break;
            case 'PhpArray':
                $o_cache = new PhpArrayAdapter($a_cache_config['file'], $a_cache_config['fallback_pool']);
                break;
            case 'Redis':
                $o_cache = new RedisAdapter($a_cache_config['redis_client'], $namespace, $lifetime);
                break;
            case 'RedisTagAware':
                $redis_client = RedisAdapter::createConnection($a_cache_config['redis_client']);
                $o_cache = new RedisTagAwareAdapter($redis_client);
                break;
            case 'PhpFiles':
            default:
                try {
                    $o_cache = new PhpFilesAdapter($namespace, $lifetime, $directory);
                }
                catch (CacheException $e) {
                    $o_cache = null;
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
                    'cache_type' => 'PhpFiles',
                    'directory'  => BASE_PATH . '/cache/',
                    'lifetime'   => 0
                ];
            }
            self::$instance[$name] = new CacheFactory($a_cache_config);
        }
        return self::$instance[$name]->o_cache;
    }
}
