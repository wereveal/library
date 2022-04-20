<?php
/**
 * Class NavgroupsModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\TesterTraits;

/**
 * Tests the NavgroupsModel class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-12-01 14:13:41
 * @change_log
 * - 1.0.0-alpha.1 - updated to php 8 standards                 - 2021-12-01 wer
 * - 1.0.0-alpha.0 - Initial version                            - 2017-06-09 wer
 * @todo Ritc/Library/Tests/NavgroupsModelTester.php - Everything
 */
class NavgroupsModelTester
{
    use TesterTraits;

    /** @var NavgroupsModel */
    private NavgroupsModel $o_model;
    /** @var int  */
    private int $created_id = -1;

    /**
     * NavgroupsModelTester constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $a_test_params = [
            'namespace'  => 'Ritc\Library\Models',
            'class_name' => 'NavgroupsModel'
        ];
        $this->setupTests($a_test_params);
        /** @var DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->o_model = new NavgroupsModel($o_db);
    }

    /**
     * Tests the method.
     * @return string
     */
    public function createTester():string
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
            $results = !empty($a_results);
            if ($results === $expected_results) {
                $this->setSubPassed($test_name, $subtest_name);
                if ($subtest_name === 'valid_values') {
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
    public function readTester():string
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
            $results = !empty($a_results)
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
    public function updateTester():string
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

            $created_id = $_SESSION['created_id'];
        }
        else {
            $created_id = $this->created_id;
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $test_values = $a_values['test_values'];
            if ($key === 'valid_values') {
                $test_values['ng_id'] = $created_id;
            }
            if ($key === 'duplicate_name') {
                $test_values['ng_id'] = $created_id;
            }
            if ($key === 'missing_name') {
                $test_values['ng_id'] = $created_id;
            }
            $expected_results = $a_values['expected_results'];
            $a_results = $this->o_model->$test_name($test_values);
            if ($a_results === $expected_results) {
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
    public function deleteTester():string
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

            $created_id = $_SESSION['created_id'];
        }
        else {
            $created_id = $this->created_id;
        }
        try {
            $default_ng_id = $this->o_model->retrieveDefaultId();
        }
        catch (ModelException) {
            $this->setSubFailed($test_name, 'Unknown error: could not retrieve default id.');
            return 'failed';
        }
        if (empty($default_ng_id) || $default_ng_id < 1) {
            $this->setSubFailed($test_name, 'Unknown error: could not retrieve default id.');
            return 'failed';
        }
        $failed = 0;
        foreach ($this->a_test_values[$test_name] as $key => $a_values) {
            $ng_id = $a_values['test_values']['ng_id'];
            if ($ng_id === 'created') {
                $ng_id = $created_id;
            }
            if ($ng_id === 'default') {
                $ng_id = $default_ng_id;
            }
            if ($ng_id === 'immutable') {
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
            catch (ModelException) {
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
    public function deleteWithMapTester():string
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
    public function readByIdTester():string
    {
        $test_name = $this->shortenName(__method__);
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
            $results = !empty($a_results)
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
    public function readByNameTester():string
    {
        $test_name = $this->shortenName(__method__);
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
            $results = !empty($a_results)
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
    public function readIdByNameTester():string
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
            $results = !empty($a_results)
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
    public function retrieveDefaultIdTester():string
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $expected_results = $this->a_test_values[$test_name]['expected_results'];
        $results = $this->o_model->$test_name();
        $results = $results !== -1
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
    public function retrieveDefaultNameTester():string
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $expected_results = $this->a_test_values[$test_name]['expected_results'];
        $results = $this->o_model->$test_name();
        $results = !empty($results)
        ;
        if ($results !== $expected_results) {
            return 'failed';
        }
        return 'passed';
    }
}
