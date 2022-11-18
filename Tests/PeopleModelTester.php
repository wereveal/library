<?php
/**
 * Class PeopleModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Traits\TesterTraits;

/**
 * Tests the Group Model Class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-12-01 14:33:12
 * @todo.   Rewrite it all
 * @change_log
 * - 1.0.0-alpha.1  - updated to php 8 standards                - 2021-12-01 wer
 * - 1.0.0-alpha.0  - Initial rewrite version                   - 2016-03-05 wer
 * - 0.1.0          - Initial version                           - unknown wer
 */
class PeopleModelTester
{
    use TesterTraits;
    /** @var DbModel */
    protected DbModel $o_db;
    /** @var PeopleModel */
    protected PeopleModel $o_model;

    /**
     * PeopleModelTester constructor.
     *
     * @param Di $o_di
     * @noinspection PhpFieldAssignmentTypeMismatchInspection
     */
    public function __construct(Di $o_di)
    {
        $this->o_db    = $o_di->get('db');
        $this->o_model = new PeopleModel($this->o_db);
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
     * @throws ModelException
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
