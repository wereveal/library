<?php
/**
 * Class NavgroupsModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\TesterTraits;

/**
 * Tests the NavgroupsModel class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-06-09 10:14:17
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-09 wer
 * @todo Ritc/Library/Tests/NavgroupsModelTester.php - Everything
 */
class NavgroupsModelTester
{
    use LogitTraits, TesterTraits;

    /** @var \Ritc\Library\Models\NavgroupsModel  */
    private $o_model;
    /** @var int  */
    private $created_id = -1;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $a_test_params = [
            'namespace'  => 'Ritc\Library\Models',
            'class_name' => 'NavgroupsModel'
        ];
        $this->setupTests($a_test_params);
        /** @var \Ritc\Library\Services\DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->o_model = new NavgroupsModel($o_db);
        $this->o_model->setElog($this->o_elog);
    }

    /**
     * Tests the method.
     * @return string
     */
    public function createTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $subtest_name = $key;
            $expected_results = $a_values['expected_results'];
            $a_results = $this->o_model->$test_name($a_values['test_values']);
            $results = empty($a_results)
                ? false
                : true;
            if ($results === $expected_results) {
                $this->setSubPassed($test_name, $subtest_name);
                if ($subtest_name == 'valid_values') {
                    $_SESSION['created_id'] = $a_results[0];
                    $this->created_id = $a_results[0];
                }
            }
            else {
                $this->setSubFailed($test_name, $subtest_name);
                $failed++;
            }
        }
        if ($failed > 0) {
            return 'failed';
        }
        return 'passed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function readTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $subtest_name = $key;
            if (strpos($key, '_id')) {
                $test_value = ['ng_id' => $a_values['test_value']];
            }
            elseif (strpos($key, '_name')) {
                $test_value = ['ng_name' => $a_values['test_value']];
            }
            else {
                $test_value = [];
            }
            $expected_results = $a_values['expected_results'];
            $a_results = $this->o_model->$test_name($test_value);
            $results = empty($a_results)
                ? false
                : true
            ;
            if ($results === $expected_results) {
                $this->setSubPassed($test_name, $subtest_name);
            }
            else {
                $this->setSubFailed($test_name, $subtest_name);
                $failed++;
            }
        }
        if ($failed > 0) {
            return 'failed';
        }
        return 'passed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function updateTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        if ($this->created_id < 1) {
            if (empty($_SESSION['created_id'])) {
                $this->setSubFailed($test_name, 'Missing Created ID');
                return 'failed';
            }
            else {
                $created_id = $_SESSION['created_id'];
            }
        }
        else {
            $created_id = $this->created_id;
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $test_values = $a_values['test_values'];
            if ($key == 'valid_values') {
                $test_values['ng_id'] = $created_id;
            }
            if ($key == 'duplicate_name') {
                $test_values['ng_id'] = $created_id;
            }
            if ($key == 'missing_name') {
                $test_values['ng_id'] = $created_id;
            }
            $expected_results = $a_values['expected_results'];
            $a_results = $this->o_model->$test_name($test_values);
            if ($a_results == $expected_results) {
                $this->setSubPassed($test_name, $key);
            }
            else {
                $this->setSubFailed($test_name, $key);
                $failed++;
            }
        }
        if ($failed > 0) {
            return 'failed';
        }
        return 'passed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function deleteTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        if ($this->created_id < 1) {
            if (empty($_SESSION['created_id'])) {
                $this->setSubFailed($test_name, 'Missing Created ID');
                return 'failed';
            }
            else {
                $created_id = $_SESSION['created_id'];
            }
        }
        else {
            $created_id = $this->created_id;
        }
        try {
            $default_ng_id = $this->o_model->retrieveDefaultId();
        }
        catch (ModelException $e) {
            $this->setSubFailed($test_name, "Unknown error: could not retrieve default id.");
            return 'failed';
        }
        if (empty($default_ng_id) || $default_ng_id < 1) {
            $this->setSubFailed($test_name, "Unknown error: could not retrieve default id.");
            return 'failed';
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $ng_id = $a_values['test_values']['ng_id'];
            if ($ng_id == 'created') {
                $ng_id = $created_id;
            }
            if ($ng_id === 'default') {
                $ng_id = $default_ng_id;
            }
            if ($ng_id == 'immutable') {
                $ng_id = $default_ng_id;
            }
            try {
                $results = $this->o_model->delete($ng_id);
                if ($results === $a_values['expected_results']) {
                    $this->setSubPassed($test_name, $key);
                }
                else {
                    $this->setSubFailed($test_name, $key);
                    $failed++;
                }
            }
            catch (ModelException $e) {
                $this->setSubFailed($test_name, $key);
                $failed++;
            }
        }
        if ($failed > 0) {
            return 'failed';
        }
        return 'passed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function deleteWithMapTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        return 'failed';
    }

    /**
     * Tests the method readById.
     * @return string
     */
    public function readByIdTester()
    {
        $test_name = $this->shortenname(__method__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $subtest_name = $key;
            if (strpos($key, '_id')) {
                $test_value = $a_values['test_value'];
            }
            elseif (strpos($key, '_name')) {
                $test_value = $a_values['test_value'];
            }
            else {
                $test_value = '';
            }
            $expected_results = $a_values['expected_results'];
            $a_results = $this->o_model->$test_name($test_value);
            $results = empty($a_results)
                ? false
                : true
            ;
            if ($results === $expected_results) {
                $this->setsubpassed($test_name, $subtest_name);
            }
            else {
                $this->setsubfailed($test_name, $subtest_name);
                $failed++;
            }
        }
        if ($failed > 0) {
            return 'failed';
        }
        return 'passed';

    }

    /**
     * Tests the method.
     * @return string
     */
    public function readByNameTester()
    {
        $test_name = $this->shortenname(__method__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $subtest_name = $key;
            if (strpos($key, '_id')) {
                $test_value = $a_values['test_value'];
            }
            elseif (strpos($key, '_name')) {
                $test_value = $a_values['test_value'];
            }
            else {
                $test_value = '';
            }
            $expected_results = $a_values['expected_results'];
            $a_results = $this->o_model->$test_name($test_value);
            $results = empty($a_results)
                ? false
                : true
            ;
            if ($results === $expected_results) {
                $this->setsubpassed($test_name, $subtest_name);
            }
            else {
                $this->setsubfailed($test_name, $subtest_name);
                $failed++;
            }
        }
        if ($failed > 0) {
            return 'failed';
        }
        return 'passed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function readIdByNameTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $subtest_name = $key;
            if (strpos($key, '_id')) {
                $test_value = $a_values['test_value'];
            }
            elseif (strpos($key, '_name')) {
                $test_value = $a_values['test_value'];
            }
            else {
                $test_value = '';
            }
            $expected_results = $a_values['expected_results'];
            $a_results = $this->o_model->$test_name($test_value);
            $results = empty($a_results)
                ? false
                : true
            ;
            if ($results === $expected_results) {
                $this->setSubPassed($test_name, $subtest_name);
            }
            else {
                $this->setSubFailed($test_name, $subtest_name);
                $failed++;
            }
        }
        if ($failed > 0) {
            return 'failed';
        }
        return 'passed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function retrieveDefaultIdTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $expected_results = $this->a_test_values[$test_name]['expected_results'];
        $results = $this->o_model->$test_name();
        $results = $results == -1
            ? false
            : true
        ;
        if ($results !== $expected_results) {
            return 'failed';
        }
        return 'passed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function retrieveDefaultNameTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $expected_results = $this->a_test_values[$test_name]['expected_results'];
        $results = $this->o_model->$test_name();
        $results = empty($results)
            ? false
            : true
        ;
        if ($results !== $expected_results) {
            return 'failed';
        }
        return 'passed';
    }
}
