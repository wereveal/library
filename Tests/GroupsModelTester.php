<?php
/**
 * Class GroupsModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
use Ritc\Library\Models\GroupsModel;

/**
 * Tests the Group Model Class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2016-03-05 10:44:05
 * @change_log
 * - v1.0.0-alpha.0 - Initial rewrite version        - 2016-03-05 wer
 * - v0.1.0         - Initial version                - unknown wer
 * @todo Everything
 */
class GroupsModelTester extends Tester
{
    /** @var int  */
    protected $new_id;
    /** @var DbModel */
    private $o_db;
    /** @var Di */
    private $o_di;
    /** @var GroupsModel */
    private $o_group;

    /**
     * GroupsModelTester constructor.
     * @param array  $a_test_order
     * @param string $db_config
     * @throws \Ritc\Library\Exceptions\FactoryException
     * @throws \Ritc\Library\Exceptions\ServiceException
     */
    public function __construct(array $a_test_order = array(), $db_config = 'db_config.php')
    {
        $this->a_test_order = $a_test_order;
        $this->o_elog = Elog::start();
        $o_di = new Di();
        $o_di->set('elog', $this->o_elog);
        $this->o_di = $o_di;
        $o_pdo = PdoFactory::start($db_config, 'rw', $o_di);
        if ($o_pdo !== false) {
            $this->o_db = new DbModel($o_pdo, $db_config);
        }
        else {
            $this->o_elog->write('Could not connect to the database', LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
        $this->o_group = new GroupsModel($this->o_db);
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
