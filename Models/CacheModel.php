<?php
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

class CacheModel extends ModelAbstract
{
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'cache');
        $this->setRequiredKeys(['cache_key']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    /**
     * Updates an existing cache_key record with new values or
     * creates a new key=>value cache record.
     *
     * @param array $a_values Required
     * @return bool
     * @throws CacheException
     */
    public function updateOrCreate(array $a_values): bool
    {
        if (empty($a_values['cache_key'])) {
            throw new CacheException('Missing cache name', ExceptionHelper::getCodeNumberCache('missing_key'));
        }
        try {
            $a_results = $this->readByKey($a_values['cache_key']);
            if (empty($a_results)) {
                $a_results = $this->create($a_values);
                return !empty($a_results);
            }
            $a_update_values = [
                'cache_id' => $a_results['cache_id'],
                'cache_value' => $a_values['cache_value']
            ];
            return $this->update($a_update_values);
        }
        catch (ModelException) {
            try {
                $a_results = $this->create($a_values);
                return !empty($a_results);
            }
            catch (ModelException $e) {
                throw new CacheException('Could not update or create new cache record',
                                         ExceptionHelper::getCodeNumberCache('database'), $e);
            }
        }
    }

    /**
     * Returns the value of the cache.
     *
     * @param string $cache_key
     * @return array
     * @throws ModelException
     */
    public function readByKey(string $cache_key):array
    {
        $a_search_for = ['cache_key' => $cache_key];
        $a_search_params = [
            'comparison_type' => 'LIKE'
        ];
        try {
            $a_results = $this->read($a_search_for, $a_search_params);
            return $a_results[0] ?? [];
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns an array of cache values for each key provided in the array.
     *
     * @param array $a_keys
     * @return array
     * @throws ModelException
     */
    public function readByKeys(array $a_keys): array
    {
        $a_search_for = [];
        foreach ($a_keys as $key) {
            $a_search_for[] = ['cache_key' => $key];
        }
        $a_search_params = [
            'comparison_type' => 'LIKE'
        ];
        try {
            return $this->read($a_search_for, $a_search_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns an array of caches.
     *
     * @param string $prefix
     * @return array
     * @throws ModelException
     */
    public function readByKeyPartial(string $prefix): array
    {
        $a_search_for = ['cache_key' => $prefix . '%'];
        $a_search_params = [
            'comparison_type' => 'LIKE'
        ];
        try {
            $a_results = $this->read($a_search_for, $a_search_params);
            return $a_results ?? [];
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }

    }

    /**
     * @return int|bool
     * @throws CacheException
     */
    public function clearCache(): int|bool
    {
        try {
            return $this->o_db->rawExec('TRUNCATE TABLE ' . $this->db_table);
        }
        catch (ModelException $e) {
            throw new CacheException('Could not clear cache.', ExceptionHelper::getCodeNumberCache('operation'), $e);
        }
    }

    /**
     * Deletes a single cache record by the cache_key.
     * Since cache_keys are unique, the $key will be an exact match.
     *
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function deleteByKey(string $key): bool
    {
        $sql = "DELETE FROM $this->db_table WHERE cache_key LIKE :cache_key";
        $a_value = ['cache_key' => $key];
        try {
            return $this->o_db->delete($sql, $a_value);
        }
        catch (ModelException $e) {
            throw new CacheException('Could not delete the cache by key', ExceptionHelper::getCodeNumberCache('database'), $e);
        }
    }

    /**
     * Deletes (a) cache record(s) by a partial cache_key.
     * Partial key is always the start of the key, e.g., full key is
     * my.special.key partial keys will normally be like my.special although
     * the way this works, technically, my.spe will match all records with my.spe
     * e.g., my.special.key and my.species.key would both be deleted.
     * a cache_key smy.special.key would not be matched or deleted.
     *
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function deleteByKeyPartial(string $key): bool
    {
        $sql = "DELETE FROM $this->db_table WHERE cache_key LIKE :cache_key";
        $a_value = ['cache_key' => $key . '%'];
        try {
            return $this->o_db->delete($sql, $a_value);
        }
        catch (ModelException $e) {
            throw new CacheException('Could not delete the cache by key', ExceptionHelper::getCodeNumberCache('database'), $e);
        }
    }
}