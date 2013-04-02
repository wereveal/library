<?php

namespace Wer\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\Model\WerCategory;
use Wer\GuideBundle\Model\WerField;
use Wer\GuideBundle\Model\WerItem;
use Wer\GuideBundle\Model\WerSection;
use Wer\FrameworkBundle\Library\Database;

class MainController extends Controller
{
    private $o_cat;
    private $o_field;
    private $o_item;
    private $o_sec;
    private $o_db;

    public function __construct()
    {
        $this->o_db    = Database::start();
        $this->o_cat   = new WerCategory();
        $this->o_field = new WerField();
        $this->o_item  = new WerItem();
        $this->o_sec   = new WerSection();
    }
    public function indexAction()
    {
        return $this->render(
            'WerImportBundle:Main:index.html.twig',
            array(
                'title'       => 'Import',
                'description' => '',
                'site_url'    => "http://{$_SERVER['SERVER_NAME']}",
                'body_text'   => 'Import'
            )
        );
    }
    /**
     *  Imports all the records for the guide
     *  @param none
     *  @return bool success or failure
    **/
    public function importAll()
    {
        $this->o_db->startTransaction();
        include $_SERVER['DOCUMENT_ROOT'] . '/assets/files/guide.php';
        $a_section_values = array(
            ':sec_name'        => $a_data['sec_name'],
            ':sec_description' => $a_data['sec_description'],
            ':sec_image'       => $a_data['image'],
            ':sec_order'       => $a_data['sec_order'],
            ':sec_active'      => $a_data['sec_active'],
            ':sec_old_cat_id'  => $a_data['sec_old_cat_id']
        );

        if ($this->sectionExists($a_data['sec_old_cat_id'])) { // update section
            $a_section = $this->o_sec->readSectionByOldCatId($a_data['sec_old_cat_id']);
            if ($a_section === false) {
                $this->o_db->rollbackTransaction();
                exit('Could not retrieve section');
            }
            $a_section_values[':sec_id'] = $a_section['sec_id'];
            $results = $this->o_sec->updateSection($a_query_values);
            if ($results === false) {
                $this->o_db->rollbackTransaction();
                exit('Could not update the section'
                    . $a_data['sec_name'] . '('
                    . $a_data['sec_old_cat_id'] . ')'
                );
            }
            $section_id = $a_section['sec_id'];
        } else { // insert category
            $section_id = $this->o_sec->createSection($a_query_values);
            if ($section_id === false) {
                $this->o_db->rollbackTransaction();
                exit('Could not insert the section'
                    . $a_data['sec_name'] . '('
                    . $a_data['sec_old_cat_id'] . ')'
                );
            }
        }
        foreach ($a_data['sec_categories'] as $a_category) {
            $a_category_values = array(
                ':cat_name'        => $a_category['cat_name'],
                ':cat_description' => $a_category['cat_description'],
                ':cat_image'       => $a_category['cat_image'],
                ':cat_order'       => $a_category['cat_order'],
                ':cat_active'      => $a_category['cat_active'],
                ':cat_old_cat_id'  => $a_category['cat_old_cat_id']
            );
            if ($this->categoryExists($a_category['cat_old_cat_id'])) { // update category
                $a_old_cat = $this->o_cat->readCatByOldCatId($a_category['cat_old_cat_id']);
                if ($a_old_cat === false) {
                    $this->o_db->rollbackTransaction();
                    exit('Could not retrieve category');
                }
                $a_category_values[':cat_id'] = $a_old_cat['cat_id'];
                $results = $this->o_cat->updateCategory($a_category_values);
                if ($results === false) {
                    $this->o_db->rollbackTransaction();
                    exit('Could not update the category'
                        . $a_category['cat_name'] . '('
                        . $a_category['cat_old_cat_id'] . ')'
                    );
                }
                $category_id = $a_old_cat['cat_id'];
            } else { // insert category
                $category_id = $this->o_cat->createCategory($a_category_values);
                if ($category_id === false) {
                    $this->o_db->rollbackTransaction();
                    exit('Could not insert the category'
                        . $a_category['cat_name'] . '('
                        . $a_category['cat_old_cat_id'] . ')'
                    );
                }
                $a_sc_values = array(
                    ':sc_section_id'  => $section_id,
                    ':sc_category_id' => $category_id
                );
                $results = $this->o_cat->createSectionCategory($a_sc_values);
                if ($results === false) {
                    $this->o_db->rollbackTransaction();
                    exit('Could not create the wer_section_category record');
                }
            }
            foreach ($a_category['cat_items'] as $a_item) {
                $a_item_values = array(
                    ':item_name'   => $a_item['item_name'],
                    ':item_active' => $a_item['item_active'],
                    ':item_old_id' => $a_item['item_old_id']
                );
                if ($this->itemExists($a_item['item_old_id'])) {
                    $a_org_item = $this->o_item->readItemByOldItemId($a_item['item_old_id']);
                    if ($a_org_item === false) {
                        $this->o_db->rollbackTransaction();
                        exit('Could not retrieve the item');
                    }
                    $a_item_values[':item_id'] = $a_org_item['item_id'];
                    $a_item_values[':item_updated_on'] = date('Y-m-d H:i:s');
                    $results = $this->o_item->updateItem($a_item_values);
                    if ($results === false) {
                        $this->o_db->rollbackTransaction();
                        exit('Could not update the item');
                    }
                    $item_id = $a_org_item['item_id'];
                } else {
                    $current_date = date('Y-m-d H:i:s');
                    $a_item_values[':item_created_on'] = $current_date;
                    $a_item_values[':item_updated_on'] = $current_date;
                    $item_id = $this->o_item->createItem($a_item_values);
                    if ($item_id === false) {
                        $this->o_db->rollbackTransaction();
                        exit('Could not insert a new item ' . $a_item['item_name']);
                    }

                    $a_ci_values = array(
                        ':ci_category_id' => $category_id,
                        ':ci_item_id'     => $item_id,
                        ':ci_order'       => 0
                    );
                    $ci_id = $this->o_item->createCategoryItem($a_ci_values);
                    if ($ci_id === false) {
                        $this->o_db->rollbackTransaction();
                        exit('Could not create the Category Item bridge record');
                    }
                }
                if (is_array($a_item['item_fields'])) {
                    foreach ($a_item['item_fields'] as $a_field_data) {
                        $a_item_data = array();
                        $a_field = $this->o_item->readFieldByName($a_field_data['field_key']);
                        $a_item_data['data_field_id']   = $a_field['field_id'];
                        $a_item_data['data_item_id']    = $item_id;
                        $a_item_data['data_text']       = $a_field_data['field_data'];
                        $a_item_data['data_created_on'] = $current_date;
                        $a_item_data['data_updated_on'] = $current_date;
                        $a_item_data_id = $this->o_item->createItemData($a_item_data);
                    }
                }
            }
        }
        $this->o_db->commitTransaction();
        /*
            if section doesn't exist $this->sec($old_cat_id) === false
                create the section and get the new id, put it in a variable.
            else
                update the section and put id in variable
            endif
            foreach ($a_data['sec_categories'] as $a_category)
                if category doesn't exist $this->categoryExists($old_cat_id) === false
                    create the category and get the new id, put it in a variable
                else
                    update the category and put id in variable
                endif
                foreach ($a_category['cat_items'] as $a_item)
                    if item doesn't exist $this->itemExists($old_itemm_id) === false
                        create the item, get the new id and put it in a variable
                    else
                        update the item
                    endif
                    foreach ($a_item['item_fields'] as $a_item_data)
                        if data doesn't exist $this->itemDataExists($item_id, $field_key)
                            create the item data
                        else
                            update the item data
                        endif
                    endforeach
                endforeach
            endforeach

            private function sectionExists($sec_old_cat_id)
            private function categoryExists($cat_old_cat_id)
            private function itemExists($item_old_item_id)
            private function itemDataExists($item_id, $field_key)
            private function selectFieldId($field_name)
        */
    }

    ### Select Methods ###
    /**
     *  Selects the id of the field which matches the field name
     *  @param str $field_name
     *  @return int $field_id
    **/
    public function grabFieldId($field_name = '')
    {
        $a_results = $this->o_field->readFieldByName($field_name);
        if (is_array($a_results)) {
            return $a_results['field_id'];
        }
        return '';
    }

    ### Utility Methods ###
    /**
     *  Verifies that the category exists
     *  @param int $old_cat_id
     *  @return bool true or false
    **/
    private function categoryExists($old_cat_id = '')
    {
        $a_results = $this->o_cat->readCatByOldCatId($old_cat_id);
        if ($a_results !== false) {
            return true;
        }
        return false;
    }
    /**
     *  Verifies the item record exists
     *  @param int $old_item_id
     *  @return bool true or false
    **/
    private function itemExists($old_item_id = '')
    {
        $a_results = $this->o_item->readItemByOldItemId($old_item_id);
        if ($a_results !== false) {
            return true;
        }
        return false;
    }
    /**
     *  Verifies the Item Data Record exists
     *  @param int $item_id
     *  @param str $field_name
     *  @return bool true or false
    **/
    private function itemDataExists($item_id = '', $field_name = '')
    {
        $field_id = '';
        $a_results = $this->o_item->readItemData($item_id, $field_id, $field_name);
        if ($a_results !== false) {
            return true;
        }
        return false;
    }
    /**
     *  Verifies the section record exists
     *  @param int $old_id
     *  @return bool true or false
    **/
    private function sectionExists($old_id = '')
    {
        $a_results = $this->o_sec->readSectionByOldItemId($old_id);
        if ($a_results !== false) {
            return true;
        }
        return false;
    }
}
