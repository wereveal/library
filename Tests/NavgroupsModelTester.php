<?php
/**
 * @brief     Tests the NavgroupsModel class..
 * @ingroup   lib_tests
 * @file      Ritc/Library/Tests/NavgroupsModelTester.php
 * @namespace Ritc\Library\Tests
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-06-09 10:14:17
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-09 wer
 * @todo Ritc/Library/Tests/NavgroupsModelTester.php - Everything
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Models\NavgroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\TesterTraits;

/**
 * Class NavgroupsModelTester.
 * @class   NavgroupsModelTester
 * @package Ritc\Library\Tests
 */
class NavgroupsModelTester
{
    use LogitTraits, TesterTraits;

    /** @var \Ritc\Library\Models\NavgroupsModel  */
    private $o_model;

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
        if (empty($this->a_test_values['create'])) {
            return 'skipped';
        }
        return 'failed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function readTester()
    {
        if (empty($this->a_test_values['read'])) {
            return 'skipped';
        }
        $failed = 0;
        foreach ($this->a_test_values['read'] as $key => $a_values) {
            $subtest_name = $key;
            if (strpos($key, '_id') !== false) {
                $test_value = ['ng_id' => $a_values['test_value']];
            }
            else {
                $test_value = ['ng_name' => $a_values['test_value']];
            }
            $expected_results = $a_values['expected_results'];
            $results = empty($this->o_model->read($test_value))
                ? false
                : true
            ;
            if ($results && $expected_results) {
                $this->setSubPassed('read', $subtest_name);
            }
            else {
                $this->setSubFailed('read', $subtest_name);
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
        if (empty($this->a_test_values['update'])) {
            return 'skipped';
        }
        return 'failed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function deleteTester()
    {
        if (empty($this->a_test_values['delete'])) {
            return 'skipped';
        }
        return 'failed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function deleteWithMapTester()
    {
        if (empty($this->a_test_values['deleteWithMap'])) {
            return 'skipped';
        }
        return 'failed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function readByNameTester()
    {
        if (empty($this->a_test_values['readByName'])) {
            return 'skipped';
        }
        return 'failed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function readIdByNameTester()
    {
        if (empty($this->a_test_values['readIdByName'])) {
            return 'skipped';
        }
        return 'failed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function retrieveDefaultIdTester()
    {
        if (empty($this->a_test_values['retrieveDefaultId'])) {
            return 'skipped';
        }
        return 'failed';
    }

    /**
     * Tests the method.
     * @return string
     */
    public function retrieveDefaultNameTester()
    {
        if (empty($this->a_test_values['retrieveDefaultName'])) {
            return 'skipped';
        }
        return 'failed';
    }
}