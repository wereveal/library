<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Services\CacheByFile;
use Ritc\Library\Traits\CacheTesterTraits;
use Ritc\Library\Traits\TesterTraits;

class CacheByFileTester
{
    use TesterTraits;
    use CacheTesterTraits;

    private CacheByFile $o_cbf;

    /**
     *
     */
    public function __construct()
    {
        try {
            $this->o_cbf = new CacheByFile([]);
        }
        catch (CacheException) {
            return;
        }
        $a_test_params = [
            'namespace'     => 'Ritc\Library\Services',
            'class_name'    => 'CacheByFile',
            'instance_name' => 'o_cache',
            'passed_subs'   => true
        ];
        $this->setupTests($a_test_params);
    }

    ### Tests Specific to Class
    /**
     * @return string
     */
    public function getTest():string
    {
        foreach($this->a_test_values['get'] as $test_name => $test_values) {
            try {
                $the_result = $test_values['expected_results'] ===
                               $this->o_cbf->get($test_values['key'], $test_values['default']);
            }
            catch (CacheException) {
                $the_result = 'failed';
            }
            // passed_subs for $test_name = $the_result
        }
        return 'failed';
    }

    public function setTest():string
    {
        return 'failed';
    }

    public function deleteTest():string
    {
        return 'failed';
    }

}