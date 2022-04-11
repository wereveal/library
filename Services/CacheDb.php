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
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): ?string
    {
        try {
            $value = $this->o_cache_model->readByName($key);
            if (!empty($value[0]['cache_ttl'])) {
                if ($value[0]['cache_ttl'] + $this->default_ttl > time()) {
                    $this->delete($key);
                    return $default;
                }
            }
            return !empty($value[0]['cache_value']) ? $value[0]['cache_value'] : $default;
        }
        catch (ModelException $e) {
            $error_code = ExceptionHelper::getCodeNumberCache('read');
            throw new CacheException($e->getMessage(), $error_code, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, string $value, mixed $ttl = null): bool
    {
        if (!empty($key)) {
            try {
                $a_values = [
                    'cache_name'  => $key,
                    'cache_value' => $value,
                    'cache_ttl'   => time()
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
     * @inheritDoc
     */
    public function clear(): bool
    {
        // TODO: Implement clear() method.
        // delete all cache_ct_map records
        // delete all cache records
        // delete all tag records.
        return false;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getMultiple(array $a_keys, mixed $default = null): array
    {
        // TODO: Implement getMultiple() method.
        return [];
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($a_value_pairs, mixed $ttl = null): bool
    {
        // TODO: Implement setMultiple() method.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(array $a_keys): bool
    {
        // TODO: Implement deleteMultiple() method.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        try {
            $value = $this->o_cache_model->readByName($key);
            return !empty($value[0]);
        }
        catch (ModelException) {
            return false;
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