<?php /** @noinspection UnusedConstructorDependenciesInspection */

/**
 * Class GroupsModelTester
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Exceptions\FactoryException;
use Ritc\Library\Exceptions\ServiceException;
use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
use Ritc\Library\Models\GroupsModel;

/**
 * Tests the Group Model Class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-12-01 14:11:20
 * @change_log
 * - 1.0.0-alpha.1  - php 8 update                              - 2021-12-01 wer
 * - 1.0.0-alpha.0  - Initial rewrite version                   - 2016-03-05 wer
 * - 0.1.0          - Initial version                           - unknown wer
 * @todo Everything
 */
class GroupsModelTester extends Tester
{
    /** @var int  */
    protected int $new_id;
    /** @var DbModel */
    protected DbModel $o_db;
    /** @var Di */
    protected Di $o_di;
    /** @var GroupsModel */
    protected GroupsModel $o_group;

    /**
     * GroupsModelTester constructor.
     *
     * @param array  $a_test_order
     * @param string $db_config
     * @throws FactoryException
     * @throws ServiceException
     */
    public function __construct(array $a_test_order = array(), string $db_config = 'db_config.php')
    {
        $this->a_test_order = $a_test_order;
        $this->o_elog = Elog::start();
        $o_di = new Di();
        $o_di->set('elog', $this->o_elog);
        $this->o_di = $o_di;
        $o_pdo = PdoFactory::start($db_config, 'rw');
        if ($o_pdo !== false) {
            $this->o_db = new DbModel($o_pdo, $db_config);
            $this->o_group = new GroupsModel($this->o_db);
        }
        else {
            $this->o_elog->write('Could not connect to the database', LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
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
