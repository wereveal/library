<?php

namespace Wer\GuideBundle\Tests\Model;

use Wer\GuideBundle\Model\WerItem;

class WerItemTest extends \PHPUnit_Framework_TestCase
{
    public function testReadItem()
    {
        $o_item = new WerItem();
        $results1 = $o_item->readItem();
        $results2 = $o_item->readItem(
            array(
                ':item_name'=>'Andalusia Family Cafe',
                ':item_active'=>'1',
                ':item_old_id'=>1280
            ),
            'OR'
        );

        $expected = 'Andalusia Family Cafe';
        $results1_name = $results1[0]['item_name'];
        $results2_name = $results2[0]['item_name'];

        $this->assertEquals($results1_name, $expected);
        $this->assertEquals($results2_name, $expected);
    }
    public function testReadItemByOldItemId()
    {
        $o_item = new WerItem();
        $results = $o_item->readItemByOldItemId(1280);
        $item_name = $results['item_name'];
        $expected = 'Andalusia Family Cafe';
        $this->assertEquals($item_name, $expected);
    }
    public function testSetRequiredItemKeys()
    {
        $o_item = new WerItem();
        $current_timestamp = date('Y-m-d H:i:s');
        $item_name = 'Test' . mt_rand();
        $item_old_id = mt_rand(0,4000);
        $a_full_keys = array(
            ':item_name'       => $item_name,
            ':item_created_on' => $current_timestamp,
            ':item_updated_on' => $current_timestamp,
            ':item_active'     => 1,
            ':item_old_id'     => $item_old_id
        );
        $a_partial_ok_keys = array(
            ':item_name'       => $item_name,
            ':item_old_id'     => $item_old_id
        );
        $a_bad_keys = array(
            ':item_old_id'       => $item_old_id
        );
        $a_too_many_keys = array(
            ':item_name'       => $item_name,
            ':item_created_on' => $current_timestamp,
            ':item_updated_on' => $current_timestamp,
            ':item_active'     => 1,
            ':item_old_id'     => $item_old_id,
            ':item_not_needed' => 'this should not be here'
        );
        $results1 = $o_item->setRequiredItemKeys($a_full_keys);
        $results2 = $o_item->setRequiredItemKeys($a_partial_ok_keys);
        $results3 = $o_item->setRequiredItemKeys($a_bad_keys);
        $results4 = $o_item->setRequiredItemKeys($a_too_many_keys);
        $results2_created_on = $results2[':item_created_on'];

        $this->assertEquals($a_full_keys, $results1);
        $this->assertGreaterThanOrEqual($current_timestamp, $results2_created_on);
        $this->assertEquals(false, $results3);
        $this->assertEquals($a_full_keys, $results4);
    }
    public function testCreateItem()
    {
        $o_item = new WerItem();
        $current_timestamp = date('Y-m-d H:i:s');
        $item_name = 'Test' . mt_rand();
        $item_old_id = mt_rand(0,4000);
        $a_full_keys = array(
            ':item_name'       => $item_name,
            ':item_created_on' => $current_timestamp,
            ':item_updated_on' => $current_timestamp,
            ':item_active'     => 1,
            ':item_old_id'     => $item_old_id
        );
        $a_partial_ok_keys = array(
            ':item_name'       => $item_name,
            ':item_old_id'     => $item_old_id
        );
        $a_bad_keys = array(
            ':item_old_id'       => $item_old_id
        );
        $a_too_many_keys = array(
            ':item_name'       => $item_name,
            ':item_created_on' => $current_timestamp,
            ':item_updated_on' => $current_timestamp,
            ':item_active'     => 1,
            ':item_old_id'     => $item_old_id,
            ':item_not_needed' => 'this should not be here'
        );
        $results1 = $o_item->createItem($a_full_keys);
        $results2 = $o_item->createItem($a_partial_ok_keys);
        $results3 = $o_item->createItem($a_bad_keys);
        $results4 = $o_item->createItem($a_too_many_keys);

        $this->assertGreaterThanOrEqual(1, $results1);
        $this->assertGreaterThan($results1, $results2);
        $this->assertEquals(false, $results3);
        $this->assertGreaterThan($results2, $results4);

    }
    public function testUpdateItem()
    {
        $o_item = new WerItem();
        $current_timestamp = date('Y-m-d H:i:s');
        $item_name1 = 'Test Name 1';
        $item_name2 = 'Testy Name 2';
        $item_name3 = 'Test Name ' . mt_rand(0, 20);
        $item_old_id = mt_rand(0, 9999);
        $item_id = 2;
        $a_full_keys = array(
            ':item_id'         => $item_id,
            ':item_name'       => $item_name1,
            ':item_created_on' => '2013-03-30 16:20:50',
            ':item_updated_on' => $current_timestamp,
            ':item_active'     => 0,
            ':item_old_id'     => $item_old_id
        );
        $a_partial_ok_keys = array(
            ':item_id'         => $item_id,
            ':item_name'       => $item_name2,
            ':item_active'     => 1
        );
        $a_bad_keys = array(
            ':item_name'       => $item_name1
        );
        $a_too_many_keys = array(
            ':item_id'         => $item_id,
            ':item_name'       => $item_name3,
            ':item_created_on' => '2013-03-30 16:20:50',
            ':item_updated_on' => $current_timestamp,
            ':item_active'     => 1,
            ':item_old_id'     => $item_old_id,
            ':item_not_needed' => 'this should not be here'
        );
        $results1 = $o_item->updateItem($a_full_keys);
        $results2 = $o_item->updateItem($a_partial_ok_keys);
        $results3 = $o_item->updateItem($a_bad_keys);
        $results4 = $o_item->updateItem($a_too_many_keys);

        $this->assertEquals(true, $results1);
        $this->assertEquals(true, $results2);
        $this->assertEquals(false, $results3);
        $this->assertEquals(true, $results4);
    }
    public function testCreateCategoryItem()
    {
        $o_item = new WerItem();
        $a_full_keys = array(
            ':ci_category_id' => 1,
            ':ci_item_id'     => 1,
            ':ci_order'       => 1
        );
        $a_partial_ok_keys = array(
            ':ci_category_id' => 1,
            ':ci_item_id'     => 2
        );
        $a_bad_keys = array(
            ':ci_order' => 1
        );
        $a_too_many_keys = array(
            ':ci_category_id' => 1,
            ':ci_item_id'     => 3,
            ':ci_order'       => 3,
            ':do_not_use'     => 'fred'
        );

        $results1 = $o_item->createCategoryItem($a_full_keys);
        $results2 = $o_item->createCategoryItem($a_partial_ok_keys);
        $results3 = $o_item->createCategoryItem($a_bad_keys);
        $results4 = $o_item->createCategoryItem($a_too_many_keys);

        $this->assertGreaterThanOrEqual(1, $results1);
        $this->assertGreaterThan($results1, $results2);
        $this->assertEquals(false, $results3);
        $this->assertGreaterThan($results2, $results4);

    }
}
