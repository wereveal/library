<?php
namespace Ritc\Library\Tests;

use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\Tester;
use Ritc\Library\Core\Tpl;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\ConfigAdminModel;

class ConfigAdminModelTester extends Tester
{
    protected $a_test_order;
    protected $a_test_values = array();
    protected $failed_subtests;
    protected $failed_test_names = array();
    protected $failed_tests = 0;
    protected $new_id;
    protected $num_o_tests = 0;
    protected $o_elog;
    protected $passed_subtests;
    protected $passed_test_names  = array();
    protected $passed_tests = 0;
    protected $private_properties;
    private $o_config;
    private $o_db;
    private $o_twig;

    public function __construct(DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->o_db     = $o_db;
        $this->o_config = new ConfigAdminModel($this->o_db);
        $o_tpl          = new Tpl('twig_config.php');
        $this->o_twig   = $o_tpl->getTwig();
    }
    public function renderResults(array $a_result_values = array())
    {
        $o_vh = new ViewHelper();
        if (count($a_result_values) == 0) {
            return $this->o_twig->render(
                '@pages/error.twig',
                [
                    'description' => 'An error has occurred.',
                    'public_dir'  => '',
                    'a_message' => [
                        'message'       => 'No results were available.',
                        'message_class' => '',
                        'image_src'     => '',
                        'alt_text'      => '',
                        'image_class'   => '',
                        'extras'        => ''
                    ],
                    'site_url' => '',
                    'rights_holder' => ''
                ]
            );
        }
    }
    /**
     *  Runs tests where method ends in Tester.
     *  Extends the runTests method in abstract Tester.
     *  @param string $class_name name of the class to be tested
     *  @param array $a_test_order optional, if provided it ignores
     *      the class property $a_test_order and won't try to build one
     *      from the class methods.
     *  @return int $failed_tests
     **/
    public function runTests($class_name = 'ConfigAdminModel', array $a_test_order = array())
    {
        if ($class_name == '') {
            if (substr(__CLASS__, -5) == 'Tests') {
                $class_name = str_replace('Tests', '', __CLASS__);
            }
            elseif (substr(__CLASS__, -6) == 'Tester') {
                $class_name = str_replace('Tester', '', __CLASS__);
            }
            else {
                return 999;
            }

        }
        if (count($a_test_order) === 0) {
            if (count($this->a_test_order) === 0) {
                $this->logIt('Didnt have a test order.', LOG_OFF, __METHOD__ . '.' . __LINE__);
                $o_ref = new \ReflectionClass($class_name);
                $a_methods = $o_ref->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($a_methods as $a_method) {
                    switch($a_method->name) {
                        case '__construct':
                        case '__set':
                        case '__get':
                        case '__isset':
                        case '__unset':
                        case '__clone':
                            break;
                        default:
                            if (substr($a_method->name, -6) == 'Tester') {
                                $a_test_order[] = $a_method->name;
                            }
                    }
                }
            }
            else {
                $a_test_order = $this->a_test_order;
            }
        }
        $this->logIt(
            "Before -- num_o_tests: '{$this->num_o_tests}' passed tests: '{$this->passed_tests}' failed tests: '{$this->failed_tests}' test names: "
            . var_export($this->failed_test_names, TRUE),
            LOG_OFF,
            __METHOD__ . '.' . __LINE__
        );
        $failed_tests = 0;
        foreach ($a_test_order as $method_name) {
            if (substr($method_name, -6) == 'Tester') {
                $tester_name = $method_name;
                $method_name = $this->shortenName($method_name);
            } else {
                $tester_name = $method_name . 'Tester';
            }
            if ($this->isPublicMethod($class_name, $tester_name)) {
                if ($this->$tester_name()) {
                    $this->passed_tests++;
                    $this->passed_test_names[] = $method_name;
                } else {
                    $failed_tests++;
                    $this->failed_tests++;
                    $this->failed_test_names[] = $method_name;
                }
                $this->num_o_tests++;
            }
        }
        $this->logIt("num_o_tests: {$this->num_o_tests} passed tests: {$this->passed_tests} failed tests: {$this->failed_tests} test names: "
            . var_export($this->failed_test_names, true),
            LOG_OFF,
            __METHOD__ . '.' . __LINE__
        );
        return $failed_tests;
    }

    ### TESTS ###
    public function createTester()
    {
        $bad_results = false;
        $results1 = $this->o_config->create($this->a_test_values['new_config']);
        $results2 = $this->o_config->create();
        $results3 = $this->o_config->create(array('bad_stuff' => 'bad_stuff'));
        $results4 = $this->o_config->create($this->a_test_values['new_config']);
        if ($results1 === false) {
            $bad_results = true;
            $this->setSubfailure('create', 'valid config');
        }
        else {
            $this->new_id = $results1;
            $this->logIt('New ID' . var_export($this->new_id, TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        }
        if ($results2 !== false) {
            $bad_results = true;
            $this->setSubfailure('create', 'no config');
        }
        if ($results3 !== false) {
            $bad_results = true;
            $this->setSubfailure('create', 'bad config');
        }
        if ($results4 !== false) {
            $bad_results = true;
            $this->setSubfailure('create', 'duplicate config');
        }
        if ($bad_results) {
            return false;
        }
        return true;
    }
    public function readTester()
    {
        $bad_results = false;
        $results1 = $this->o_config->read();
        $results2 = $this->o_config->read("USER_ID");
        $results3 = $this->o_config->read("badValue");
        $results4 = $this->o_config->read(1);
        if ($results1 === false || $this->compareArrays($this->a_test_values['all_configs'], $results1) === false) {
            $bad_results = true;
            $this->setSubfailure('read', 'find all configs');
        }
        if ($results2 === false || $this->compareArrays($this->a_test_values['single_config'], $results2) === false) {
            $bad_results = true;
            $this->setSubfailure('read', 'find single by name');
        }
        if ($results3 !== false) {
            $bad_results = true;
            $this->setSubfailure('read', 'bad config name');
        }
        if ($results4 === false || $this->compareArrays($this->a_test_values['single_config'], $results4) === false) {
            $bad_results = true;
            $this->setSubfailure('read', 'find single by id');
        }
        if ($bad_results) {
            return false;
        }
        return true;
    }
    public function updateTester()
    {
        $bad_results = false;
        $a_config = $this->a_test_values['modified_config'];
        $a_config['config_id'] = $this->new_id;
        $results1 = $this->o_config->update($a_config);
        if ($results1 === false) {
            $bad_results = true;
            $this->setSubfailure('update', 'modify config false');
        }
        else {
            $return1 = $this->o_config->read($a_config['config_name']);
            if ($return1['config_value'] !== $a_config['config_value']) {
                $bad_results = true;
                $this->setSubfailure('update', 'modify config not modified');
            }
        }
        $results2 = $this->o_config->update();
        if ($results2 !== false) {
            $bad_results = true;
            $this->setSubfailure('update', 'no config returned true');
        }
        $results3 = $this->o_config->update(array('bad_config_stuff' => 'bad_config_stuff'));
        if ($results3 !== false) {
            $bad_results = true;
            $this->setSubfailure('update', 'bad config info returned true');
        }
        if ($bad_results) {
            return false;
        }
        return true;
    }
    public function deleteTester()
    {
        $config_id = $this->new_id;
        $results1 = $this->o_config->delete($config_id);
        $results2 = $this->o_config->delete();
        $results3 = $this->o_config->delete(100);
        $bad_results = false;
        if ($results1 !== true) {
            $this->setSubfailure('delete', 'valid config returned false');
            $bad_results = true;
        }
        if ($results2 === true) {
            $bad_results = true;
            $this->setSubfailure('delete', 'blank config returned true');
        }
        if ($results3 === true) {
            $bad_results = true;
            $this->setSubfailure('delete', 'invalid config returned true');
        }
        if ($bad_results) {
            return false;
        }
        return true;
    }
    public function makeValidNameTester()
    {
        $results1 = $this->o_config->makeValidName('<a href="http://go.to.bad.place/">my name</a>');
        $results2 = $this->o_config->makeValidName('My Name 123');
        $results3 = $this->o_config->makeValidName('My&Name#--23');
        $good_results = true;
        if ($results1 != 'MY_NAME') {
            $this->setSubfailure('makeValidName', "Test 1 Returned {$results1}");
            $good_results = false;
        }
        if ($results2 != 'MY_NAME') {
            $this->setSubfailure('makeValidName', "Test 2 Returned {$results2}");
            $good_results = false;
        }
        if ($results3 != 'MY_NAME') {
            $this->setSubfailure('makeValidName', "Test 3 Returned {$results3}");
            $good_results = false;
        }
        return $good_results;
    }

    ### Utility ###
    /**
     *  Checks to see if a method is public.
     *  Fixes method names that end in Tester.
     *  Overriding method from abstact Tester class.
     *  @param string $class_name required defaults to ''
     *  @param string $method_name required defaults to ''
     *  @return bool true or false
    **/
    public function isPublicMethod($class_name = '', $method_name = '')
    {
        if ($class_name == '' || $method_name == '') { return false; }
        if (substr($method_name, -6) == 'Tester') {
            $method_name = $this->shortenName($method_name);
        }
        $o_ref = new \ReflectionClass('Ritc\FtpAdmin\Models\AppConfig');
        $o_method = $o_ref->getMethod($method_name);
        return $o_method->IsPublic();
    }

}
