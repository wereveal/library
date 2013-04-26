<?php

namespace Wer\GuideBundle\Tests\Model;

use Wer\GuideBundle\Model\Item;

class WerItemDataTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateItemData()
    {
        $o_item = new Item();
        include '/Users/wer/projects/guide/Symfony/web/assets/files/guide/item_5.php';
        foreach($a_item_5_data as $a_data) {
            error_log(var_export($a_data, true));
        }
    }
}
