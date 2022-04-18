<?php
namespace Ritc\Library\Services;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Helper\CacheHelper;

/**
 * Class CacheByFileJson
 *
 * Saves a json encoded string for key=>value in a file.
 * Extends CacheByFile, changing only the get and set methods with are unique to
 * json encoding/decoding.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-beta.1
 * @date    2022-03-14 14:14:58
 * @change_log
 * - v1.0.0-beta.1 - initial version                            - 2022-03-14 wer
 */
class CacheByFileJson extends CacheByFile
{
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
        $a_value = json_decode(file_get_contents($file_w_path), true);
        if (empty($a_value[$key])) {
            return $default;
        }
        return $$a_value[$key];
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
        try {
            if (!$this->delete($key)) {
                return false;
            }
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode());
        }
        $file = $this->createFilePath($key, $ttl);
        $value = json_encode([$key => $value]);
        return (bool)file_put_contents($file, $value);
    }
}