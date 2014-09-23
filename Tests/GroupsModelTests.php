<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Abstracts\Tester;
use Ritc\Library\Core\DbFactory;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\Elog;
use Ritc\Library\Models\GroupsModel;

class GroupsModelTests extends Tester
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
    private $o_group;

    public function __construct(array $a_test_order = array(), $db_config = 'db_config.php')
    {
        $this->a_test_order = $a_test_order;
        $this->o_elog = Elog::start();
        $o_dbf = DbFactory::start($db_config, 'rw');
        $o_pdo = $o_dbf->connect();
        if ($o_pdo !== false) {
            $this->o_db = new DbModel($o_pdo);
        }
        else {
            $this->o_elog->write('Could not connect to the database', LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
        $this->o_group = new GroupsModel($this->o_db);
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
