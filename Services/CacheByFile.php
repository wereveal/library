<?php
// TODO: Create this service
/**
 * Basically, will have to figure out a basic file cache.
 * This class will be extended by CacheByFilePhp, CacheByFilePhpArray and CacheByFileJson
 * Organize it by "tags", i.e., dirs in the CACHE_DIR
 * By interface, the key name is a single string, tags are not defined.
 * We will redefined the cache name to include a dot system.
 * The cache name will {tag}.{subtag}.cache_name where stuff in {} are optional
 * If no tag is given, will be assigned to the noTag tag.
 * Dir organization will be tag.subtag with the individule file in the dir.
 * Filename = {cache_name}.{file_type} where file_type could be txt, json, php
 * File contents = single line cache value: plain text, json string, php array
 */
namespace Ritc\Library\Services;

use Ritc\Library\Interfaces\CacheInterface;

class CacheByFile implements CacheInterface
{

    /**
     * @inheritDoc
     */
    public function get(string $key, string $default = null): ?string
    {
        // TODO: Implement get() method.
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, string $value, mixed $ttl = null): bool
    {
        // TODO: Implement set() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        // TODO: Implement clear() method.
    }

    /**
     * @inheritDoc
     */
    public function getMultiple(array $a_keys, mixed $default = null): arary
    {
        // TODO: Implement getMultiple() method.
    }

    /**
     * @inheritDoc
     */
    public function setMultiple(array $a_value_pairs, mixed $ttl = null): bool
    {
        // TODO: Implement setMultiple() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(array $a_keys): bool
    {
        // TODO: Implement deleteMultiple() method.
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        // TODO: Implement has() method.
    }
}