<?php
namespace Wer\Guide\Tests;

include dirname($_SERVER['DOCUMENT_ROOT']) . '/app/setup.php';

$o_cat = new CategoryTester();
$o_cat->setTestOrder(
    array(
        'readCategory',
        'readCatBySec'
    )
);

$o_cat->setTestValues(
    array(
        'readCatBySec1' => '',
        'readCatBySec1results' => array(
            'sec_id' => 1,
            'sec_name' => 'Dining Guide',
            'sec_title' => 'QC Dining Guide | Winter/Spring 2012-13',
            'sec_order' => 1,
            'sec_active' => 1,
            'sec_old_cat_id' => 59,
            'cat_id' => 18,
            'cat_name' => 'Other',
            'cat_order' => 1,
            'cat_active' => 1,
            'cat_old_cat_id' => 125
        ),
        'readCatBySec2' => 1,
        'readCategory1' => '',
        'readCategory1Params' => array(
            'order_by' => 'cat_order',
            'limit_to' => '1'
        ),
        'readCategory1results' => array(
            'cat_id' => 18,
            'cat_name' => 'Other',
            'cat_order' => 1,
            'cat_active' => 1,
            'cat_old_cat_id' => 125
        ),
        'readCategory2' => array('cat_id' => 7),
        'readCategory2results' => array(
            'cat_id' => 7,
            'cat_name' => 'Coffee/Cafe',
            'cat_order' => 13,
            'cat_active' => 1,
            'cat_old_cat_id' => 4
        ),
        'readCategory3' => array('cat_name' => 'Asian'),
        'readCategory3results' => array(
            'cat_id' => 8,
            'cat_name' => 'Asian',
            'cat_order' => 11,
            'cat_active' => 1,
            'cat_old_cat_id' => 7
        )
    )
);
$failed_tests = $o_cat->runTests('Wer\Guide\Tests\CategoryTester');
print $o_cat->returnTestResults(true, true);

?>
