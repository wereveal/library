<?php
/**
 * Class PeopleModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Models\PeopleModel;

/**
 * Tests the Group Model Class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2016-03-05 10:44:05
 * @todo.   Rewrite it all
 * @change_log
 * - v1.0.0-alpha.0 - Initial rewrite version        - 2016-03-05 wer
 * - v0.1.0         - Initial version                - unknown wer
 */
class PeopleModelTester extends Tester
{
    /** @var DbModel */
    private $o_db;
    /** @var PeopleModel */
    private $o_model;

    /**
     * PeopleModelTester constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_db    = $o_di->get('db');
        $this->o_model = new PeopleModel($this->o_db);
        $this->o_elog  = $o_di->get('elog');
        $this->o_model->setElog($this->o_elog);
    }

    ### Tests ###
    /**
     * @return bool
     */
    public function createTester():bool
    {
        return false;
    }

    /**
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readTester():bool
    {
        $a_test_values  = $this->a_test_values['read']['test_values'];
        $a_test_results = $this->a_test_values['read']['expected_results'];
        foreach ($a_test_values as $key => $a_input_values) {
            $results = $this->o_model->read($a_input_values);
            if (Arrays::compareArrays($a_test_results[$key], $results)) {
                return true;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function updateTester():bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function deleteTester():bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function readByIdTester():bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function readyByName():bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isValidGroupId():bool
    {
        return false;
    }

}
