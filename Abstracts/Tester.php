<?php
/**
 *  A abstract class for testing that all other testing classes use.
 *  @file Tester.php
 *  @namespace Ritc/Library/Abstract
 *  @class Tester
 *  @author William E Reveal  <bill@revealitconsulting.com>
 *  @version  2.0.0
 *  @date 2013-12-13 15:35:11
 *  @note <pre><b>Change log</b>
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
 *  @note RITC Library v4.0.0
 *  @ingroup ritc_library abstracts
**/
namespace Ritc\Library\Abstracts;

abstract class Tester
{
    protected $a_test_order;
    protected $a_test_values = array();
    protected $failed_subtests;
    protected $failed_test_names = array();
    protected $failed_tests;
    protected $num_o_tests;
    protected $passed_subtests;
    protected $passed_test_names  = array();
    protected $passed_tests;
    public function __construct()
    {
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
        $failed_test_names = '';
        $passed_test_names = '';
        if ($show_test_names === true) {
            $passed_test_names = "Passed Test Names\n";
            if (count($this->passed_test_names) > 0) {
                foreach ($this->passed_test_names as $name) {
                    $passed_test_names .= "&nbsp;&nbsp;&nbsp;&nbsp;{$name}\n";
                }
            }
        }
        if ($show_test_names === true || $show_test_names == 'failed') {
            $failed_test_names = "Failed Test Names\n";
            foreach ($this->failed_test_names as $name) {
                $failed_test_names .= "&nbsp;&nbsp;&nbsp;&nbsp;{$name}\n";
                if (is_array($this->failed_subtests) && isset($this->failed_subtests[$name])) {
                    foreach ($this->failed_subtests[$name] as $individual_test) {
                        $failed_test_names .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$individual_test}\n";
                    }
                }
            }
        }
        return array(
            'failed_tests'      => $this->failed_tests,
            'passed_tests'      => $this->passed_tests,
            'num_o_tests'       => $this->num_o_tests,
            'failed_test_names' => $failed_test_names,
            'passed_test_names' => $passed_test_names
        );
    }
    /**
     *  Runs tests where method ends in Test.
     *  @param string $class_name name of the class to be tested
     *  @param array $a_test_order optional, if provided it ignores
     *      the class property $a_test_order and won't try to build one
     *      from the class methods.
     *  @return int $failed_tests
    **/
    public function runTests($class_name = '', array $a_test_order = array())
    {
        if ($class_name == '' ) { return false; }
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
        // error_log(var_export($a_test_order, true));
        // error_log("Before -- num_o_tests: {$this->num_o_tests} passed tests: {$this->passed_tests} failed tests: {$this->failed_tests} test names: " . var_export($this->failed_test_names, true));
        $failed_tests = 0;
        foreach ($a_test_order as $method_name) {
            // error_log($method_name);
            if (substr($method_name, -6) == 'Tester') {
                $tester_name = $method_name;
                $method_name = $this->shortenName($method_name);
            } else {
                $tester_name = $method_name . 'Tester';
            }
            // error_log("method name: {$method_name} - tester name: {$tester_name}");
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
        // error_log("num_o_tests: {$this->num_o_tests} passed tests: {$this->passed_tests} failed tests: {$this->failed_tests} test names: " . var_export($this->failed_test_names, true));
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
