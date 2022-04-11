<?php
namespace Ritc\Library\Interfaces;

use Ritc\Library\Exceptions\CacheException;

/**
 * This interface is based on the PSR\SimpleCache\CacheInterface but adds
 * prefix (similar to tags/pools) methods and sets type declarations.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @date    2022-03-14 14:35:24
 * @version 1.0.0-beta.1
 * @change_log
 * - v1.0.0-beta.1 - initial version                            - 2022-03-14 wer
 */
interface CacheInterface
{
    /**
     * Fetches the value from the cache by unique key.
     *
     * @param string $key     Required, The unique key of the item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     * @return string|null    The value of the cache
     * @throws CacheException
     */
    public function get(string $key, mixed $default): ?string;

    /**
     * Saves data in cache, uniquely reference by a key.
     *
     * @param string $key   Required, The key of the item to store
     * @param string $value Optional, default to '' which is a value in itself.
     * @param int    $ttl   Optional, default to 0=no expiration
     * @return bool         True on success, false elsewise.
     * @throws CacheException
     */
    public function set(string $key, string $value, int $ttl = 0): bool;

    /**
     * Deletes an item from the cache.
     *
     * @param string $key Required. The unique key of cache.
     * @return bool
     * @throws  CacheException
     */
    public function delete(string $key): bool;

    /**
     * Wipes clean the entire cache.
     *
     * @return bool
     * @throws  CacheException
     */
    public function clear(): bool;

    /**
     * Deletes all caches with the first part(s) of a multipart key,
     * e.g. 'the.multipart.key' results in 'the.multipart' being the prefix.
     *
     * @param string $prefix Required.
     * @return bool
     * @throws CacheException
     */
    public function clearByKeyPrefix(string $prefix): bool;

    /**
     * Fetches multiple cache items by their unique keys.
     *
     * @param array  $a_keys  Required. An array of keys that can be obtained in a single opperation
     * @param mixed  $default Optional. Default value to return for keys that do not exist.
     * @return array          An array of the values obtained.
     * @throws  CacheException
     */
    public function getMultiple(array $a_keys, mixed $default = null): array;

    /**
     * Fetches multiple cache items by their prefix.
     * Prefix the part of the total multipart key.
     * e.g. 'the.multipart.key' results in 'the.multipart' being the prefix.
     *
     * @param string $prefix Required.
     * @param mixed $default Optional, defaults to null
     * @return array         An array of the values obtained.
     */
    public function getMultipleByPrefix(string $prefix, string $default = null): array;

    /**
     * Saves multiple cache items.
     *
     * @param array  $a_value_pairs Key=>value pairs to be saved [$key => $value, $key => $value]
     * @param int    $ttl           Optional, defaults to 0=no expiration
     * @return bool                 True only if all value pairs are set true.
     * @throws CacheException
     */
    public function setMultiple(array $a_value_pairs, int $ttl = 0): bool;

    /**
     * @param array $a_keys List of cache keys to delete
     * @return bool
     * @throws  CacheException
     */
    public function deleteMultiple(array $a_keys): bool;

    /**
     * Determins whether an item is present in the cache.
     *
     * NOTE: It is recommended by PSR that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache key to check.
     * @return bool
     * @throws  CacheException
     */
    public function has(string $key): bool;
}