<?php
namespace Wer\Guide\Tests;

use Wer\Guide\Model\Section;
use Wer\Framework\Library\Elog;
use Wer\Framework\Library\Files;
use Wer\Framework\Library\Html;
use Wer\Framework\Library\Tester;

class SectionTester extends Tester
{
    protected $a_test_order;
    protected $a_test_values = array();
    protected $failed_test_names;
    protected $failed_tests = 0;
    protected $num_o_tests;
    protected $o_elog;
    protected $o_files;
    protected $o_section;
    protected $o_html;
    protected $passed_test_names  = array();
    protected $passed_tests = 0;
    public function __construct()
    {
        $this->o_elog     = Elog::start();
        $this->o_html     = new Html;
        $this->o_files    = new Files('test_results.tpl', 'templates', 'default', 'Wer\Framework');
        $this->o_section  = new Section;
    }
    public function readSectionTester()
    {
        $search_for1 = $this->a_test_values['search_for1'];
        $a_search_parameters1 = $this->a_test_values['search_parameters1'];
        $search_for2 = $this->a_test_values['search_for2'];
        $a_search_parameters2 = $this->a_test_values['search_parameters2'];
        $search_for3 = $this->a_test_values['search_for3'];
        $a_search_parameters3 = $this->a_test_values['search_parameters3'];
        $a_section_values = $this->a_test_values['section_values'];
        $results = $this->o_section->readSection($search_for1, $a_search_parameters1);
        if ($this->compareArrays($a_section_values, $results[0]) === false) {
            return false;
        }
        $results = $this->o_section->readSection($search_for2, $a_search_parameters2);
        if ($results != array()) {
            return false;
        }
        $results = $this->o_section->readSection($search_for3, $a_search_parameters3);
        if ($this->compareArrays($a_section_values, $results[0]) === false) {
            return false;
        }
        return true;
    }
}
