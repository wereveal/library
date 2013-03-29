<?php

namespace Wer\GuideBundle\Tests\Model;

use Wer\GuideBundle\Model\WerSection;

class WerSectionTest extends \PHPUnit_Framework_TestCase
{
    public $a_good_query_values = array(
        ':sec_name' => 'Test',
        ':sec_description' => 'Description',
        ':sec_image' => 'fred.png',
        ':sec_order' => '2',
        ':sec_active' => 1,
        ':sec_old_cat_id' => '14'
    );
    public $a_bad_query_values = array(
        ':sec_name' => '',
        ':sec_image' => '',
        ':sec_order' => '',
        ':sec_old_cat_id' => '14'
    );
    public $a_ok_query_values = array(
        ':sec_name' => 'Test2'
    );

    public function testCreateSection()
    {
        $o_sec = new WerSection();
        $good_values = $this->a_good_query_values;
        $bad_values = $this->a_bad_query_values;
        $ok_values = $this->a_ok_query_values;
        $good_values[':sec_name'] = $good_values[':sec_name'] . mt_rand();
        $ok_values[':sec_name']   = $ok_values[':sec_name'] . mt_rand();

        $results1 = $o_sec->createSection($good_values);
        $results2 = $o_sec->createSection($bad_values);
        $results3 = $o_sec->createSection($ok_values);

        $this->assertGreaterThan(0, $results1);
        $this->assertEquals(false, $results2);
        $this->assertGreaterThan(0, $results3);
    }
    public function testReadSectionByOldCatId()
    {
        $o_sec = new WerSection();
        $expected = 14;
        $results = $o_sec->readSectionByOldCatId($expected);
        $results2 = $o_sec->readSectionByOldCatId(4);

        $this->assertEquals($expected, $results['sec_old_cat_id']);
        $this->assertEquals(false, $results2);
    }
    public function testUpdateSection()
    {
        $o_sec = new WerSection();
        $good_query_values = $this->a_good_query_values;
        $good_query_values[':sec_id'] = 1;
        $good_query_values[':sec_description'] = 'Description ' . mt_rand();
        $good_query_values[':sec_order'] = mt_rand(0,10);
        $ok_query_values = $this->a_ok_query_values;
        $ok_query_values[':sec_id'] = 2;
        $ok_query_values[':sec_description'] = 'Description ' . mt_rand();

        $results1 = $o_sec->updateSection($good_query_values);
        $results2 = $o_sec->updateSection($this->a_bad_query_values);
        $results3 = $o_sec->updateSection($ok_query_values);

        $this->assertEquals(true, $results1);
        $this->assertEquals(false, $results2);
        $this->assertEquals(true, $results3);
    }
    public function testSetRequiredSectionKeys()
    {
    }
}
