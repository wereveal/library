<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Service\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Models\PageModel;

class PeopleModelTester extends Tester
{
    use LogitTraits;

    private $o_db;
    private $o_model;

    public function __construct(Di $o_di)
    {
        $this->o_db    = $o_di->get('db');
        $this->o_model = new PageModel($this->o_db);
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
