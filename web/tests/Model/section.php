<?php
namespace Wer\Guide\Tests;

include dirname($_SERVER['DOCUMENT_ROOT']) . '/app/setup.php';

$o_section = new SectionTester();
$o_section->setTestOrder(
    array(
        'readSection'
    )
);

$o_section->setTestValues(
    array(
        'search_for1' => array(
            'sec_id' => 1,
        ),
        'search_parameters1' => '',
        'search_for2' => array(
            'sec_id' => 2,
        ),
        'search_parameters2' => array(
            'order_by' => 'sec_order'
        ),
        'search_for3' => array(
            'sec_id' => '1',
            'sec_old_cat_id' => '59',
            'sec_name' => 'QC Dining%'
        ),
        'search_parameters3' => array(
            'order_by' => 'sec_order DESC',
            'search_type' => 'OR',
            'limit_to' => '1',
            'starting_from' => '1',
            'comparison_type' => 'LIKE'
        ),
        'section_values' => array(
            'sec_id' => 1,
            'sec_order' => 1,
            'sec_active' => 1,
            'sec_old_cat_id' => 59,
            'sec_name' => 'QC Dining Guide | Winter/Spring 2012-13'
        ),
    )
);
$failed_tests = $o_section->runTests('Wer\Guide\Tests\SectionTester');
print $o_section->returnTestResults(true, true);

?>
