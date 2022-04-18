<?php
namespace Ritc\Library\Services;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Helper\CacheHelper;
use Ritc\Library\Interfaces\CacheInterface;
use Ritc\Library\Traits\CacheTraits;

/**
 * Class CacheByFilePhp
 * Returns a string saved in the file
 */
class CacheByFilePhp implements CacheInterface
{
    use CacheTraits;

    protected string $file_starting_text;

    /**
     * Contructor for class.
     *
     * @param array $a_cache_config
     * @throws CacheException
     */
    public function __construct(array $a_cache_config)
    {
        try {
            $this->setupCache($a_cache_config);
            $this->cleanExpiredFiles($this->cache_path);
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
        $this->file_starting_text =<<<STARTTXT
 <?php
return 
STARTTXT;
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
            $file_w_path = CacheHelper::fetchByKeyNewestPath($key, $this->file_ext);
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode());
        }
        return include $file_w_path ?? $default;
    }

    /**
     * Saves data in cache, uniquely reference by a key with an optional tag.
     *
     * @param string $key   Required, The key of the item to store
     * @param string $value Optional, default to '' which is a value in itself.
     * @param int    $ttl   Optional, default to 0=no expiration
     * @return bool         True on success, false elsewise.
     * @throws CacheException
     */
    public function set(string $key, string $value, int $ttl = 0): bool
    {
        try {
            if (!$this->delete($key)) {
                return false;
            }
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
        $file_w_path = $this->createFilePath($key, $ttl);
        $value = trim($this->file_starting_text) . " '" . $value . "';";
        return (bool)file_put_contents($file_w_path, $value);
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
        try {
            return $this->deleteFiles($key, $this->file_ext);
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }
}