<?php
namespace Ritc\Library\Interfaces;

use DateInterval;
use Ritc\Library\Exceptions\CacheException;

/**
 * This interface is based on the PSR\SimpleCache\CacheInterface
 */
interface CacheInterface
{
    /**
     * Fetches the value from the cache by unique key.
     *
     * @param string $key           The unique key of the item in the cache.
     * @param null|string $default  Default value to return if the key does not exist.
     * @return string|null          The value of the cache
     * @throws  CacheException
     */
    public function get(string $key, string $default = null): ?string;

    /**
     * Saves data in cache, uniquely reference by a key with an optional expiration TTL time.
     *
     * @param string                $key    The key of the item to store
     * @param string                $value  THe value of the item to store
     * @param null|int|DateInterval $ttl    Optional. The TTL value of the cached item.
     * @return bool                         True on success, false elsewise.
     * @throws  CacheException
     */
    public function set(string $key, string $value, mixed $ttl = null): bool;

    /**
     * Deletes an item from the cache.
     *
     * @param string $key The unique key of cache.
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
     * Fetches multiple cache items by their unique keys.
     *
     * @param array  $a_keys    An array of keys that can be obtained in a single opperation
     * @param mixed  $default   Default value to return for keys that do not exist.
     * @return array            An array of the values obtained.
     * @throws  CacheException
     */
    public function getMultiple(array $a_keys, mixed $default = null): array;

    /**
     * Saves multiple cache items.
     *
     * @param array                 $a_value_pairs  Key=>value pairs to be saved
     * @param DateInterval|int|null $ttl            Optional vaule
     * @return bool                                 True only if all value pairs are set true.
     * @throws  CacheException
     */
    public function setMultiple(array $a_value_pairs, DateInterval|int|null $ttl = null): bool;

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