<?php
/**
 * @brief     Tests the Constants Model Class.
 * @details   Tests the Constants Model Class.
 * @ingroup   lib_tests
 * @file      ConstantsModelTester.php
 * @namespace Ritc\Library\Tests
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-03-05 10:44:05
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial rewrite version        - 2016-03-05 wer
 * - v0.1.0         - Initial version                - unknown wer
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\TesterTraits;

/**
 * Class ConstantsModelTester.
 * @class   ConstantsModelTester
 * @package Ritc\Library\Tests
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
            'namespace'  => 'Ritc\Library\Models',
            'class_name' => 'ConstantsModel'
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
        return $this->genericDbTest($test_name, $this->a_test_values[$test_name]);
    }

    /**
     * @return string
     */
    public function readTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        return $this->genericDbTest($test_name, $this->a_test_values[$test_name]);
    }

    /**
     * @return string
     */
    public function updateTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        return $this->genericDbTest($test_name, $this->a_test_values[$test_name]);
    }

    /**
     * @return string
     */
    public function deleteTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        return $this->genericDbTest($test_name, $this->a_test_values[$test_name]);
    }

    /**
     * @return string
     */
    public function makeValidNameTester()
    {
        $test_name = $this->shortenName(__METHOD__);
        return $this->genericTest('o_model', $test_name, $this->a_test_values[$test_name]);
    }
}
