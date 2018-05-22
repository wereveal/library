<?php
/**
 * Class ConstantsModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\TesterTraits;

/**
 * Tests the Constants Model Class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2017-06-14 16:27:15
 * @change_log
 * - v1.0.0         - Initial working version        - 2017-06-14 wer
 * - v1.0.0-alpha.0 - Initial rewrite version        - 2016-03-05 wer
 * - v0.1.0         - Initial version                - unknown wer
 */
class ConstantsModelTester
{
    use LogitTraits, TesterTraits;
    /** @var ConstantsModel */
    private $o_model;

    /**
     * ConstantsModelTester constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $a_test_params = [
            'namespace'     => 'Ritc\Library\Models',
            'class_name'    => 'ConstantsModel',
            'instance_name' => 'o_model'
        ];
        $this->setupTests($a_test_params);
        /** @var \Ritc\Library\Services\DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->o_model = new ConstantsModel($o_db);
        $this->o_model->setElog($this->o_elog);
    }

    ### TESTS ###
    /**
     * @return string
     */
    public function createTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        $a_names = [
            'instance' => 'o_model',
            'test'     => $test_name
        ];
        return $this->genericDbTest($a_names, $this->a_test_values[$test_name]);
    }

    /**
     * @return string
     */
    public function readTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        $a_names = [
            'instance' => 'o_model',
            'test'     => $test_name
        ];
        return $this->genericDbTest($a_names, $this->a_test_values[$test_name]);
    }

    /**
     * @return string
     */
    public function updateTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->new_id)) {
            return 'failed';
        }
        else {
            $a_values = [];
            foreach ($this->a_test_values[$test_name] as $key => $a_test_values) {
                $a_test_values['test_values']['const_id'] = $this->new_id;
                $a_values[$key] = $a_test_values;
            }
        }
        $a_names = [
            'instance' => 'o_model',
            'test'     => $test_name
        ];
        return $this->genericDbTest($a_names, $a_values);
    }

    /**
     * @return string
     */
    public function deleteTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        if (empty($this->a_test_values[$test_name])) {
            return 'skipped';
        }
        $a_names = [
            'instance' => 'o_model',
            'test'     => $test_name
        ];
        $good_results = true;
        if (empty($this->new_id)) {
            if (empty($_SESSION['created_id'])) {
                return 'failed';
            }
            else {
                $new_id = $_SESSION['created_id'];
            }
        }
        else {
            $new_id = $this->new_id;
        }
        foreach ($this->a_test_values[$test_name] as $subtest => $a_test_values) {
            $a_names['subtest'] = $subtest;
            switch ($subtest) {
                case 'no_values':
                    $a_test_values['test_value'] = '';
                    break;
                case 'still_immutable':
                case 'not_immutable':
                    $a_test_values['test_value'] = $new_id;
                    break;
                case 'invalid_id':
                default:
                    // use the given test value.
            }
            if ($subtest == 'not_immutable') {
                try {
                    $results = $this->o_model->update(['const_id' => $new_id, 'const_immutable' => 'false']);
                }
                catch (ModelException $e) {
                    $results = false;
                }
                if ($results !== false) {
                    $results = $this->genericDbSubTest($a_names, $a_test_values);
                    if ($results) {
                        $_SESSION['created_id'] = -1;
                        $this->new_id = -1;
                    }
                }
            }
            else {
                $results = $this->genericDbSubTest($a_names, $a_test_values);
            }
            $good_results = $good_results && $results;
        }
        if ($good_results) {
            return 'passed';
        }
        return 'failed';
    }

    /**
     * @return string
     */
    public function makeValidNameTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        $a_names = [
            'instance' => 'o_model',
            'test'     => $test_name
        ];
        return $this->genericTest($a_names, $this->a_test_values[$test_name]);
    }
}
