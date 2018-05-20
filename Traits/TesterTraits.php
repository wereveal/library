<?php
namespace Ritc\Library\Traits;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\LocateFile;

/**
 * Provides all the basic commands which are extended by specific testers for specific classes.
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v4.2.0
 * @date    2017-10-19 14:03:18
 * ## Change log
 * - v4.2.0 - updated setValues and setOrder to not use an empty parameter              - 2017-10-19 wer
 * - v4.1.0 - Added new methods specific to model testing                               - 2017-06-20 wer
 * - v4.0.0 - Turned the class into a trait to maybe remove a hidden bug                - 2017-06-09 wer
 * - v3.7.0 - Refactoring of subtest methods                                            - 2017-06-09 wer
 * - v3.6.0 - switched setupTests to use LocateFile class                               - 2017-05-30 wer
 * - v3.5.0 - modified setupTests to create test order from test values                 - 2017-05-12 wer
 * - v3.4.0 - modified setupTests to set a new property class_name                      - 2016-04-20 wer
 * - v3.3.0 - added new method to automate some setup                                   - 2016-03-10 wer
 * - v3.2.0 - moved the compare_arrays to the Arrays helper class                       - 11/02/2015 wer
 * - v3.1.0 - no longer extends Base class, uses Logit Trait instead                    - 08/19/2015 wer
 * - v3.0.0 - changed to be a class so it could extend Base class and modified for such - 09/24/2014 wer
 * - v2.0.0 - modified to not do any view stuff                                         - 2013-12-13 wer
 * - v1.1.0 - added new a couple new methods                                            - 2013-05-10 wer
 *      - compare_arrays
 *          - checks to see if the values in the first array
 *          - exist in the second array
 *      - setSubFailed
 *          - allows the test results to display individual subtests
 *          - within the method tester
 * - v1.0.1 - updated to match new framework                                            - 2013-04-03 wer
 */
trait TesterTraits
{
    /** @var array specifies the order of tests */
    protected $a_test_order       = [];
    /** @var array values that will be tested */
    protected $a_test_values      = [];
    /** @var string name of the class */
    protected $class_name         = '';
    /** @var bool the output will show which sub-tests are passed  */
    protected $show_passed_subs   = false;
    /** @var array which sub-tests were failed */
    protected $failed_subtests    = [];
    /** @var array names of the failed tests */
    protected $failed_test_names  = [];
    /** @var int number of failed tests */
    protected $failed_tests       = 0;
    /** @var  string name of the instance */
    protected $instance_name      = '';
    /** @var string name of the namespace */
    protected $namespace          = '';
    /** @var int the new id of the record created */
    protected $new_id             = -1;
    /** @var int number of tests run */
    protected $num_o_tests        = 0;
    /** @var string name of the file which specifies the order of tests */
    protected $order_file         = 'test_order.php';
    /** @var array names of passed sub-tests */
    protected $passed_subtests    = [];
    /** @var array names of passed tests */
    protected $passed_test_names  = [];
    /** @var int number of passed tests */
    protected $passed_tests       = 0;
    /** @var array names of test to skip */
    protected $skipped_test_names = [];
    /** @var int numbe of skipped tests */
    protected $skipped_tests      = 0;
    /** @var string name of the file which contains the values to be tested */
    protected $values_file        = 'test_values.php';

    ### Main Methods for Testing ###
    /**
     * Runs tests where method ends in Test.
     * @param bool $return_results optional, defaults to true which also returns test names.
     * @return array
     */
    public function runTests($return_results = true)
    {
        if ($this->class_name != '') {
            $class_name = $this->class_name;
        }
        elseif (substr(__CLASS__, -5) == 'Tests') {
            $class_name = str_replace('Tests','',__CLASS__);
        }
        elseif (substr(__CLASS__, -6) == 'Tester') {
            $class_name = str_replace('Tester','',__CLASS__);
        }
        else {
            return [];
        }
        $ns_class = $this->namespace . '\\' . $class_name;
        $a_test_order = [];
        if (count($this->a_test_order) === 0) {
            $o_ref = new \ReflectionClass($ns_class);
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
        $failed_tests = 0;
        foreach ($a_test_order as $method_name) {
            if (substr($method_name, -6) == 'Tester') {
                $tester_name = $method_name;
                $method_name = $this->shortenName($method_name);
            } else {
                $tester_name = $method_name . 'Tester';
            }
            if ($this->isPublicMethod($tester_name)) {
                $results = $this->$tester_name();
                switch ($results) {
                    case 'passed':
                        $this->passed_tests++;
                        $this->passed_test_names[] = $method_name;
                        break;
                    case 'skipped':
                        $this->skipped_tests++;
                        $this->skipped_test_names[] = $method_name;
                        break;
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
        if ($return_results) {
            return $this->returnTestResults($return_results);
        }
        return [];
    }

    /**
     * Sets up the two main arrays the tests uses.
     * @param array $a_values ['namespace', 'class_name', 'order_file', 'values_file', 'extra_dir']
     */
    public function setupTests(array $a_values = [])
    {
        $namespace     = '';
        $class_name    = '';
        $instance_name = '';
        $order_file    = 'test_order.php';
        $values_file   = 'test_values.php';
        $extra_dir     = '';
        $short_ns      = __NAMESPACE__;
        $passed_subs   = false;

        $a_expected_keys = [
            'namespace',
            'class_name',
            'instance_name',
            'order_file',
            'values_file',
            'extra_dir',
            'passed_subs'
        ];
        foreach ($a_expected_keys as $keyname) {
            if (isset($a_values[$keyname])) {
                $$keyname = $a_values[$keyname];
            }
        }
        if ($passed_subs) {
            $this->show_passed_subs = $passed_subs;
        }
        if (!empty($namespace)) {
            $this->namespace = $namespace;
            $a_ns_part = explode('\\', $namespace);
            $short_ns = '';
            for ($i = 0; $i < count($a_ns_part) - 1; $i++) {
                $short_ns .= empty($short_ns)
                    ? $a_ns_part[$i]
                    : '\\' . $a_ns_part[$i];
            }
        }
        if (!empty($class_name)) {
            $this->class_name = $class_name;
            $values_file = $class_name . '_values.php';
            $order_file = $class_name . '_order.php';
        }
        if (!empty($instance_name)) {
            $this->instance_name = $instance_name;
        }
        $test_values_file = LocateFile::getTestFileWithPath($values_file, $short_ns, $extra_dir);
        if (empty($test_values_file)) {
            $this->a_test_values = [];
        }
        else {
            $this->values_file = $test_values_file;
            $this->a_test_values = include $test_values_file;
        }
        $test_order_file = LocateFile::getTestFileWithPath($order_file, $short_ns, $extra_dir);
        if (!empty($test_order_file)) {
            $this->order_file = $test_order_file;
            $this->a_test_order = include $test_order_file;
        }
        else {
            if (!empty($a_test_values)) {
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

    /**
     * Deletes a test record if the id exists.
     * @param string $instance
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function cleanupDbTests($instance = '') {
        if ($this->new_id > 0) {
            $new_id = $this->new_id;
        }
        elseif (isset($_SESSION['created_id'])) {
            $new_id = $_SESSION['created_id'];
        }
        else {
            $new_id = -1;
        }
        if ($instance == '') {
            $instance = $this->instance_name;
        }
        if ($new_id > 0) {
            try {
                $this->$instance->delete($new_id);
                $_SESSION['created_id'] = -1;
                $this->new_id = -1;
            }
            catch (ModelException $e) {
                $_SESSION['created_id'] = -1;
                $this->new_id = -1;
            }
        }
    }

    ### Generic Tests - usually called by the main tester class ###
    /**
     * Generic Test comparing returned value(s) and expected value(s).
     * @param array $a_names
     * @param array $a_test_values
     * @return string
     */
    private function genericTest(array $a_names = [], array $a_test_values = [])
    {
        if (empty($a_test_values) || empty($a_names) || empty($a_names['instance']) || empty($a_names['test'])) {
            return 'skipped';
        }
        $good_results = true;
        foreach ($a_test_values as $subtest => $a_values) {
            $a_names['subtest'] = $subtest;
            $results = $this->genericSubtest($a_names, $a_values);
            $good_results = $good_results && $results;
        }
        if ($good_results) {
            return 'passed';
        }
        return 'failed';
    }

    /**
     * Runs a single subtest.
     * @param array $a_names
     * @param array $a_values
     * @return bool
     */
    public function genericSubtest(array $a_names = [], array $a_values = [])
    {
        if (empty($a_names) || empty($a_names['instance']) || empty($a_names['test']) || empty($a_names['subtest'])) {
            return false;
        }
        $o_name  = $a_names['instance'];
        $test    = $a_names['test'];
        $subtest = $a_names['subtest'];
        $good_results = true;
        $expected_results = $a_values['expected_results'];
        try {
            if (isset($a_values['test_values'])) {
                $test_this = $a_values['test_values'];
            }
            elseif (isset($a_values['test_value'])) {
                $test_this = $a_values['test_value'];
            }
            else {
                $test_this = '';
            }
            $results = $this->$o_name->$test($test_this);
            if ($results == $expected_results) {
                $this->setSubPassed($test, $subtest);
            }
            else {
                $this->setSubFailed($test, $subtest);
                $good_results = false;
            }
        }
        catch (\Exception $e) {
            $this->setSubFailed($test, $subtest);
            $good_results = false;
        }
        return $good_results;
    }

    /**
     * Generic Test for Database operations.
     * @param array $a_names
     * @param array $a_test_values
     * @return string
     */
    private function genericDbTest(array $a_names = [], array $a_test_values = [])
    {
        if (empty($a_test_values) || empty($a_names)) {
            return 'skipped';
        }
        if (Arrays::isArrayOfAssocArrays($a_test_values)) {
            $good_results = true;
            foreach ($a_test_values as $subtest => $a_values) {
                $a_names['subtest'] = $subtest;
                $results = $this->genericDbSubTest($a_names, $a_values);
                $good_results = $good_results && $results;
            }
            if ($good_results) {
                return 'passed';
            }
        }
        else {
            return $this->genericSingleTest($a_names, $a_test_values);
        }
        return 'failed';
    }

    /**
     * Runs a subtest.
     * @param array $a_names
     * @param array $a_values
     * @return bool
     */
    public function genericDbSubTest(array $a_names = [], array $a_values = [])
    {
        if (empty($a_names['test']) || empty($a_names['subtest'])) {
            return false;
        }
        $instance = empty($a_names['instance'])
            ? 'o_model'
            : $a_names['instance'];
        $test    = $a_names['test'];
        $subtest = $a_names['subtest'];
        $good_results = true;
        $expected_results = $a_values['expected_results'];
        if (isset($a_values['test_values'])) {
            $test_this = $a_values['test_values'];
        }
        elseif (isset($a_values['test_value'])) {
            $test_this = $a_values['test_value'];
        }
        else {
            $test_this = '';
        }
        try {
            $a_results = $this->$instance->$test($test_this);
            $results = empty($a_results)
                ? false
                : true;
            if ($results == $expected_results) {
                $this->setSubPassed($test, $subtest);
                if ($test == 'create') {
                    if ($subtest == 'valid_create_values' || $subtest == 'good_values') {
                        $_SESSION['created_id'] = $a_results[0];
                        $this->new_id = $a_results[0];
                    }
                }
            }
            else {
                $this->setSubFailed($test, $subtest);
                $good_results = false;
            }
        }
        catch (ModelException $e) {
            if ($expected_results == false) {
                $this->setSubPassed($test, $subtest);
            }
            else {
                $this->setSubFailed($test, $subtest);
                $good_results = false;
            }
        }
        catch (\TypeError $e) {
            if ($expected_results == false) {
                $this->setSubPassed($test, $subtest);
            }
            else {
                $this->setSubFailed($test, $subtest);
                $good_results = false;
            }
        }
        catch (\Exception $e) {
            if ($expected_results == false) {
                $this->setSubPassed($test, $subtest);
            }
            else {
                $this->setSubFailed($test, $subtest);
                $good_results = false;
            }
        }
        catch (\Error $e) {
            if ($expected_results == false) {
                $this->setSubPassed($test, $subtest);
            }
            else {
                $this->setSubFailed($test, $subtest);
                $good_results = false;
            }
        }
        return $good_results;
    }

    /**
     * Runs a single generic test.
     * @param array $a_names
     * @param array $a_test_values
     * @return string
     */
    public function genericSingleTest(array $a_names = [], array $a_test_values)
    {
        if (empty($a_names) || empty($a_test_values)) {
            return 'skipped';
        }
        $instance = $a_names['instance'];
        $test = $a_names['test'];
        $expected_results = $a_test_values['expected_results'];
        $test_this = $a_test_values['test_values'];
        $results = $this->$instance->$test($test_this);
        if ($results == $expected_results) {
            return 'passed';
        }
        else {
            return 'failed';
        }
    }

    /**
     * Runs a single database test.
     * @param array $a_names
     * @param array $a_test_values
     * @return string
     */
    public function genericDbSingleTest(array $a_names = [], array $a_test_values)
    {
        if (empty($a_names) || empty($a_test_values)) {
            return 'skipped';
        }
        $test = $a_names['test'];
        $instance = $a_names['instance'];
        $expected_results = $a_test_values['expected_results'];
        $test_this = $a_test_values['test_values'];
        try {
            $a_results = $this->$instance->$test($test_this);
            $results = empty($a_results)
                ? false
                : true;
            if ($results == $expected_results) {
                return 'passed';
            }
            else {
                return 'failed';
            }
        }
        catch (ModelException $e) {
            return $expected_results
                ? 'failed'
                : 'passed';
        }
    }

    ### All the other methods needed to run tests ###
    /**
     * Returns an array showing the number and optionally names of tests success and failure
     * @param bool $show_test_names optional defaults to showing names
     * @return array
     */
    public function returnTestResults($show_test_names = true)
    {
        $a_failed_test_names = array();
        $a_passed_test_names = array();
        if ($show_test_names === true) {
            foreach ($this->passed_test_names as $name) {
                $a_subnames = [];
                if ($this->show_passed_subs) {
                    if (!empty($this->passed_subtests[$name])) {
                        $a_subnames = $this->passed_subtests[$name];
                    }
                }
                $a_passed_test_names[] = [
                    'name' => $name,
                    'subtest_names' => $a_subnames
                ];
            }
            foreach ($this->failed_test_names as $name) {
                $a_failed = [];
                $a_success = [];
                if (!empty($this->failed_subtests[$name])) {
                    $a_failed = $this->failed_subtests[$name];
                }
                if (!empty($this->passed_subtests[$name])) {
                    $a_success = $this->passed_subtests[$name];
                }
                $a_failed_test_names[] = [
                    'name'            => $name,
                    'subtest_failed'  => $a_failed,
                    'subtest_success' => $a_success
                ];
            }
            return array(
                'failed_tests'       => $this->failed_tests,
                'passed_tests'       => $this->passed_tests,
                'skipped_tests'      => $this->skipped_tests,
                'num_o_tests'        => $this->num_o_tests,
                'failed_test_names'  => $a_failed_test_names,
                'passed_test_names'  => $a_passed_test_names,
                'skipped_test_names' => $this->skipped_test_names
            );
        }
        else {
            return array(
                'failed_tests'       => $this->failed_tests,
                'passed_tests'       => $this->passed_tests,
                'num_o_tests'        => $this->num_o_tests,
                'failed_test_names'  => '',
                'passed_test_names'  => '',
                'skipped_test_names' => ''
            );
        }
    }

    /**
     * Adds a method name to the test order.
     * @param string $method_name
     * @return bool
     */
    public function addMethodToTestOrder($method_name = '')
    {
        if ($method_name == '') { return false; }
        $this->a_test_order[] = $method_name;
        return true;
    }

    /**
     * Adds a single key=>value pair to the a_test_values array
     * @param string $key the key name
     * @param mixed $value  the value assigned to the key
     */
    public function addTestValue($key = '', $value = '')
    {
        if ($key == '') { return; }
        $this->a_test_values[$key] = $value;
    }

    /**
     * Getter
     * @return array
     */
    public function getFailedTestNames()
    {
        return $this->failed_test_names;
    }

    /**
     * Getter
     * @return int
     */
    public function getFailedTests()
    {
        return $this->failed_tests;
    }

    /**
     * Getter
     * @return int
     */
    public function getNumOTests()
    {
        return $this->num_o_tests;
    }

    /**
     * Getter
     * @return int
     */
    public function getPassedTests()
    {
        return $this->passed_tests;
    }

    /**
     * Getter
     * @return array
     */
    public function getPassedTestNames()
    {
        return $this->passed_test_names;
    }

    /**
     * Getter
     * @return array
     */
    public function getTestOrder()
    {
        return $this->a_test_order;
    }

    /**
     * Removes Tester or Test from method name
     * @param  string $method_name defaults to 'Tester'
     * @return string
     */
    public function shortenName($method_name = 'Tester')
    {
        if (strpos($method_name, '::')) {
            $a_parts = explode('::', $method_name);
            $method_name = $a_parts[1];
        }
        if (substr($method_name, -6) == 'Tester') {
            return substr($method_name, 0, -6);
        }
        if (substr($method_name, -5) == 'Tests') {
            return substr($method_name, 0, -5);
        }
        return $method_name;
    }

    /**
     * Standard Setter for the property $class_name;
     * @param string $class_name
     */
    public function setClassName($class_name = '')
    {
        $this->class_name = $class_name;
    }

    /**
     * Sets three properties, num_o_test++, failed_tests++, and failed test names.
     * @param string $method_name
     */
    public function setFailures($method_name = '')
    {
        $this->num_o_tests++;
        $this->failed_tests++;
        $this->failed_test_names[] = $this->shortenName($method_name);
    }

    /**
     * Standard setter for the propery $namespace.
     * @param string $namespace
     */
    public function setNamespace($namespace = '')
    {
        $this->namespace = $namespace;
    }

    /**
     * Sets failed_subtests
     * @param string $method_name
     * @param string $test_name
     */
    public function setSubFailed($method_name = '', $test_name = '')
    {
        if ($method_name == '' || $test_name == '') { return; }
        $method_name = $this->shortenName($method_name);
        if (is_array($this->failed_subtests) === false) {
            $this->failed_subtests = array();
        }
        if (array_key_exists($method_name, $this->failed_subtests)) {
            $this->failed_subtests[$method_name][] = $test_name;
        }
        else {
            $this->failed_subtests[$method_name] = array($test_name);
        }
    }

    /**
     * Records the names of the subtests passed for a test.
     * @param string $method_name
     * @param string $test_name
     */
    public function setSubPassed($method_name = '', $test_name = '')
    {
        if ($method_name != '' && $test_name != '') {
            $method_name = $this->shortenName($method_name);
            if (array_key_exists($method_name, $this->passed_subtests)) {
                $this->passed_subtests[$method_name][] = $test_name;
            }
            else {
                $this->passed_subtests[$method_name] = array($test_name);
            }
        }
    }

    /**
     * Sets the array a_test_order to the array passed in
     * @param array $a_test_order optional, defaults to an empty array
     */
    public function setTestOrder(array $a_test_order = array())
    {
        if (empty($a_test_order)) {
            $a_test_order = include $this->order_file;
        }
        $this->a_test_order = $a_test_order;
    }

    /**
     * Sets the array a_test_value to the array passed in
     * @param array $a_test_values optional, defaults to an empty array
     */
    public function setTestValues(array $a_test_values = array())
    {
        if (empty($a_test_values)) {
            $a_test_values = include $this->values_file;
        }
        $this->a_test_values = $a_test_values;
    }

    /**
     * Return the values in $this->a_test_values
     * @return array $a_test_values
     */
    public function getTestValues()
    {
        return $this->a_test_values;
    }

    ### Utility Methods ###
    /**
     * Checks to see if a method is public in the tester class.
     * @param string $method_name required defaults to ''
     * @return bool true or false
     */
    public function isPublicMethod($method_name = '')
    {
        if ($method_name == '') {
            return false;
        }
        $o_ref = new \ReflectionClass(__CLASS__);
        $o_method = $o_ref->getMethod($method_name);
        return $o_method->isPublic();
    }
}
