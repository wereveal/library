<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Service\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Models\PeopleModel;

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
        $a_test_stuph = $this->a_test_values['read'];
        foreach ($a_test_stuph as $a_test) {
            $results = $this->o_model->read($a_test['test_values']);
            if (!$this->compareArrays($a_test['expected_results'], $results)) {
                return false;
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
