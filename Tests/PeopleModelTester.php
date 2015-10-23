<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Service\Di;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Elog;
use Ritc\Library\Models\PeopleModel;

class PeopleModelTester extends Tester
{
    private $o_db;
    private $o_model;

    public function __construct(Di $o_di)
    {
        $this->o_db    = $o_di->get('db');
        $this->o_model = new PeopleModel($this->o_db);
    }

    ### Tests ###
    public function createTester()
    {
        return false;
    }
    public function readTester()
    {
        return false;
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
