<?php
namespace Ritc\Library\Services;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Interfaces\CacheInterface;

/**
 * Class CacheApcu.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2022-03-20 17:44:30
 * @change_log
 * - v1.0.0-alpha.0 - Initial version.                          - 2022-03-20 wer
 */
class CacheApcu implements CacheInterface
{
    private int $ttl;

    public function __construct(array $a_config)
    {
        $this->ttl = $a_config['ttl'];
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default): ?string
    {
        if (empty($key)) {
            throw new CacheException(
                'Key missing',
                ExceptionHelper::getCodeNumberCache('missing_value')
            );
        }
        $result = apcu_fetch($key);
        return !$result ? null : $result;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, string $value = '', int $ttl = 0): bool
    {
        if (empty($key)) {
            throw new CacheException(
                'Missing key name',
                ExceptionHelper::getCodeNumberCache('missing_value')
            );
        }
        if ($ttl === 0) {
            $ttl = $this->ttl;
        }
        return apcu_store($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        if (empty($key)) {
            throw new CacheException(
                'Missing key name',
                ExceptionHelper::getCodeNumberCache('missing_value')
            );
        }
        return apcu_delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    /**
     * @inheritDoc
     */
    public function clearByKeyPrefix(string $prefix): bool
    {
        $a_keys = $this->getKeysFromPrefix($prefix);
        foreach ($a_keys as $key) {
            $results = apcu_delete($key);
            if (!$results) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple(array $a_keys, mixed $default = null): array
    {
        if (empty($a_keys)) {
            throw new CacheException('Missing keys', ExceptionHelper::getCodeNumberCache('missing_value'));
        }
        return apcu_fetch($a_keys);
    }

    /**
     * @inheritDoc
     */
    public function getMultipleByPrefix(string $prefix, string $default = null): array
    {
        if (empty($prefix)) {
            return [];
        }
        $a_keys = $this->getKeysFromPrefix($prefix);
        return apcu_fetch($a_keys);
    }

    /**
     * @inheritDoc
     */
    public function setMultiple(array $a_value_pairs, int $ttl = 0): bool
    {
        if ($ttl <= 0) {
            $ttl = $this->ttl;
        }
        foreach ($a_value_pairs as $a_value_pair) {
            try {
                $result = $this->set($a_value_pair['key'], $a_value_pair['value'], $ttl);
                if (!$result) {
                    return false;
                }
            }
            catch (CacheException $e) {
                throw new CacheException($e->getMessage(), $e->getCode());
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(array $a_keys): bool
    {
        if (empty($a_keys)) {
            throw new CacheException(
                'Missing keys',
                ExceptionHelper::getCodeNumberCache('missing_value')
            );
        }
        foreach ($a_keys as $key) {
            $results = $this->delete($key);
            if (!$results) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return apcu_exists($key);
    }

    /**
     * Figures out the keys in the APCu cache that match the prefix (partial key).
     *
     * @param string $prefix
     * @return array
     */
    private function getKeysFromPrefix(string $prefix): array
    {
        $a_info = apcu_cache_info();
        $a_keys = [];
        foreach ($a_info['cache_list'] as $a_cache_item) {
            if (str_starts_with($a_cache_item['info'],$prefix)) {
                $a_keys[] = $a_cache_item['info'];
            }
        }
        return $a_keys;
    }
}