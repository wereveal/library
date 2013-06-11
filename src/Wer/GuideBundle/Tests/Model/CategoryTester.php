<?php
namespace Wer\GuideBundle\Tests;

use Wer\GuideBundle\Model\Category;
use Wer\FrameworkBundle\Library\Elog;
use Wer\FrameworkBundle\Library\Files;
use Wer\FrameworkBundle\Library\Html;
use Wer\FrameworkBundle\Library\Tester;

class CategoryTester extends Tester
{
    protected $a_test_order;
    protected $a_test_values = array();
    protected $failed_test_names;
    protected $failed_tests = 0;
    protected $num_o_tests;
    protected $o_elog;
    protected $o_files;
    protected $o_cat;
    protected $o_html;
    protected $passed_test_names  = array();
    protected $passed_tests = 0;
    public function __construct()
    {
        $this->o_elog  = Elog::start();
        $this->o_html  = new Html;
        $this->o_files = new Files('test_results.tpl', 'templates', 'default', 'Wer\Framework');
        $this->o_cat   = new Category;
    }
    public function readCategoryTester()
    {
        $results1 = $this->o_cat->readCategory($this->a_test_values['readCategory1'], $this->a_test_values['readCategory1Params']);
        $this->o_elog->write('' . var_export($results1 , TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if($this->compareArrays($this->a_test_values['readCategory1results'], $results1) === false) {
            $this->setSubfailure('readCategory', 'test1');
            return false;
        }
        $results2 = $this->o_cat->readCategory($this->a_test_values['readCategory2']);
        $this->o_elog->write('' . var_export($results2 , TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if($this->compareArrays($this->a_test_values['readCategory2results'], $results2) === false) {
            $this->setSubfailure('readCategory', 'test2');
            return false;
        }
        $results3 = $this->o_cat->readCategory($this->a_test_values['readCategory3']);
        $this->o_elog->write('' . var_export($results3 , TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if($this->compareArrays($this->a_test_values['readCategory3results'], $results3) === false) {
            $this->setSubfailure('readCategory', 'test3');
            return false;
        }
        return true;
    }
    public function readCatBySecTester()
    {
        $results1 = $this->o_cat->readCatBySec($this->a_test_values['readCatBySec1']);
        $this->o_elog->write('' . var_export($results1[0] , TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if($this->compareArrays($this->a_test_values['readCatBySec1results'], $results1[0]) === false) {
            $this->setSubfailure('readCatBySec', 'test1');
            return false;
        }
        $results2 = $this->o_cat->readCatBySec($this->a_test_values['readCatBySec2']);
        $this->o_elog->write('results2' . var_export($results2 , TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if($this->compareArrays($this->a_test_values['readCatBySec1results'], $results1[0]) === false) {
            $this->setSubfailure('readCatBySec', 'test2');
            return false;
        }
        return true;
    }
}
