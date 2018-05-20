<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Models\NavigationModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * NavigationModel class tester.
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-06-09 10:15:23
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-09 wer
 * @todo Ritc/Library/Tests/NavigationModelTester.php - Everything
 */
class NavigationModelTester extends Tester
{
    use LogitTraits;

    /** @var \Ritc\Library\Models\NavigationModel  */
    private $o_db;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->o_db = new NavigationModel($this->o_db);
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