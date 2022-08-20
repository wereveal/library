<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Services\CacheByFile;
use Ritc\Library\Traits\TesterTraits;

class CacheByFileTester
{
    use TesterTraits;

    private CacheByFile $o_cbf;

    public function __construct()
    {
        try {
            $this->o_cbf = new CacheByFile([]);
        }
        catch (CacheException $e) {
            return;
        }
        $a_test_params = [
            'namespace'     => 'Ritc\Library\Services',
            'class_name'    => 'CacheByFile',
            'instance_name' => 'o_cache'
        ];
        $this->setupTests($a_test_params);
    }

    public function getTester():string
    {

    }
}