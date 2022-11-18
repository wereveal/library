<?php
namespace Ritc\Library\Traits;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Helper\CacheHelper;
use Ritc\Library\Helper\ExceptionHelper;

/**
 * CacheTraits is used by the file based caching services.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-beta.1
 * @date    2022-03-14 14:14:58
 * @change_log
 * - v1.0.0-beta.1 - initial version                            - 2022-03-14 wer
 */
trait CacheByFileTraits
{
    /** @var string */
    protected string $cache_path;
    /** @var string  */
    protected string $cache_type;
    /** @var int */
    protected int $default_ttl;
    /** @var string  */
    protected string $file_ext;

    /**
     * Wipes clean the entire cache.
     *
     * @return bool
     * @throws  CacheException
     */
    public function clear(): bool
    {
        try {
            $a_files = CacheHelper::fileInfoByPath($this->cache_path);
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode());
        }
        foreach($a_files as $a_file) {
            if (!unlink($a_file['with_path'])) {
                return false;
            }
        }
        return true;
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
            $a_files = CacheHelper::fileInfoByPrefix($prefix);
            foreach ($a_files as $a_file) {
                if ($a_file['file_ext'] === $this->file_ext) {
                    $result = unlink($a_file['with_path']);
                    if (!$result) {
                        return false;
                    }
                }
            }
            return true;
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Transverses recursively through the designated directory path and
     * deletes all files therein past their expiration date.
     *
     * @param string $dir_path
     * @return bool
     * @throws CacheException
     */
    public function cleanExpiredFiles(string $dir_path): bool
    {
        try {
            $a_files = CacheHelper::fileInfoByPath($dir_path);
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode());
        }
        foreach ($a_files as $a_file) {
            if ($a_file['expires'] <= time()) {
                $result = unlink($a_file['with_path']);
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
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
        $a_return_this = [];
        foreach($a_keys as $key) {
            $a_return_this[$key] = $this->get($key, $default);
        }
        return $a_return_this;
    }

    /**
     * Fetches multiple cache items by their prefix.
     * Prefix the part of the total multipart key.
     * e.g. 'the.multipart.key' results in 'the.multipart' being the prefix.
     *
     * @param string $prefix Required.
     * @param mixed $default Optional, defaults to null
     * @return array         An array of the values obtained.
     */
    public function getMultipleByPrefix(string $prefix, string $default = null): array
    {
        $a_return_this = [];
        try {
            $a_files = CacheHelper::fileInfoByPrefix($prefix);
            foreach ($a_files as $a_file) {
                if ($a_file['expires'] < time()) {
                    unlink($a_file['with_path']);
                }
                elseif ($a_file['file_ext'] === $this->file_ext) {
                    $key = $a_file['key'];
                    $a_return_this[$key] = $this->get($key, $default);
                }
            }
        }
        catch (CacheException) {
            return [];
        }
        return $a_return_this;
    }

    /**
     * Saves multiple cache items.
     *
     * @param array  $a_value_pairs Key=>value pairs to be saved [$key => $value, $key => $value]
     * @param int    $ttl           Optional, defaults to 0=no expiration
     * @return bool                 True only if all value pairs are set true.
     * @throws CacheException
     */
    public function setMultiple(array $a_value_pairs, mixed $ttl = null): bool
    {
        foreach ($a_value_pairs as $key => $value) {
            try {
                $result = $this->set($key, $value, $ttl);
            }
            catch (CacheException $e) {
                throw new CacheException($e->getMessage(), $e->getCode(), $e);
            }
            if (!$result) {
                throw new CacheException(
                    "Could not set one of the key/value pairs",
                    ExceptionHelper::getCodeNumberCache('create')
                );
            }
        }
        return true;
    }

    /**
     * @param string $key
     * @param int    $ttl
     * @return string
     * @throws CacheException
     */
    public function createFilePath(string $key, int $ttl): string
    {
        $key = strtolower($key);
        try {
            $file_path = CacheHelper::createPathFromKey($key);
        }
        catch (CacheException $e) {
            throw new CacheException(
                $e->getMessage(),
                $e->getCode(),
                $e);
        }
        $file_parts = CacheHelper::fetchKeyParts($key);
        $filename = $file_parts['file_start'];
        $ttl = $ttl > 0 ? $ttl : $this->default_ttl;
        $expires = time() + $ttl;
        return $file_path . "/{$filename}.{$expires}.{$this->file_ext}";
    }

    /**
     * Deletes multiple cache files by an array of key.
     *
     * @param array $a_keys List of cache keys to delete
     * @return bool
     * @throws  CacheException
     */
    public function deleteMultiple(array $a_keys): bool
    {
        foreach ($a_keys as $key) {
            try {
                $a_files = CacheHelper::fileInfoByKey($key);
            }
            catch (CacheException $e) {
                throw new CacheException($e->getMessage(), $e->getCode());
            }
            foreach ($a_files as $a_file) {
                if ($a_file['file_ext'] === $this->file_ext) {
                    $result = unlink($a_file['with_path']);
                    if (!$result) {
                        return false;
                    }
                }
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
            $a_files = CacheHelper::fileInfoByKey($key);
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode());
        }
        foreach ($a_files as $a_file) {
            if ($a_file['file_ext'] === $this->file_ext) {
                return file_exists($a_file['with_path']);
            }
        }
        return false;
    }

    /**
     * Deletes all files that match the key and optional file extension.
     *
     * @param string $key Required
     * @param string $ext Optional, if not provided all matching files will be deleted.
     * @return bool
     * @throws CacheException
     */
    protected function deleteFiles(string $key, string $ext): bool
    {
        if (empty($key)) {
            throw new CacheException('Missing key name', ExceptionHelper::getCodeNumberCache('missing_value'));
        }
        if (empty($ext)) {
            $a_paths = CacheHelper::fetchFilePathsFromKey($key);
        }
        else {
            $a_files = CacheHelper::fileInfoByKey($key);
            $a_paths = [];
            foreach($a_files as $a_file) {
                if ($a_file['file_ext'] === $ext) {
                    $a_paths[] = $a_file['with_path'];
                }
            }
        }
        if (count($a_paths) > 0) {
            foreach ($a_paths as $path) {
                $result = unlink($path);
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param array $a_cache_config
     * @return void
     * @throws CacheException
     */
    protected function setupCache(array $a_cache_config): void
    {
        $this->default_ttl = $a_cache_config['ttl']
                             ?? defined(CACHE_TTL)
                                 ? CACHE_TTL
                                 : 604800;
        $cache_path = $a_cache_config['cache_path']
                      ?? defined(CACHE_PATH)
                          ? CACHE_PATH
                          : BASE_PATH . '/config';
        $cache_type = $a_cache_config['cache_type'] ?? defined(CACHE_TYPE) ? CACHE_TYPE : 'Db';
        if (!file_exists($cache_path) && !mkdir($cache_path, 0755, true) && !is_dir($cache_path)) {
            throw new CacheException(
                sprintf('Directory "%s" was not created', $cache_path),
                ExceptionHelper::getCodeNumberCache('operation')
            );
        }
        $this->cache_path = $cache_path;
        $this->file_ext   = $a_cache_config['file_ext'] ?? 'txt';
        $this->cache_type = $cache_type;
    }

    /**
     * @return string
     */
    public function getCachePath(): string
    {
        return $this->cache_path;
    }

    /**
     * @return string
     */
    public function getCacheType(): string
    {
        return $this->cache_type;
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