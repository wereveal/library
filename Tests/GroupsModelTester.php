<?php
/**
 * @brief     Tests the Group Model Class.
 * @ingroup   lib_tests
 * @file      GroupsModelTests.php
 * @namespace Ritc\Library\Tests
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-03-05 10:44:05
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial rewrite version        - 2016-03-05 wer
 * - v0.1.0         - Initial version                - unknown wer
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Basic\Tester;
use Ritc\Library\Factories\PdoFactory;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Elog;
use Ritc\Library\Models\GroupsModel;

/**
 * Class GroupsModelTests.
 * @class   GroupsModelTests
 * @package Ritc\Library\Tests
 */
class GroupsModelTests extends Tester
{
    /** @var array */
    protected $a_test_order;
    /** @var array */
    protected $a_test_values = array();
    /** @var int */
    protected $failed_subtests;
    /** @var array */
    protected $failed_test_names = array();
    /** @var int */
    protected $failed_tests = 0;
    /** @var int  */
    protected $new_id;
    /** @var int */
    protected $num_o_tests = 0;
    /** @var */
    protected $passed_subtests;
    /** @var array */
    protected $passed_test_names  = array();
    /** @var int */
    protected $passed_tests = 0;
    /** @var DbModel */
    private $o_db;
    /** @var object */
    private $o_elog;
    /** @var GroupsModel */
    private $o_group;

    /**
     * GroupsModelTests constructor.
     * @param array $a_test_order
     * @param string $db_config
     */
    public function __construct(array $a_test_order = array(), $db_config = 'db_config.php')
    {
        $this->a_test_order = $a_test_order;
        $this->o_elog = Elog::start();
        $o_pdo = PdoFactory::start($db_config, 'rw');
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
