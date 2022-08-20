<?php
/**
 * Class ExampleTester
 * Pure example of how to set up a test class.
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Services\Di;
use Ritc\Library\Traits\TesterTraits;

class ExampleTester
{
    use TesterTraits;

    public function __construct(Di $o_di)
    {
        /* NOTE:
         * This is an example of one way to do it.
         * The other way would be to do something like
         * $o_test = new ExampleTester($o_di)
         * $o_test->setupTests(['namespace' => '', 'class_name' => '', 'instance_name' => '']);
         * The question is, where do you want the parameters.
         * In my mind, since they are specific to a test, no reason not to put them here.
         */
        $a_test_params = [
            'namespace'     => 'Proper\Namespace\To\Class\Tested',
            'class_name'    => 'ClassName',
            'instance_name' => 'o_thing'
        ];
        $this->setupTests($a_test_params);
    }
    /**
     * Important Methods in the Traits
     *
     * public function runTests(bool $return_results = true):array
     * public function setupTests(array $a_values = []):void
     * private function genericTest(array $a_names = [], array $a_test_values = []):string
     * public function genericSubtest(array $a_names = [], array $a_values = []):bool
     * private function genericDbTest(array $a_names = [], array $a_test_values = []):string
     * public function genericDbSubTest(array $a_names = [], array $a_values = []):bool
     * public function genericSingleTest(array $a_names = [], array $a_test_values = []):string
     * public function genericDbSingleTest(array $a_names = [], array $a_test_values = []):string
     * public function returnTestResults(bool $show_test_names = true):array
     * public function isPublicMethod(string $method_name = ''):bool
     * public function addMethodToTestOrder(string $method_name = ''):bool
     * public function addTestValue(string $key = '', null|string $value = ''):void
     *
     */
}