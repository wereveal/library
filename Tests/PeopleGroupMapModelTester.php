<?php
/**
 * @brief     Tests the Group Model Class.
 * @ingroup   ritc_library lib_tests
 * @file      PeopleGroupMapModelTests.php
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
use Ritc\Library\Models\PeopleGroupMapModel;

/**
 * Class PeopleGroupMapModelTests.
 * @class   PeopleGroupMapModelTests
 * @package Ritc\Library\Tests
 */
class PeopleGroupMapModelTests extends Tester
{
    protected $a_test_order;
    protected $a_test_values = array();
    protected $failed_subtests;
    protected $failed_test_names = array();
    protected $failed_tests = 0;
    protected $new_id;
    protected $num_o_tests = 0;
    protected $passed_subtests;
    protected $passed_test_names  = array();
    protected $passed_tests = 0;
    private $o_db;
    private $o_elog;
    private $o_ugm;

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
        $this->o_ugm = new PeopleGroupMapModel($this->o_db);
    }

    ### Tests ###
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
