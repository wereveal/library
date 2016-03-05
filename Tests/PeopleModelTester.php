<?php
/**
 * @brief     Tests the Group Model Class.
 * @ingroup   ritc_library lib_tests
 * @file      PeopleModelTester.php
 * @namespace Ritc\Library\Tests
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-03-05 10:44:05
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial rewrite version        - 2016-03-05 wer
 * - v0.1.0         - Initial version                - unknown wer
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Models\PeopleModel;

/**
 * Class PeopleModelTester.
 * @class   PeopleModelTester
 * @package Ritc\Library\Tests
 */
class PeopleModelTester extends Tester
{
    use LogitTraits;

    private $o_db;
    private $o_model;

    public function __construct(Di $o_di)
    {
        $this->o_db    = $o_di->get('db');
        $this->o_model = new PeopleModel($this->o_db);
        $this->o_elog  = $o_di->get('elog');
        $this->o_model->setElog($this->o_elog);
    }

    ### Tests ###
    public function createTester()
    {
        return false;
    }
    public function readTester()
    {
        $a_test_values  = $this->a_test_values['read']['test_values'];
        $a_test_results = $this->a_test_values['read']['expected_results'];
        foreach ($a_test_values as $key => $a_input_values) {
            $results = $this->o_model->read($a_input_values);
            if (Arrays::compareArrays($a_test_results[$key], $results)) {

            }
        }
        return true;
    }
    public function updateTester()
    {
        return false;
    }
    public function deleteTester()
    {
        return false;
    }
    public function readByIdTester()
    {
        return false;
    }
    public function readyByName()
    {
        return false;
    }
    public function isValidGroupId()
    {
        return false;
    }

}
