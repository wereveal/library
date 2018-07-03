<?php
/**
 * Class CacheHelper.
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use Psr\Cache\InvalidArgumentException as CacheException;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheException ;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Simple\AbstractCache;

/**
 * Makes using cache easier by hidding differences btween psr-6 and psr-16 types..
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-20 09:53:42
 * @change_log
 * - v1.0.0 - Initial version                                   - 2018-06-20 wer
 */
class CacheHelper
{
    use LogitTraits;

    /**
     * Type of Cache, e.g. PhpFiles.
     *
     * @var string
     */
    protected $cache_type;
    /**
     * The cache object.
     *
     * @var AbstractAdapter|AbstractCache|CacheItem|TagAwareAdapter
     */
    protected $o_cache;

    /**
     * CacheHelper constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        if (USE_CACHE) {
            $o_cache = $o_di->get('cache');
            if (\is_object($o_cache)) {
                $this->o_cache = $o_cache;
                $this->cache_type = strpos(CACHE_TYPE, 'Simple') !== false
                    ? 'psr-16'
                    : 'psr-6';
            }
        }
    }

    /**
     * Removes all cache key/values.
     */
    public function clearAll()
    {
        if (empty($this->cache_type)) {
            return true;
        }
        return $this->o_cache->prune();
    }

    /**
     * Removes a cache record for specified key.
     *
     * @param string $cache_key
     */
    public function clearKey(string $cache_key = ''):void
    {
        switch ($this->cache_type) {
            case 'psr-6':
                try {
                    $this->o_cache->deleteItem($cache_key);
                }
                catch (CacheException $e) {
                    break;
                }
                break;
            case 'psr-16':
                $this->o_cache->delete($cache_key);
                break;
            default:
                // do nothing
        }
    }

    /**
     * Removes all key/value pairs with specified tag.
     *
     * @param string $tag
     */
    public function clearTag(string $tag = ''):void
    {
        switch ($this->cache_type) {
            case 'psr-6':
                try{
                    $this->o_cache->invalidateTags([$tag]);
                }
                catch (CacheException $e) {
                    // do nothing
                }
                break;
            case 'psr-16':
            default:
                // do nothing
        }
    }

    /**
     * Gets the value for a key.
     *
     * @param string $cache_key
     * @return mixed
     */
    public function get(string $cache_key = '')
    {
        switch ($this->cache_type) {
            case 'psr-6':
                try{
                    $o_item = $this->o_cache->getItem($cache_key);
                    if ($o_item->isHit()) {
                        return $o_item->get();
                    }
                }
                catch (CacheException $e) {
                    // do nothing
                }
                break;
            case 'psr-16':
                if ($this->o_cache->has($cache_key)) {
                    try {
                        return $this->o_cache->get($cache_key);
                    }
                    catch (SimpleCacheException $e) {
                        // do nothing
                    }
                }
                break;
            default:
        }
        return '';
    }

    /**
     * Standard GETter for class property $cache_type.
     *
     * @return string
     */
    public function getCacheType():string
    {
        return $this->cache_type;
    }

    /**
     * Sets a cache key/value pair.
     * Optionally will tag the pair.
     *
     * @param string $cache_key Required
     * @param mixed  $value     Optional but if empty why?
     * @param string $tag       Optional, only used if cache is PSR-6
     * @return bool
     */
    public function set(string $cache_key = '', $value = '', string $tag = ''):bool
    {
        if (empty($cache_key)) {
            return false;
        }
        switch ($this->cache_type) {
            case 'psr-6':
                try {
                    $o_item = $this->o_cache->getItem($cache_key);
                    $o_item->set($value);
                    if (!empty($tag)) {
                        $o_item->tag($tag);
                    }
                    $this->o_cache->save($o_item);
                    return true;
                }
                catch (CacheException $e) {
                    return false;
                }
            case 'psr-16':
                try {
                    $this->o_cache->set($cache_key, $value);
                    return true;
                }
                catch (SimpleCacheException $e) {
                    return false;
                }
                break;
            default:
                return false;
        }
    }
}
