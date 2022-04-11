<?php
/**
 * Class CacheHelperSymfony.
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use Exception;
use Psr\Cache\CacheException as PsrException;
use Psr\Cache\InvalidArgumentException as CacheException;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheException ;
use Ritc\Library\Factories\CacheFactory;
use Ritc\Library\Traits\LogitTraits;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\CacheItem;

/**
 * Makes using cache easier by hidding differences btween psr-6 and psr-16 types..
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.2
 * @date    2021-11-29 15:26:22
 * @todo    Bug fixes, compatibility to latest version of Symfony\Cache
 * @deprecated 2022-03-14 13:54:07
 * @change_log
 * - v1.0.0-alpha.2 - Changed whole thing to alpha, updated for php8    - 2021-11-29 wer
 *                    Lots of figuring out how to make sure this works
 * - v1.0.0-alpha.1 - Bug Fixes                                         - 2018-12-18 wer
 * - v1.0.0-alpha.0 - Initial version                                   - 2018-06-20 wer
 */
class CacheHelperSymfony
{
    use LogitTraits;

    /**
     * Type of Cache, e.g. PhpFiles.
     *
     * @var string
     */
    protected string $cache_type = '';
    /**
     * The cache object.
     *
     * @var AbstractAdapter|CacheItem|TagAwareAdapter
     */
    protected AbstractAdapter|CacheItem|TagAwareAdapter $o_cache;

    /**
     * CacheHelperSymfony constructor.
     *
     * @param CacheFactory $o_cache
     */
    public function __construct(CacheFactory $o_cache)
    {
        $this->o_cache = $o_cache;
        $this->cache_type = str_contains(CACHE_TYPE, 'Simple')
            ? 'psr-16'
            : 'psr-6';
    }

    /**
     * Removes all cache key/values.
     */
    public function clearAll(): bool
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
                catch (CacheException) {
                    break;
                }
                break;
            case 'psr-16':
                try {
                    $this->o_cache->delete($cache_key);
                }
                catch (CacheException) {
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
                catch (CacheException) {
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
                catch (CacheException) {
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
        catch (CacheException) {
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
        catch (CacheException) {
            // do nothing
        }
    }

    /**
     * Gets the value for a key.
     *
     * @param string $cache_key
     * @return mixed
     */
    public function get(string $cache_key = ''): mixed
    {
        switch ($this->cache_type) {
            case 'psr-6':
                try{
                    $o_item = $this->o_cache->getItem($cache_key);
                    if ($o_item->isHit()) {
                        return $o_item->get();
                    }
                }
                catch (CacheException) {
                    // do nothing
                }
                break;
            case 'psr-16':
                try {
                    if ($this->o_cache->has($cache_key)) {
                        try {
                            return $this->o_cache->get($cache_key);
                        }
                        catch (CacheException) {
                            // do nothing
                        }
                    }
                }
                catch (SimpleCacheException) {
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
        catch(CacheException) {
            return false;
        }
    }

    /**
     * Sets a cache key/value pair.
     * Optionally will tag the pair.
     *
     * @param string $cache_key Required
     * @param mixed $value      Optional but if empty why?
     * @param string $tag       Optional, only used if cache is PSR-6
     * @return bool
     */
    public function set(string $cache_key = '', mixed $value = '', string $tag = ''):bool
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
                        catch (PsrException | Exception) {
                            return false;
                        }
                    }
                    $this->o_cache->save($o_item);
                    return true;
                }
                catch (CacheException) {
                    return false;
                }
            case 'psr-16':
                $this->o_cache->set($value);
                return true;
            default:
                return false;
        }
    }
}
