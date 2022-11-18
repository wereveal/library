<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Services\CacheDb;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\TesterTraits;

class CacheDbTester
{
    use TesterTraits;

    private CacheDb $o_cbf;

    /**
     *
     */
    public function __construct(Di $o_di, array $a_cache_config)
    {
        $this->o_cbf = new CacheDb($o_di, $a_cache_config);
        $a_test_params = [
            'namespace'     => 'Ritc\Library\Services',
            'class_name'    => 'CacheByFile',
            'instance_name' => 'o_cache'
        ];
        $this->setupTests($a_test_params);
    }

    /**
     * @return string
     * @throws CacheException
     */
    public function getTester():string
    {
        try {
            $stuff = $this->o_cbf->get();
        }
        catch (CacheException) {
            return '';
        }
        return '';
    }
}