<?php
/**
 * Class PeopleGroupMapModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
use Ritc\Library\Models\PeopleGroupMapModel;

/**
 * Tests the Group Model Class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2016-03-05 10:44:05
 * @todo    Rewrite the whole thing
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial rewrite version        - 2016-03-05 wer
 * - v0.1.0         - Initial version                - unknown wer
 */
class PeopleGroupMapModelTester extends Tester
{
    /** @var int */
    protected $new_id;
    /** @var DbModel */
    private $o_db;
    /** @var \Ritc\Library\Services\Di  */
    private $o_di;
    /** @var PeopleGroupMapModel */
    private $o_ugm;

    /**
     * PeopleGroupMapModelTester constructor.
     * @param array $a_test_order
     * @param string $db_config
     */
    public function __construct(array $a_test_order = array(), $db_config = 'db_config.php')
    {
        $this->a_test_order = $a_test_order;
        $this->o_elog = Elog::start();
        $this->o_di = new Di();
        $this->o_di->set('elog', $this->o_elog);
        $o_pdo = PdoFactory::start($db_config, 'rw', $this->o_di);
        if ($o_pdo !== false) {
            $this->o_db = new DbModel($o_pdo, $db_config);
        }
        else {
            $this->o_elog->write('Could not connect to the database', LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
        $this->o_ugm = new PeopleGroupMapModel($this->o_db);
    }

    ### Tests ###
    /**
     * @return bool
     */
    public function createTester()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function readTester()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function updateTester()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function deleteTester()
    {
        return false;
    }
}
