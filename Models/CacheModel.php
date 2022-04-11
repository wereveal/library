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
        $this->setRequiredKeys(['cache_name']);
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
        if (empty($a_values['cache_name'])) {
            throw new CacheException('Missing cache name', ExceptionHelper::getCodeNumberCache('missing_key'));
        }
        try {
            $a_results = $this->readByName($a_values['cache_name']);
            if (empty($a_results)) {
                $a_results = $this->create($a_values);
                return !empty($a_results);
            }
            else {
                $a_update_values = [
                    'cache_id' => $a_results['cache_id'],
                    'cache_value' => $a_values['cache_value']
                ];
                return $this->update($a_update_values);
            }
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
     * @param string $cache_name
     * @return array
     * @throws ModelException
     */
    public function readByName(string $cache_name):array
    {
        $a_search_for = ['cache_name' => $cache_name];
        try {
            $a_results = $this->read($a_search_for);
            return $a_results[0] ?? [];
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}