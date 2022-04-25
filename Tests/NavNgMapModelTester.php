<?php
/**
 * Class NavNgMapModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Models\NavNgMapModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;

/**
 * NavNgMapModel class tester.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-12-01 14:16:10
 * @change_log
 * - v1.0.0-alpha.1 - updated for php 8                         - 2021-12-01 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-06-09 wer
 * @todo Ritc/Library/Tests/NavNgMapModelTester.php - Everything
 */
class NavNgMapModelTester extends Tester

{
    /** @var NavNgMapModel */
    protected NavNgMapModel $o_db;

    /**
     * NavNgMapModelTester constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        /** @var DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->o_db = new NavNgMapModel($o_db);
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
