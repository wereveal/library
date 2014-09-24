<?php
/**
 *  @brief A class for testing that all other testing classes should extend.
 *  @details Class that extends this class should end with the word Tests or
 *           Tester, e.g. MyClassTester or MyClassTests.
 *  @file Tester.php
 *  @namespace Ritc/Library/Core
 *  @class Tester
 *  @author William E Reveal  <bill@revealitconsulting.com>
 *  @version  3.0.0
 *  @date 2014-09-24 13:02:53
 *  @note <pre><b>Change log</b>
 *      v3.0.0 - changed to be a class so it could extend Base class and modified for such - 09/24/2014 wer
 *      v2.0.1 - added missing method 07/01/2014 wer
 *      v2.0.0 - modified to not do any view stuff 2013-12-13 wer
 *      v1.1.0 - added new a couple new methods  2013-05-10 wer
 *          compare_arrays
 *              checks to see if the values in the first array
 *              exist in the second array
 *          setSubfailure
 *              allows the test results to display individual subtests
 *              within the method tester
 *      v1.0.1 - updated to match new framework 2013-04-03 wer
 *  </pre>
 *  @note RITC Library v5.0.0
 *  @ingroup ritc_library abstracts
**/
namespace Ritc\Library\Core;

class Tester extends Base
{
    protected $a_test_order;
    protected $a_test_values = array();
    protected $failed_subtests;
    protected $failed_test_names = array();
    protected $failed_tests;
    protected $num_o_tests;
    protected $o_elog;
    protected $passed_subtests;
    protected $passed_test_names  = array();
    protected $passed_tests;
    protected $private_properties;

    public function __construct()
    {
        $this->setPrivateProperties();
    }
    public function addMethodToTestOrder($method_name = '')
    {
        if ($method_name == '') { return false; }
        $this->a_test_order[] = $method_name;
        return true;
    }
    /**
     *  Adds a single key=>value pair to the a_test_values array
     *  @param string $key the key name
     *  @param mixed $value  the value assigned to the key
     *  @return null
    **/
    public function addTestValue($key = '', $value = '')
    {
        if ($key == '') { return; }
        $this->a_test_values[$key] = $value;
    }
    public function getFailedTestNames()
    {
        return $this->failed_test_names;
    }
    public function getFailedTests()
    {
        return $this->failed_tests;
    }
    public function getNumOTests()
    {
        return $this->num_o_tests;
    }
    public function getPassedTests()
    {
        return $this->passed_tests;
    }
    public function getPassedTestNames()
    {
        return $this->passed_test_names;
    }
    public function getTestOrder()
    {
        return $this->a_test_order;
    }
    /**
     *  Returns an array showing the number and optionally names of tests success and failure
     *  @param bool $show_test_names optional defaults to showing names
     *  @return array
    **/
    public function returnTestResults($show_test_names = true)
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
        else {
            return array(
                'failed_tests'      => $this->failed_tests,
                'passed_tests'      => $this->passed_tests,
                'num_o_tests'       => $this->num_o_tests,
                'failed_test_names' => '',
                'passed_test_names' => ''
            );
        }
    }
    /**
     *  Runs tests where method ends in Test.
     *  @param string $class_name optional, name of the class to be tested - only really needed if
     *                            the class name doesn't match this class name minus Tester or Tests
     *                            e.g. MyClass and MyClassTester doesn't require $class_name
     *                            but MyClass and ThisClassTest requires a valid value for $class_name,
     *                            i.e., $class_name = MyClass
     *  @param array $a_test_order optional, if provided it ignores
     *      the class property $a_test_order and won't try to build one
     *      from the class methods.
     *  @return int $failed_tests
    **/
    public function runTests($class_name = '', array $a_test_order = array())
    {
        if ($class_name == '') {
            if (substr(__CLASS__, -5) == 'Tests') {
                $class_name = str_replace('Tests','',__CLASS__);
            }
            elseif (substr(__CLASS__, -6) == 'Tester') {
                $class_name = str_replace('Tester','',__CLASS__);
            }
            else {
                return 999;
            }
        }
        if (count($a_test_order) === 0) {
            if (count($this->a_test_order) === 0) {
                $o_ref = new \ReflectionClass($class_name);
                $a_methods = $o_ref->getMethods(\ReflectionMethod::IS_PUBLIC);
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
                            if (substr($a_method->name, -6) == 'Tester') {
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
        $this->logIt("Before -- num_o_tests: {$this->num_o_tests} passed tests: {$this->passed_tests} failed tests: {$this->failed_tests} test names: " . var_export($this->failed_test_names, true), LOG_OFF, __METHOD__);
        $failed_tests = 0;
        foreach ($a_test_order as $method_name) {
            $this->logIt($method_name, LOG_OFF, __METHOD__ . '.' . __LINE__);
            if (substr($method_name, -6) == 'Tester') {
                $tester_name = $method_name;
                $method_name = $this->shortenName($method_name);
            } else {
                $tester_name = $method_name . 'Tester';
            }
            $this->logIt("method name: {$method_name} - tester name: {$tester_name}", LOG_OFF, __METHOD__ . '.' . __LINE__);
            if ($this->isPublicMethod($class_name, $tester_name)) {
                if ($this->$tester_name()) {
                    $this->passed_tests++;
                    $this->passed_test_names[] = $method_name;
                } else {
                    $failed_tests++;
                    $this->failed_tests++;
                    $this->failed_test_names[] = $method_name;
                }
                $this->num_o_tests++;
            }
        }
        $this->logIt("num_o_tests: {$this->num_o_tests} passed tests: {$this->passed_tests} failed tests: {$this->failed_tests} test names: " . var_export($this->failed_test_names, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $failed_tests;
    }
    public function shortenName($method_name = 'Tester')
    {
        if (substr($method_name, -6) == 'Tester') {
            return substr($method_name, 0, -6);
        }
        return $method_name;
    }
    public function setFailures($method_name = '')
    {
        $this->num_o_tests++;
        $this->failed_tests++;
        $this->failed_test_names[] = str_replace('Tester', '', $method_name);
    }
    public function setSubfailure($method_name = '', $test_name = '')
    {
        if ($method_name == '' || $test_name == '') { return; }
        $method_name = str_replace('Tester', '', $method_name);
        if (is_array($this->failed_subtests) === false) {
            $this->failed_subtests = array();
        }
        if (array_key_exists($method_name, $this->failed_subtests)) {
            $this->failed_subtests[$method_name][] = $test_name;
        } else {
            $this->failed_subtests[$method_name] = array($test_name);
        }
    }
    public function setTestOrder($a_test_order = '')
    {
        if ($a_test_order == '') {
            $this->a_test_order = array();
        }
        if (is_array($a_test_order) === false) {
            $this->a_test_order = array();
        }
        $this->a_test_order = $a_test_order;
    }
    /**
     *  Sets the array a_test_value to the array passed in
     *  @param array $a_test_values optional, defaults to an empty array
     *  @return null
    **/
    public function setTestValues(array $a_test_values = array())
    {
        $this->a_test_values = $a_test_values;
    }
    /**
     *  Return the values in $this->a_test_values
     *  @param none
     *  @return array $a_test_values
    **/
    public function getTestValues()
    {
        return $this->a_test_values;
    }

    ### Utility Methods ###
    /**
     *  Compares two arrays and sees if the values in the second array match the first.
     *  @param array $a_good_values required
     *  @param array $a_check_values required
     *  @return bool true or false
    **/
    public function compareArrays(array $a_good_values = array(), array $a_check_values = array())
    {
        foreach ($a_good_values as $key => $value) {
            if ($a_good_values[$key] != $a_check_values[$key]) {
                return false;
            }
        }
        return true;
    }
    /**
     *  Checks to see if a method is public.
     *  Fixes method names that end in Tester.
     *  @param string $class_name required defaults to ''
     *  @param string $method_name required defaults to ''
     *  @return bool true or false
    **/
    public function isPublicMethod($class_name = '', $method_name = '')
    {
        if ($class_name == '' || $method_name == '') { return false; }
        $o_ref = new \ReflectionClass($class_name);
        $o_method = $o_ref->getMethod($method_name);
        return $o_method->IsPublic();
    }
}
