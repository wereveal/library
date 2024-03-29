<?php
/**
 * Class Tester
 * @package Ritc_Library
 */
namespace Ritc\Library\Basic;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Ritc\Library\Helper\LocateFile;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class provides a base class of which one extends specific testers for specific classes.
 * Class that extends this class should end with the word Tests or Tester,
 * e.g. MyClassTester or MyClassTests.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v4.0.0
 * @date    2021-11-26 14:01:47
 * @change_log
 * - v4.0.0 - Updated for php 8                                                         - 2021-11-26 wer
 * - v3.7.0 - Updated to handle ReflectionException                                     - 2018-05-19 wer
 * - v3.6.0 - Refactoring of subtest methods                                            - 2017-06-09 wer
 * - v3.5.0 - modified setupTests to create test order from test values                 - 2017-05-12 wer
 * - v3.4.0 - modified setupTests to set a new property class_name                      - 2016-04-20 wer
 * - v3.3.0 - added new method to automate some setup                                   - 2016-03-10 wer
 * - v3.2.0 - moved the compare_arrays to the Arrays helper class                       - 11/02/2015 wer
 * - v3.1.0 - no longer extends Base class, uses Logit Trait instead                    - 08/19/2015 wer
 * - v3.0.0 - changed to be a class so it could extend Base class and modified for such - 09/24/2014 wer
 * - v2.1.0 - added missing method                                                      - 07/01/2014 wer
 * - v2.0.0 - modified to not do any view stuff                                         - 2013-12-13 wer
 * - v1.1.0 - added new a couple new methods                                            - 2013-05-10 wer
 *    - compare_arrays
 *      - checks to see if the values in the first array
 *      - exist in the second array
 *    - setSubFailed
 *      - allows the test results to display individual subtests
 *      - within the method tester
 * - v1.0.0 - updated to match new framework                                            - 2013-04-03 wer
 * @todo add a todo test type, i.e. a way to say, this method needs to be written so don't run a test, just list it.
 */
class Tester
{
    use LogitTraits;

    /** @var array the test order */
    protected array $a_test_order = [];
    /** @var array the test values */
    protected array $a_test_values = [];
    /** @var string name of the class being tested */
    protected string $class_name = '';
    /** @var array list of failed sub-tests */
    protected array $failed_subtests = [];
    /** @var array list of failed tests */
    protected array $failed_test_names = [];
    /** @var int number of failed tests */
    protected int $failed_tests = 0;
    /** @var string namespace being tested */
    protected string $namespace = '';
    /** @var int number of tests */
    protected int $num_o_tests = 0;
    /** @var array list of passed sub-tests */
    protected array $passed_subtests = [];
    /** @var array list of passed tests */
    protected array $passed_test_names = [];
    /** @var int number of passed tests */
    protected int $passed_tests = 0;
    /** @var array list of skipped tests */
    protected array $skipped_test_names = [];
    /** @var int number of skipped tests */
    protected int $skipped_tests = 0;

    /**
     * Adds a method name to the test order.
     *
     * @param string $method_name
     * @return bool
     */
    public function addMethodToTestOrder(string $method_name = ''):bool
    {
        if ($method_name === '') { return false; }
        $this->a_test_order[] = $method_name;
        return true;
    }

    /**
     * Adds a single key=>value pair to the a_test_values array
     *
     * @param string $key   the key name
     * @param mixed  $value the value assigned to the key
     */
    public function addTestValue(string $key = '', mixed $value = ''):void
    {
        if ($key === '') { return; }
        $this->a_test_values[$key] = $value;
    }

    /**
     * @return array
     */
    public function getFailedTestNames():array
    {
        return $this->failed_test_names;
    }

    /**
     * @return int
     */
    public function getFailedTests():int
    {
        return $this->failed_tests;
    }

    /**
     * @return int
     */
    public function getNumOTests():int
    {
        return $this->num_o_tests;
    }

    /**
     * @return int
     */
    public function getPassedTests():int
    {
        return $this->passed_tests;
    }

    /**
     * @return array
     */
    public function getPassedTestNames():array
    {
        return $this->passed_test_names;
    }

    /**
     * @return array
     */
    public function getTestOrder():array
    {
        return $this->a_test_order;
    }

    /**
     * Returns an array showing the number and optionally names of tests success and failure
     *
     * @param bool $show_test_names optional defaults to showing names
     * @return array
     */
    public function returnTestResults(bool $show_test_names = true):array
    {
        $a_failed_test_names = array();
        $a_passed_test_names = array();
        if ($show_test_names === true) {
            if (count($this->passed_test_names) > 0) {
                $a_passed_test_names = $this->passed_test_names;
            }
            foreach ($this->failed_test_names as $name) {
                if (is_array($this->failed_subtests) && isset($this->failed_subtests[$name])) {
                    $a_subnames = $this->failed_subtests[$name];
                }
                else {
                    $a_subnames = array();
                }
                $a_failed_test_names[] = array(
                    'name' => $name,
                    'subtest_names' => $a_subnames
                );
            }
            return array(
                'failed_tests'      => $this->failed_tests,
                'passed_tests'      => $this->passed_tests,
                'num_o_tests'       => $this->num_o_tests,
                'failed_test_names' => $a_failed_test_names,
                'passed_test_names' => $a_passed_test_names
            );
        }

        return array(
            'failed_tests'      => $this->failed_tests,
            'passed_tests'      => $this->passed_tests,
            'num_o_tests'       => $this->num_o_tests,
            'failed_test_names' => '',
            'passed_test_names' => ''
        );
    }

    /**
     * Runs tests where method ends in Test.
     *
     * @param string $class_name   optional, name of the class to be tested - only really needed if
     *                            the class name doesn't match this class name minus Tester or Tests
     *                            e.g. MyClass and MyClassTester doesn't require $class_name
     *                            but MyClass and ThisClassTest requires a valid value for $class_name,
     *                            i.e., $class_name = MyClass
     * @param array  $a_test_order optional, if provided it ignores the class property $a_test_order
     *                            and won't try to build one from the class methods.
     * @return int number of failed tests.
     * @throws ReflectionException
     */
    public function runTests(string $class_name = '', array $a_test_order = []):int
    {
        if ($class_name === '') {
            if ($this->class_name !== '') {
                $class_name = $this->class_name;
            }
            elseif (str_ends_with(__CLASS__, 'Tests')) {
                $class_name = str_replace('Tests','',__CLASS__);
            }
            elseif (str_ends_with(__CLASS__, 'Tester')) {
                $class_name = str_replace('Tester','',__CLASS__);
                $this->class_name = $class_name;
            }
            else {
                return 999;
            }
        }
        if (count($a_test_order) === 0) {
            if (count($this->a_test_order) === 0) {
                try {
                    $o_ref = new ReflectionClass($class_name);
                }
                catch (ReflectionException) {
                    return 999;
                }
                $a_methods = $o_ref->getMethods(ReflectionMethod::IS_PUBLIC);
                foreach ($a_methods as $a_method) {
                    switch($a_method->name) {
                        case '__construct':
                        case '__set':
                        case '__get':
                        case '__isset':
                        case '__unset':
                        case '__clone':
                            break;
                        default:
                            if (str_ends_with($a_method->name, 'Tester')) {
                                $a_test_order[] = $a_method->name;
                            }
                    }
                }
            }
            else {
                $a_test_order = $this->a_test_order;
            }
        }
        $this->logIt(var_export($a_test_order, true), LOG_OFF, __METHOD__);
        $message = "Before -- num_o_tests: {$this->num_o_tests}
            passed tests: {$this->passed_tests}
            failed tests: {$this->failed_tests}
            test names: " . var_export($this->failed_test_names, true);

        $this->logIt($message, LOG_OFF, __METHOD__);
        $failed_tests = 0;
        foreach ($a_test_order as $method_name) {
            $this->logIt($method_name, LOG_OFF, __METHOD__ . '.' . __LINE__);
            if (str_ends_with($method_name, 'Tester')) {
                $tester_name = $method_name;
                $method_name = $this->shortenName($method_name);
            } else {
                $tester_name = $method_name . 'Tester';
            }
            $this->logIt("method name: {$method_name} - tester name: {$tester_name}", LOG_OFF, __METHOD__ . '.' . __LINE__);
            if ($this->isPublicMethod($class_name, $tester_name)) {
                $results = $this->$tester_name();
                switch ($results) {
                    case true:
                    case 'passed':
                        $this->passed_tests++;
                        $this->passed_test_names[] = $method_name;
                        break;
                    case 'skipped':
                        $this->skipped_tests++;
                        $this->skipped_test_names[] = $method_name;
                        break;
                    case false:
                    case 'failed':
                    default:
                        $failed_tests++;
                        $this->failed_tests++;
                        $this->failed_test_names[] = $method_name;
                        break;
                }
                $this->num_o_tests++;
            }
        }
        $this->logIt("num_o_tests: {$this->num_o_tests} passed tests: {$this->passed_tests} failed tests: {$this->failed_tests} test names: " . var_export($this->failed_test_names, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $failed_tests;
    }

    /**
     * Removes Tester or Test from method name
     *
     * @param string $method_name defaults to 'Tester'
     * @return string
     */
    public function shortenName(string $method_name = 'Tester'):string
    {
        if (str_ends_with($method_name, 'Tester')) {
            return substr($method_name, 0, -6);
        }
        return $method_name;
    }

    /**
     * Standard Setter for the property $class_name;
     *
     * @param string $class_name
     */
    public function setClassName(string $class_name = ''):void
    {
        $this->class_name = $class_name;
    }

    /**
     * Sets three properties, num_o_test++, failed_tests++, and failed test names.
     *
     * @param string $method_name
     */
    public function setFailures(string $method_name = ''):void
    {
        $this->num_o_tests++;
        $this->failed_tests++;
        $this->failed_test_names[] = $this->shortenName($method_name);
    }

    /**
     * Standard setter for the propery $namespace.
     *
     * @param string $namespace
     */
    public function setNamespace(string $namespace = ''):void
    {
        $this->namespace = $namespace;
    }

    /**
     * Sets failed_subtests
     *
     * @param string $method_name
     * @param string $test_name
     */
    public function setSubFailed(string $method_name, string $test_name):void
    {
        if (!empty($method_name) && !empty($test_name)) {
            $method_name = $this->shortenName($method_name);
            if (is_array($this->failed_subtests) === false) {
                $this->failed_subtests = [];
            }
            if (array_key_exists($method_name, $this->failed_subtests)) {
                $this->failed_subtests[$method_name][] = $test_name;
            }
            else {
                $this->failed_subtests[$method_name] = array($test_name);
            }
        }
    }

    /**
     * Records the names of the subtests passed for a test.
     *
     * @param string $method_name
     * @param string $test_name
     */
    public function setSubPassed(string $method_name = '', string $test_name = ''):void
    {
        if ($method_name === '' || $test_name === '') {
            return;
        }
        $method_name = $this->shortenName($method_name);
        if (array_key_exists($method_name, $this->passed_subtests)) {
            $this->passed_subtests[$method_name][] = $test_name;
        }
        else {
            $this->passed_subtests[$method_name] = array($test_name);
        }
    }

    /**
     * Sets the array a_test_order to the array passed in
     * @param array $a_test_order optional, defaults to an empty array
     */
    public function setTestOrder(array $a_test_order = array()):void
    {
        $this->a_test_order = $a_test_order;
    }

    /**
     * Sets the array a_test_value to the array passed in
     * @param array $a_test_values optional, defaults to an empty array
     */
    public function setTestValues(array $a_test_values = array()):void
    {
        $this->a_test_values = $a_test_values;
    }

    /**
     * Return the values in $this->a_test_values
     * @return array $a_test_values
     */
    public function getTestValues():array
    {
        return $this->a_test_values;
    }

    ### Utility Methods ###

    /**
     * Checks to see if a method is public.
     * Fixes method names that end in Tester.
     *
     * @param string $class_name  required defaults to ''
     * @param string $method_name required defaults to ''
     * @return bool true or false
     * @throws ReflectionException
     */
    public function isPublicMethod(string $class_name = '', string $method_name = ''):bool
    {
        if ($method_name === '') {
            return false;
        }
        if ($class_name === '' && $this->class_name === '') {
            return false;
        }

        if ($class_name === '') {
            $class_name = $this->class_name;
        }
        try {
            $o_ref = new ReflectionClass($class_name);
        }
        catch (ReflectionException) {
            return false;
        }
        $o_method = $o_ref->getMethod($method_name);
        return $o_method->isPublic();
    }

    /**
     * Sets up the two main arrays the tests uses.
     * @param array $a_values ['class_name', 'order_file', 'values_file', 'extra_dir', 'namespace']
     */
    public function setupTests(array $a_values = []):void
    {
        $class_name  = '';
        $order_file  = 'test_order.php';
        $values_file = 'test_values.php';
        $extra_dir   = '';
        $namespace   = '';

        $a_expected_keys = [
            'class_name',
            'order_file',
            'values_file',
            'extra_dir',
            'namespace'
        ];
        foreach ($a_expected_keys as $keyname) {
            if (isset($a_values[$keyname]) && $a_values[$keyname] !== '') {
                $$keyname = $a_values[$keyname];
                if ($keyname === 'namespace') {
                    $this->namespace = $a_values[$keyname];
                }
            }
        }
        $this->class_name = $class_name;

        $test_values_file = LocateFile::getTestFileWithPath($values_file, $namespace, $extra_dir);
        if (empty($test_values_file)) {
           $this->a_test_values = [];
           $a_test_values = [];
        }
        else {
            $this->a_test_values = include $test_values_file;
            $a_test_values = $this->a_test_values;
        }
        $test_order_file = LocateFile::getTestFileWithPath($order_file, $namespace, $extra_dir);
        if (!empty($test_order_file)) {
            $this->a_test_order = include $test_order_file;
        }
        elseif (!empty($a_test_values)) {
            $a_test_order = [];
            foreach ($a_test_values as $key => $test_values) {
                $a_test_order[] = $key;
            }
            $this->a_test_order = $a_test_order;
        }
        else {
            $this->a_test_order = [];
        }
    }
}
