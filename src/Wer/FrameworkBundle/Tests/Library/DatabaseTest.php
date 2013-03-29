<?php

namespace Wer\FrameworkBundle\Tests\Library;

use Wer\FrameworkBundle\Library\Database;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testFindMissingKeys()
    {
        $o_db = Database::start();
        $a_required_keys = array(':test', 'test2', 'test3');
        $a_correct_values = array('test' => 'test', ':test2' => 'test2', ':test3' => 'test3');
        $a_missing_values = array(':test' => 'test', 'test2' => '');
        $a_too_many_values = array('test' => '', 'test2' => '', 'test3' => '', 'test4' => '');

        $a_no_values = $o_db->findMissingKeys($a_required_keys, $a_correct_values);
        $a_one_array = $o_db->findMissingKeys($a_required_keys, $a_missing_values);
        $a_unknown   = $o_db->findMissingKeys($a_required_keys, $a_too_many_values);
        $a_expected_test = array();
        $a_expected_test2 = array('test3');
        $this->assertEquals($a_expected_test, $a_no_values);
        $this->assertEquals($a_expected_test2, $a_one_array);
        $this->assertEquals($a_expected_test, $a_unknown);
    }
    public function testRemoveBadKeys()
    {
        $o_db = Database::start();
        $a_required_keys = array(':test', 'test2', 'test3');
        $a_correct_values = array('test' => 'test', ':test2' => 'test2', ':test3' => 'test3');
        $a_too_many_values = array('test' => 'test', ':test2' => 'test2', ':test3' => 'test3', 'test4' => '');
        $a_missing_values = array('test' => 'test', ':test2' => 'test2');

        $a_result1 = $o_db->removeBadKeys($a_required_keys, $a_correct_values);
        $a_result2 = $o_db->removeBadKeys($a_required_keys, $a_too_many_values);
        $a_result3 = $o_db->removeBadKeys($a_required_keys, $a_missing_values);

        $this->assertEquals($a_correct_values, $a_result1);
        $this->assertEquals($a_correct_values, $a_result2);
        $this->assertEquals($a_missing_values, $a_result3);
    }
}
