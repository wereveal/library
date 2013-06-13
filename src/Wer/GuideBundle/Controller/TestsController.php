<?php
/**
 *  Controller to do Tests for the GuideBundle.
 *  @file TestsController.php
 *  @class TestsController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.2
 *  @par Change Log
 *      v0.1 - Initial version 2012-06-04
 *  @par Wer GuideBundle version 1.0
 *  @date 2013-06-06 13:36:24
 *  @ingroup guide_bundle
**/
namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\Tests\Model\ItemTester;

class TestsController extends Controller
{
    public function itemAction()
    {
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
        $failed_tests = $o_item->runTests('Wer\GuideBundle\Tests\Model\ItemTester');
        $a_content = $o_item->returnTestResults(true, false);
        $a_content = $this->formatTestNames($a_content, 'BR');
        $a_twig_values = array(
            'title'         => 'Guide',
            'description'   => 'This is a test',
            'site_url'      => SITE_URL,
            'rights_holder' => 'William E. Reveal',
            'content'       => $a_content
        );
        return $this->render('WerGuideBundle:Pages:test.html.twig', $a_twig_values);
    }

    /**
     *  Format the test names.
     *  @param array $a_content
     *  @return array
    **/
    public function formatTestNames($a_content = '', $format = 'BR')
    {
        switch ($format) {
            case 'BR':
                $a_content['failed_test_names'] = nl2br($a_content['failed_test_names']);
                $a_content['passed_test_names'] = nl2br($a_content['passed_test_names']);
                return $a_content;
            case 'UL':
                $a_lines_failed = explode("\n", $a_content['failed_test_names']);
                $a_lines_passed = explode("\n", $a_content['passed_test_names']);
                $tests_failed = '';
                $tests_passed = '';
                $start_ul = '';
                $start_sub_ul = '';
                foreach ($a_lines_failed as $key=>$line) {
                    if (substr($line, 0, 1) != '&') {
                        $tests_failed .= '<h3>' . $line . '</h3>';
                    } elseif (substr($line, 0, 48) == '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') {
                        if ($start_sub_ul == '') {
                            $tests_failed .= '<ul>';
                        }
                        $line = str_replace('&nbsp;', '', $line);
                        $tests_failed .= '<li>' . $line . '</li>';
                    } elseif (substr($line, 0, 24) == '&nbsp;&nbsp;&nbsp;&nbsp;') {
                        if ($start_ul == '') {
                            $tests_failed .= '<ul>';
                            $start_ul == 'no more';
                        }
                        if ($start_sub_ul != '') {
                            $start_sub_ul = '';
                            $tests_failed .= '</ul>';
                        }
                        $tests_failed .= '</li>';
                        $line = str_replace('&nbsp;', '', $line);
                        $tests_failed .= '<li>' . $line;
                    } elseif ($line == '') {
                        // nothing
                    }
                }
                $tests_failed .= '</ul>';
                $start_ul = '';
                $start_sub_ul = '';
                foreach ($a_lines_passed as $key=>$line) {
                    if (substr($line, 0, 1) != '&') {
                        $tests_passed .= '<h3>' . $line . '</h3>';
                    } elseif (substr($line, 0, 48) == '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') {
                        if ($start_sub_ul == '') {
                            $tests_passed .= '<ul>';
                        }
                        $line = str_replace('&nbsp;', '', $line);
                        $tests_passed .= '<li>' . $line . '</li>';
                    } elseif (substr($line, 0, 24) == '&nbsp;&nbsp;&nbsp;&nbsp;') {
                        if ($start_sub_ul != '') {
                            $start_sub_ul = '';
                            $tests_passed .= '</ul>';
                        }
                        $tests_passed .= '</li>';
                        $line = str_replace('&nbsp;', '', $line);
                        $tests_passed .= '<li>' . $line;
                    } elseif ($line == '') {
                        // nothing
                    }
                }
                $tests_passed .= '</ul>';
                $a_content['failed_test_names'] = $tests_failed;
                $a_content['passed_test_names'] = $tests_passed;
                return $a_content;
        }
    }
}
