<?php

namespace Wer\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\Model\WerCategory;
use Wer\GuideBundle\Model\WerField;
use Wer\GuideBundle\Model\WerItem;
use Wer\GuideBundle\Model\WerSection;

class MainController extends Controller
{
    private $o_model_category;
    private $o_model_field;
    private $o_model_item;
    private $o_model_section;

    public function __construct()
    {
        $this->o_model_category = new WerCategory();
        $this->o_model_field    = new WerField();
        $this->o_model_item     = new WerItem();
        $this->o_model_section  = new WerSection();
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
        include $_SERVER['DOCUMENT_ROOT'] . '/assets/files/guide.php';
        $a_section_values = array(
            ':section_name'        => $a_data['sec_name'],
            ':section_description' => $a_data['sec_description'],
            ':section_active'      => $a_data['sec_active'],
            ':section_image'       => $a_data['image'],
            ':sec_old_cat_id'      => $a_data['sec_old_cat_id']
        );
        if ($this->sectionExists($a_data['sec_old_cat_id'])) {
            $a_section = $this->o_model_section->readSectionByOldItemId($a_data['sec_old_cat_id']);
            // update the section
            $a_section_values[':section_id'] = $a_section['section_id'];
            $results = $this->o_model_section->updateSection($a_query_values);
        } else {
            $results = $this->o_model_section->createSection($a_query_values);
        }
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
        $a_results = $this->o_model_field->readFieldByName($field_name);
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
        $a_results = $this->o_model_category->readCatByOldCatId($old_cat_id);
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
        $a_results = $this->o_model_item->readItemByOldItemId($old_item_id);
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
        $a_results = $this->o_model_item->readItemData($item_id, $field_id, $field_name);
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
        $a_results = $this->o_model_section->readSectionByOldItemId($old_id);
        if ($a_results !== false) {
            return true;
        }
        return false;
    }
}
