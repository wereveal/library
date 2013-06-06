<?php
namespace Wer\GuideBundle\Tests\Model;

use Wer\GuideBundle\Model\Item;
use Wer\FrameworkBundle\Library\Files;
use Wer\FrameworkBundle\Library\Html;
use Wer\FrameworkBundle\Library\Tester;

class ItemTester extends Tester
{
    protected $a_test_order;
    protected $a_test_values = array();
    protected $failed_test_names;
    protected $failed_tests = 0;
    protected $num_o_tests;
    protected $o_files;
    protected $o_item;
    protected $o_html;
    protected $passed_test_names  = array();
    protected $passed_tests = 0;
    public function __construct()
    {
        $this->o_html  = new Html;
        $this->o_files = new Files('test_results.tpl', 'templates', 'default', 'Wer\Framework');
        $this->o_item  = new Item;
    }
    public function readItemTester()
    {
        $a_item = $this->a_test_values['old_item'];
        $a_values = array(
            'item_id' => $a_item['item_id'],
            'item_name' => $a_item['item_name'],
            'item_old_id' => $a_item['item_old_id']
        );
        $a_results = $this->o_item->readItem($a_values);
        if ($a_results === false || !is_array($a_results) || count($a_results) == 0) {
            $this->setSubfailure('readItem', 'all three');
            return false;
        }
        $a_found_item = $a_results[0];
        return $this->compareArrays($a_item, $a_found_item);
    }
    public function readItemByCategoryTester()
    {
        $return_this = true;
        $a_category = $this->a_test_values['category_item'];
        $a_results = $this->o_item->readItemByCategory($a_category['ci_category_id']);
        if ($a_results === false || !is_array($a_results) || count($a_results) == 0) {
            $this->setSubfailure('readItemByCategory', 'default (featured) search');
            $return_this = false;
        }
        $a_search_params = array(
            'is_featured' => false,
            'is_random' => true,
            'limit_to' => 5
        );
        $a_results = $this->o_item->readItemByCategory($a_category['ci_category_id'], $a_search_params);
        if ($a_results === false || !is_array($a_results) || count($a_results) == 0) {
            $this->setSubfailure('readItemByCategory', 'random search');
            $return_this = false;
        }
        return $return_this;
    }
    public function readItemByNameTester()
    {
        $a_item = $this->a_test_values['old_item'];
        $a_results = $this->o_item->readItemByName($a_item['item_name']);
        if ($a_results === false || !is_array($a_results) || count($a_results) == 0) {
            return false;
        }
        $a_found_item = $a_results[0];
        return $this->compareArrays($a_item, $a_found_item);
    }
    public function readItemByNameFirstLetterTester()
    {
        $a_item = $this->a_test_values['first_letter'];
        $first_letter = substr($a_item['item_name'], 0, 1);
        $a_results = $this->o_item->readItemByNameFirstLetter($first_letter, 1);
         if ($a_results === false || $a_results == array()) {
            return false;
        }
        $a_found_item = $a_results[0];
        return $this->compareArrays($a_item, $a_found_item);
    }
    public function readItemByOldItemIdTester()
    {
        $a_item = $this->a_test_values['old_item'];
        $a_results = $this->o_item->readItemByOldItemId($a_item['item_old_id']);
        if ($a_results === false) {
            return false;
        }
        $a_found_item = $a_results[0];
        return $this->compareArrays($a_item, $a_found_item);
    }
    public function readItemBySectionTester()
    {
        $a_section = $this->a_test_values['section_item'];
        $a_results = $this->o_item->readItemBySection($a_section['sc_sec_id']);
        // error_log(var_export($a_results, true));
        if ($a_results === false || $a_results == array()) {
            $this->setSubfailure('readItemBySection', 'default search');
            return false;
        }
        $a_parms = array(
            'is_featured' => false,
            'is_random'   => true,
            'limit_to'    => 5
        );
        $a_results = $this->o_item->readItemBySection($a_section['sc_sec_id'], $a_parms);
        // error_log(var_export($a_results, TRUE));
        if ($a_results === false || count($a_results) != 5) {
            $this->setSubfailure('readItemBySection', 'random search');
            return false;
        }
        return true;
    }
    public function readItemCountTester()
    {
        $a_item = $this->a_test_values['old_item'];
        $a_search_pairs = array('item_name' => substr($a_item['item_name'], 0, 1) . '%');
        $a_search_params = array('comparison_type' => 'LIKE');
        $results = $this->o_item->readItemCount($a_search_pairs, $a_search_params);
        return false;
    }
    public function readItemFeaturedTester()
    {
        $a_item = $this->a_test_values['old_item'];
        $a_results = $this->o_item->readItemFeatured();
        if ($a_results === false || $a_results == array() || is_null($a_results)) {
            return false;
        }
        $a_found_item = $a_results[0];
        return $this->compareArrays($a_item, $a_found_item);
    }
    public function readItemRandomTester()
    {
        $a_results = $this->o_item->readItemRandom(10);
        if ($a_results === false || $a_results == array() || is_null($a_results) || count($a_results) < 10) {
            return false;
        }
        return true;
    }
    public function readItemIdsTester()
    {
        $a_results = $this->o_item->readItemIds();
        if ($a_results === false || $a_results == array() || is_null($a_results) || count($a_results) < 10) {
            return false;
        }
        $a_results = $this->o_item->readItemIds(8);
        if ($a_results === false || $a_results == array() || is_null($a_results) || count($a_results) != 8) {
            return false;
        }
        return true;
    }
    public function readItemDataTester()
    {
        $a_test_data = $this->a_test_values['item_data'];
        $a_results = $this->o_item->readItemData(
            array('data_item_id' => $this->a_test_values['old_item']['item_id']),
            array('search_type' => 'AND')
        );
        if ($a_results === false || $a_results == array() || is_null($a_results) || count($a_results) < 1) {
            $this->setSubfailure('readItemData', 'first');
            return false;
        }
        foreach ($a_results as $a_item) {
            switch ($a_item['field_name']) {
                case 'city':
                    if ($a_item['data_text'] != $a_test_data['city']) {
                        return false;
                    }
                    break;
                case 'federal_state':
                    if ($a_item['data_text'] != $a_test_data['federal_state']) {
                        return false;
                    }
                    break;
                case 'latitude':
                    if ($a_item['data_text'] != $a_test_data['latitude']) {
                        return false;
                    }
                    break;
                case 'longitude':
                    if ($a_item['data_text'] != $a_test_data['longitude']) {
                        return false;
                    }
                    break;
                default:
                    // do nothing
            }
        }
        return true;
    }
    public function readFieldByIdTester()
    {
        $a_field_values = $this->a_test_values['field'];
        $results = $this->o_item->readFieldById($a_field_values['field_id']);
        if ($results['field_name'] == $a_field_values['field_name']) {
            return true;
        }
        return false;
    }
    public function readFieldByNameTester()
    {
        $a_field_values = $this->a_test_values['field'];
        $results = $this->o_item->readFieldByName($a_field_values['field_name']);
        if ($results['field_id'] == $a_field_values['field_id']) {
            return true;
        }
        return false;
    }
    public function readFieldByOldIdTester()
    {
        $a_field_values = $this->a_test_values['field'];
        $results = $this->o_item->readFieldByOldId($a_field_values['field_old_id']);
        if ($results['field_id'] == $a_field_values['field_id']) {
            return true;
        }
        return false;
    }
    public function requiredItemKeysTester()
    {
        // need to test both new and update Tests
        $good_new_keys = array('item_name' => 'test', 'not_a_key' => 'not_a_key');
        $good_update_keys = array('item_id' => 1, 'not_a_key' => 'not_a_key');
        $results_1 = $this->o_item->requiredItemKeys($good_new_keys, 'new');
        $results_2 = $this->o_item->requiredItemKeys($good_update_keys, 'new');
        $results_3 = $this->o_item->requiredItemKeys($good_update_keys, 'update');
        $results_4 = $this->o_item->requiredItemKeys($good_new_keys, 'update');
        if ($results_2 !== false || $results_4 !== false) {
            return false;
        }
        if (!isset($results_1['item_updated_on']) || !isset($results_3['item_updated_on'])) {
            return false;
        }
        return true;
    }
    public function requiredItemDataKeysTester()
    {
        $good_new_keys = array('data_field_id' => 1, 'data_item_id' => 1, 'data_text' => 'test');
        $good_update_keys = array('data_id' => 1);
        $results_1 = $this->o_item->requiredItemDataKeys($good_new_keys, 'new');
        $results_2 = $this->o_item->requiredItemDataKeys($good_update_keys, 'new');
        $results_3 = $this->o_item->requiredItemDataKeys($good_update_keys, 'update');
        $results_4 = $this->o_item->requiredItemDataKeys($good_new_keys, 'update');
        if ($results_2 !== false || $results_4 !== false) {
            return false;
        }
        if (!isset($results_1['data_updated_on']) || !isset($results_3['data_updated_on'])) {
            return false;
        }
        return true;
    }
    public function createItemTester()
    {
        return false;
    }
    public function createCategoryItemTester()
    {
        return false;
    }
    public function createItemDataTester()
    {
        return false;
    }
    public function updateItemTester()
    {
        return false;
    }
    public function updateItemDataTester()
    {
        return false;
    }
    public function deleteItemDataTester()
    {
        return false;
    }
    public function deleteCategoryItemTester()
    {
        return false;
    }
    public function deleteItemTester()
    {
        return false;
    }

    ### Utility Methods ###

    /**
     *  Compares two arrays and sees if the values in the second array match the first.
     *  @param array $a_good_values required
     *  @param array $a_check_values required
     *  @return bool true or false
    **/
    public function compareArrays($a_good_values = '', $a_check_values = '')
    {
        foreach ($a_good_values as $key => $value) {
            if ($a_good_values[$key] != $a_check_values[$key]) {
                return false;
            }
        }
        return true;
    }
}
