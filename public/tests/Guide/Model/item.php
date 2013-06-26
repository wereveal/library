<?php
namespace Wer\Guide\Tests\Model;

$config_dir = $_SERVER['DOCUMENT_ROOT'] . '/../src/Wer/Framework/Resources/config';

include $config_dir . '/autoload.php';
include $config_dir . '/setup.php';

$o_item = new ItemTester();
$o_item->setTestOrder(
    array(
        'readItem',
        'readItemCount',
        'readItemData',
        'readItemIds',
        'readItemByOldItemId',
        'readItemFeatured',
        'readItemByName',
        'readItemByNameFirstLetter',
        'readItemBySection',
        'readItemByCategory',
        'readItemRandom',
        'readFieldById',
        'readFieldByName',
        'readFieldByOldId',
        'requiredItemKeys',
        'requiredItemDataKeys',
        'createItem',
        'createCategoryItem',
        'createItemData',
        'updateItem',
        'updateItemData',
        'deleteCategoryItem',
        'deleteItemData',
        'deleteItem'
    )
);

$o_item->setTestValues(
    array(
        'field' => array(
            'field_id' => 1,
            'field_type_id' => 2,
            'field_name' => 'about',
            'field_old_id' => 13,
        ),
        'old_item' => array(
            'item_id' => 919,
            'item_name' => "Bruegger's Bagel Bakery",
            'item_old_id' => 37,
            'item_featured' => 1
        ),
        'first_letter' => array(
            'item_id' => '1682',
            'item_name' => "Bad Boy'z Pizza (Davenport)",
            'item_old_id' => '2305'
        ),
        'item_data' => array(
            'city' => 'Bettendorf',
            'federal_state' => 'IA',
            'latitude' => '41.5360826',
            'longitude' => '-90.5210763'
        ),
        'category_item' => array(
            'ci_category_id' => 7,
            'ci_item_id' => 919
        ),
        'section_item' => array(
            'sc_sec_id' => 1,
            'sc_cat_id' => 7,
            'ci_category_id' => 7,
            'ci_item_id' => 919
        ),
        'new_item' => array(
            'item_id' => 1,
            'item_name' => 'Test Item',
            'item_active' => 1,
            'item_featured' => 1,
            'item_old_id' => 0
        ),
        'new_category_item' => array(
            'ci_id' => 1,
            'ci_category_id' => 7,
            'ci_item_id' => 1,
            'ci_order' => 1
        ),
        'new_item_data' => array(
            'data_id' => 1,
            'data_item_id' => 1,
            'data_field_id' => 13,
            'data_text' => 'Bettendorf'
        )
    )
);
$failed_tests = $o_item->runTests('Wer\Guide\Tests\ItemTester');
print $o_item->returnTestResults(true, true);

?>
