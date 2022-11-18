<?php
/**
 * Class PeopleGroupMapModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Exceptions\FactoryException;
use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Models\PeopleGroupMapModel;
use Ritc\Library\Traits\TesterTraits;

/**
 * Tests the Group Model Class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-12-01 14:21:34
 * @todo    Rewrite the whole thing
 * @change_log
 * - 1.0.0-alpha.1  - updated for php 8 standard    - 2021-12-01 wer
 * - 1.0.0-alpha.0  - Initial rewrite version       - 2016-03-05 wer
 * - 0.1.0          - Initial version               - unknown wer
 */
class PeopleGroupMapModelTester
{
    use TesterTraits;
    /** @var int */
    protected int $new_id;
    /** @var DbModel */
    protected DbModel $o_db;
    /** @var Di */
    protected Di $o_di;
    /** @var PeopleGroupMapModel */
    protected PeopleGroupMapModel $o_ugm;

    /**
     * PeopleGroupMapModelTester constructor.
     *
     * @param array  $a_test_order
     * @param string $db_config
     */
    public function __construct(array $a_test_order = array(), string $db_config = 'db_config.php')
    {
        $this->a_test_order = $a_test_order;
        $this->o_di = new Di();
        try {
            $o_pdo = PdoFactory::start($db_config);
            $this->o_db  = new DbModel($o_pdo, $db_config);
            $this->o_ugm = new PeopleGroupMapModel($this->o_db);
        }
        catch (FactoryException) {
            die('Unable to create the pdo instance.');
        }
    }

    ### Tests ###
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
