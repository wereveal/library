<?php
/**
 * Class NavigationModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Models\NavigationModel;
use Ritc\Library\Services\Di;

/**
 * NavigationModel class tester.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-06-09 10:15:23
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-09 wer
 * @todo Ritc/Library/Tests/NavigationModelTester.php - Everything
 */
class NavigationModelTester extends Tester
{
    /** @var \Ritc\Library\Models\NavigationModel  */
    private $o_nav_db;

    /**
     * NavigationModelTester constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        /** @var \Ritc\Library\Services\DbModel $o_db */
        $o_db = $o_di->get('db');
        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->o_nav_db = new NavigationModel($o_db);
        $this->o_nav_db->setElog($this->o_elog);
    }

    /**
     * @return bool
     */
    public function createTester():bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function readTester():bool
    {
        return false;
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
}
