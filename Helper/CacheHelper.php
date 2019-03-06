<?php
/**
 * Class CacheHelper.
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use Psr\Cache\CacheException as PsrException;
use Psr\Cache\InvalidArgumentException as CacheException;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheException ;
use Ritc\Library\Factories\CacheFactory;
use Ritc\Library\Traits\LogitTraits;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Simple\AbstractCache;

/**
 * Makes using cache easier by hidding differences btween psr-6 and psr-16 types..
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.1
 * @date    2018-12-18 13:49:30
 * @change_log
 * - v1.0.1 - Bug Fixes                                         - 2018-12-18 wer
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
    protected $cache_type = '';
    /**
     * The cache object.
     *
     * @var AbstractAdapter|AbstractCache|CacheItem|TagAwareAdapter
     */
    protected $o_cache;

    /**
     * CacheHelper constructor.
     *
     * @param CacheFactory $o_cache
     */
    public function __construct($o_cache)
    {
        if (\is_object($o_cache)) {
            $this->o_cache = $o_cache;
            $this->cache_type = strpos(CACHE_TYPE, 'Simple') !== false
                ? 'psr-16'
                : 'psr-6';
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
        $this->o_cache->clear();
        $this->o_cache->reset();
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
                try {
                    $this->o_cache->delete($cache_key);
                }
                catch (CacheException $e) {
                    break;
                }
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
     * Removes all key/value pairs with specified tags.
     *
     * @param array $a_tags
     */
    public function clearTags(array $a_tags = []):void
    {
        switch ($this->cache_type) {
            case 'psr-6':
                try{
                    $this->o_cache->invalidateTags($a_tags);
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
     * Deletes a specified key=>value pair from cache.
     *
     * @param string $cache_key
     */
    public function deleteItem(string $cache_key = ''):void
    {
        try {
            $this->o_cache->deleteItem($cache_key);
        }
        catch (CacheException $e) {
            // do nothing
        }
    }

    /**
     * Delete specific key=>value pairs.
     *
     * @param array $a_keys
     */
    public function deleteItems(array $a_keys = []):void
    {
        try {
            $this->o_cache->deleteItems($a_keys);
        }
        catch (CacheException $e) {
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
                    catch (CacheException $e) {
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
     * Checks to see if there is a cache key.
     *
     * @param string $cache_key
     * @return bool
     */
    public function hasItem(string $cache_key = ''):bool
    {
        try {
            return $this->o_cache->hasItem($cache_key);
        }
        catch(CacheException $e) {
            return false;
        }
    }

    /**
     * Sets a cache key/value pair.
     * Optionally will tag the pair.
     *
     * @param string $cache_key Required
     * @param mixed $value Optional but if empty why?
     * @param string $tag Optional, only used if cache is PSR-6
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
                        try {
                            $o_item->tag($tag);
                        }
                        catch (PsrException $e) {
                            return false;
                        }
                        catch (\Exception $e) {
                           return false;
                        }
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
