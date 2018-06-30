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
 * Class UrlsModelTester
 * @package Ritc\Library\Tests
 */
class UrlsModelTester extends Tester
{
    /** @var \Ritc\Library\Models\UrlsModel  */
    private $o_model;

    /**
     * UrlsModelTester constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $o_db = $o_di->getVar('db');
        $this->setupElog($o_di);
        $this->o_model = new UrlsModel($o_db);
        $this->o_model->setupElog($o_di);
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
