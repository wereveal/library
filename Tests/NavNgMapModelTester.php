<?php
/**
 * @brief     NavNgMapModel class tester.
 * @ingroup   lib_tests
 * @file      Ritc/Library/Tests/NavNgMapModelTester.php
 * @namespace Ritc\Library\Tests
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-06-09 10:16:28
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-09 wer
 * @todo Ritc/Library/Tests/NavNgMapModelTester.php - Everything
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Models\NavNgMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NavNgMapModelTester.
 * @class   NavNgMapModelTester
 * @package Ritc\Library\Tests
 */
class NavNgMapModelTester extends Tester

{
    use LogitTraits;

    /** @var \Ritc\Library\Models\NavNgMapModel  */
    private $o_db;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->o_db = new NavNgMapModel($this->o_db);
        $this->o_db->setElog($this->o_elog);
    }

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
}