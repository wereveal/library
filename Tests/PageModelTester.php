<?php
/**
 * Class PageModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Models\PageModel;

/**
 * Class PageModelTester.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2016-03-05 10:44:05
 * @todo    Rewrite it all
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial rewrite version        - 2016-03-05 wer
 * - v0.1.0         - Initial version                - unknown wer
 */
class PageModelTester extends Tester
{
    use LogitTraits;

    /** @var \Ritc\Library\Services\DbModel */
    private $o_db;
    /** @var PageModel */
    private $o_model;

    /**
     * PageModelTester constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        /** @var \Ritc\Library\Services\DbModel o_db */
        $this->o_db    = $o_di->get('db');
        $this->o_model = new PageModel($this->o_db);
        $this->o_model->setupElog($o_di);
    }

    ### Tests ###
    /**
     * @return bool
     */
    public function createTester()
    {
        return false;
    }

    /**
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readTester()
    {
        $a_test_values  = $this->a_test_values['read']['test_values'];
        $a_test_results = $this->a_test_values['read']['expected_results'];
        foreach ($a_test_values as $key => $a_input_values) {
            $results = $this->o_model->read($a_input_values);
            if (!Arrays::compareArrays($a_test_results[$key], $results)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function updateTester()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function deleteTester()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function readByIdTester()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function readyByName()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isValidGroupId()
    {
        return false;
    }
}
