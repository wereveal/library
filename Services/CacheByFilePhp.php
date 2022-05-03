<?php
namespace Ritc\Library\Services;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Helper\CacheHelper;

/**
 * Class CacheByFilePhp
 * Returns a string saved in the file
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-beta.1
 * @date    2022-03-14 14:14:58
 * @change_log
 * - v1.0.0-beta.1 - initial version                            - 2022-03-14 wer
 */
class CacheByFilePhp extends CacheByFile
{
    protected string $file_starting_text;

    /**
     * Constructor for class.
     *
     * @param array $a_cache_config
     * @throws CacheException
     */
    public function __construct(array $a_cache_config)
    {
        parent::__construct($a_cache_config);
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
     * @return bool         True on success, false otherwise.
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

}