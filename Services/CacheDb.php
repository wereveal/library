<?php
/**
 * Class CacheDb
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Services;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Interfaces\CacheInterface;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Models\CacheModel;

/**
 * Class CacheDb
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-beta.1
 * @date    2022-03-06 19:32:57
 * @change_log
 * - v1.0.0-beta.1 Initial Version
 */
class CacheDb implements CacheInterface
{
    private int $default_ttl = CACHE_TTL;
    private CacheModel $o_cache_model;

    public function __construct(Di $o_di, array $a_cache_config)
    {
        /** @var DbModel $o_db */
        $o_db          = $o_di->get('db');
        $this->o_cache_model = new CacheModel($o_db);
        if (!empty($a_cache_config['ttl'])) {
            $this->default_ttl = $a_cache_config['ttl'];
        }
    }

    /**
     * Fetches the value from the cache by unique key.
     *
     * @param string $key     Required, The unique key of the item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     * @return string|null    The value of the cache
     * @throws CacheException
     */
    public function get(string $key, mixed $default = null): ?string
    {
        try {
            $value = $this->o_cache_model->readByKey($key);
            if (empty($value[0]['cache_expires']) || $value[0]['cache_expires'] < time()) {
                $this->delete($key);
                return $default;
            }
            return $value[0]['cache_value'] ?? $default;
        }
        catch (ModelException $e) {
            $error_code = ExceptionHelper::getCodeNumberCache('read');
            throw new CacheException($e->getMessage(), $error_code, $e);
        }
    }

    /**
     * Saves data in cache, uniquely reference by a key.
     *
     * @param string $key   Required, The key of the item to store
     * @param string $value Optional, default to '' which is a value in itself.
     * @param int    $ttl   Optional, default to 0=no expiration
     * @return bool         True on success, false elsewise.
     * @throws CacheException
     */
    public function set(string $key, string $value, int $ttl = 0): bool
    {
        if (!empty($key)) {
            try {
                $ttl = $ttl ?? $this->default_ttl;
                $expires = time() + $ttl;
                $a_values = [
                    'cache_name'      => $key,
                    'cache_value'     => $value,
                    'cache_expires'   => $expires
                ];
                return $this->o_cache_model->updateOrCreate($a_values);
            }
            catch (CacheException $e) {
                throw new CacheException('Database Error' . $e->errorMessage(),
                                         ExceptionHelper::getCodeNumberCache('database'),
                                         $e);
            }
        }
        throw new CacheException('Missing key', ExceptionHelper::getCodeNumberCache('missing_key'));
    }

    /**
     * Deletes an item from the cache.
     *
     * @param string $key Required. The unique key of cache.
     * @return bool
     * @throws  CacheException
     */
    public function delete(string $key): bool
    {
        if (!empty($key)) {
            try {
                return $this->o_cache_model->delete($key);
            }
            catch (ModelException $e) {
                throw new CacheException('Database Error: ' . $e->errorMessage(),
                                         ExceptionHelper::getCodeNumberCache('delete'),
                                         $e);
            }
        }
        throw new CacheException('Missing Cache Name', ExceptionHelper::getCodeNumberCache('missing_key'));
    }

    /**
     * Wipes clean the entire cache.
     *
     * @return bool
     * @throws  CacheException
     */
    public function clear(): bool
    {
        try {
            $result = $this->o_cache_model->clearCache();
            return $result !== false;
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes all caches with the first part(s) of a multipart key,
     * e.g. 'the.multipart.key' results in 'the.multipart' being the prefix.
     *
     * @param string $prefix Required.
     * @return bool
     * @throws CacheException
     */
    public function clearByKeyPrefix(string $prefix): bool
    {
        try {
            $result = $this->o_cache_model->deleteByKeyPartial($prefix);
            return $result !== false;
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Fetches multiple cache items by their unique keys.
     *
     * @param array  $a_keys  Required. An array of keys that can be obtained in a single opperation
     * @param mixed  $default Optional. Default value to return for keys that do not exist.
     * @return array          An array of the values obtained.
     * @throws  CacheException
     */
    public function getMultiple(array $a_keys, mixed $default = null): array
    {
        try {
            $a_found = $this->o_cache_model->readByKeys($a_keys);
            foreach ($a_keys as $key) {
                if (!empty($a_found[$key])) {
                    $a_found[$key] = $default;
                }
            }
        }
        catch (ModelException $e) {
            throw new CacheException('Could not get the caches.', ExceptionHelper::getCodeNumberCache('database'), $e);
        }
        return $a_found;
    }

    /**
     * Fetches multiple cache items by their prefix.
     * Prefix the part of the total multipart key.
     * e.g. 'the.multipart.key' results in 'the.multipart' being the prefix.
     *
     * @param string $prefix  Required.
     * @param mixed  $default Optional, defaults to null
     * @return array          An array of the values obtained.
     * @throws CacheException
     */
    public function getMultipleByPrefix(string $prefix, string $default = null): array
    {
        // TODO: Implement getMultipleByPrefix() method.
        $a_return_this = [];
        try {
            $a_results = $this->o_cache_model->readByKeyPartial($prefix);
            foreach ($a_results as $a_result) {
                if ($a_result['cache_expires'] >= time()) {
                    $a_return_this[] = [$a_result['cache_key'] => $a_result['cache_value']];
                }
                else {
                    $this->o_cache_model->delete($a_result['cache_id']);
                }
            }
        }
        catch (ModelException $e) {
            throw new CacheException(
                'Unable to get the cache',
                ExceptionHelper::getCodeNumberCache('database'),
                $e
            );
        }
        return $a_return_this;
    }

    /**
     * Saves multiple cache items.
     *
     * @param array $a_value_pairs Key=>value pairs to be saved [$key => $value, $key => $value]
     * @param int   $ttl           Optional, defaults to 0=no expiration
     * @return bool                True only if all value pairs are set true.
     * @throws CacheException
     */
    public function setMultiple(array $a_value_pairs, mixed $ttl = null): bool
    {
        foreach ($a_value_pairs as $key => $value) {
            try {
                $this->set($key, $value, $ttl);
            }
            catch (CacheException $e) {
                throw new CacheException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return true;
    }

    /**
     * Deletes multiple cache records by key.
     *
     * @param array $a_keys List of cache keys to delete
     * @return bool
     * @throws  CacheException
     */
    public function deleteMultiple(array $a_keys): bool
    {
        foreach ($a_keys as $key) {
            try {
                $this->delete($key);
            }
            catch (CacheException $e) {
                throw new CacheException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return true;
    }

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
    public function has(string $key): bool
    {
        try {
            $value = $this->o_cache_model->readByKey($key);
            return !empty($value[0]);
        }
        catch (ModelException $e) {
            throw new CacheException(
                'Unable to determine if the cache exists.',
                ExceptionHelper::getCodeNumberCache('operation'),
                $e
            );
        }
    }

    /**
     * @return int
     */
    public function getDefaultTtl(): int
    {
        return $this->default_ttl;
    }

    /**
     * @param int $default_ttl
     */
    public function setDefaultTtl(int $default_ttl): void
    {
        $this->default_ttl = $default_ttl ?? CACHE_TTL;
    }
}