<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;

class UrlsModelTester extends Tester
{
    /** @var \Ritc\Library\Models\UrlsModel  */
    private $o_model;

    public function __construct(Di $o_di)
    {
        $o_db = $o_di->getVar('db');
        $this->setupElog($o_di);
        $this->o_model = new UrlsModel($o_db);
        $this->o_model->setupElog($this->o_elog);
    }

    public function createTester()
    {
        return [];
    }

    public function readTester()
    {
        return [];
    }

    public function updateTester()
    {
        return [];
    }

    public function deleteTester()
    {
        return [];
    }

}
