<?php
/**
 * Class UrlsModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;

/**
 * Tests the Group Model Class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-12-01 14:25:17
 * @todo.   Rewrite it all
 * @change_log
 * - 1.0.0-alpha.1  - updated for php 8 standards               - 2021-12-01 wer
 * - 1.0.0-alpha.0  - initial version                           - unknown wer
 */
class UrlsModelTester extends Tester
{
    /** @var UrlsModel */
    protected UrlsModel $o_model;

    /**
     * UrlsModelTester constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $o_db = $o_di->getVar('db');
        $this->o_model = new UrlsModel($o_db);
    }

    /**
     * @return array
     */
    public function createTester():array
    {
        return [];
    }

    /**
     * @return array
     */
    public function readTester():array
    {
        return [];
    }

    /**
     * @return array
     */
    public function updateTester():array
    {
        return [];
    }

    /**
     * @return array
     */
    public function deleteTester():array
    {
        return [];
    }

}
