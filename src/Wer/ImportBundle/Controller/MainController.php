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
    public function importAllAction()
    {
        $this->o_db->startTransaction();
        $doc_root = $_SERVER['DOCUMENT_ROOT'] . '/assets/files/guide/';
        require $doc_root . 'section_files.php';
        foreach ($a_section_file_list as $section_file_name) {
            require $doc_root . $section_file_name;
            list($name, $extension) = explode('.', $section_file_name);
            $s_id = str_replace('section_', '', $name);
            $sec_old_cat_id = ${'a_sec_' . $s_id . '_data'}['sec_old_cat_id'];
            if ($s_id != $sec_old_cat_id) {
                exit("Section ID of file {$s_id} does not match section id of data {$sec_old_cat_id}");
            }
            $a_section_values = array(
                ':sec_name'        => ${'a_sec_' . $s_id . '_data'}['sec_name'],
                ':sec_description' => ${'a_sec_' . $s_id . '_data'}['sec_description'],
                ':sec_image'       => ${'a_sec_' . $s_id . '_data'}['sec_image'],
                ':sec_order'       => ${'a_sec_' . $s_id . '_data'}['sec_order'],
                ':sec_active'      => ${'a_sec_' . $s_id . '_data'}['sec_active'],
                ':sec_old_cat_id'  => $sec_old_cat_id
            );
            $return_this = '';
            if ($this->sectionExists($a_section_values[':sec_old_cat_id'])) { // update section
                $return_this .= 'Updating section<br />';
                $a_section = $this->o_sec->readSectionByOldCatId($a_section_values['sec_old_cat_id']);
                if ($a_section === false) {
                    $this->o_db->rollbackTransaction();
                    exit('Could not retrieve section');
                }
                $a_section_values[':sec_id'] = $a_section['sec_id'];
                $results = $this->o_sec->updateSection($a_section_values);
                if ($results === false) {
                    $this->o_db->rollbackTransaction();
                    exit('Could not update the section'
                        . $a_data['sec_name'] . '('
                        . $a_data['sec_old_cat_id'] . ')'
                    );
                }
                $return_this .= 'Section Updated<br />';
                $section_id = $a_section['sec_id'];
            } else { // insert section
                $return_this .= 'Inserting Section<br />';
                $section_id = $this->o_sec->createSection($a_section_values);
                if ($section_id === false) {
                    $this->o_db->rollbackTransaction();
                    exit('Could not insert the section '
                        . $a_data['sec_name'] . '('
                        . $a_data['sec_old_cat_id'] . ')'
                    );
                }
                $return_this .= 'Section Inserted.<br />';
            }

            // next foreach file in ${'a_sec_' . $s_id . '_cat_files'}
            foreach (${'a_sec_' . $s_id . '_cat_files'} as $cat_file_name) {
                require_once $doc_root . $cat_file_name;
                list($c_name, $extension) = explode('.', $cat_file_name);
                $c_id = str_replace('cat_', '', $c_name);
                $cat_old_cat_id = ${'a_cat_' . $c_id}['cat_old_cat_id'];
                $a_category_values = array(
                    ':cat_name'        => ${'a_cat_' . $c_id}['cat_name'],
                    ':cat_description' => ${'a_cat_' . $c_id}['cat_description'],
                    ':cat_image'       => ${'a_cat_' . $c_id}['cat_image'],
                    ':cat_order'       => ${'a_cat_' . $c_id}['cat_order'],
                    ':cat_active'      => ${'a_cat_' . $c_id}['cat_active'],
                    ':cat_old_cat_id'  => $cat_old_cat_id
                );
                if ($this->categoryExists($cat_old_cat_id)) { // update category
                    $return_this .= "Updating Category: " . ${'a_cat_' . $c_id}['cat_name'] . "<br>";
                    $a_old_cat = $this->o_cat->readCatByOldCatId($cat_old_cat_id);
                    if ($a_old_cat === false) {
                        $this->o_db->rollbackTransaction();
                        exit('Could not retrieve category');
                    }
                    $a_category_values[':cat_id'] = $a_old_cat['cat_id'];
                    $results = $this->o_cat->updateCategory($a_category_values);
                    if ($results === false) {
                        $this->o_db->rollbackTransaction();
                        exit('Could not update the category '
                            . ${'a_cat_' . $c_id}['cat_name'] . '('
                            . $cat_old_cat_id . ')'
                        );
                    }
                    $return_this .= "Category Updated<br>";
                    $category_id = $a_old_cat['cat_id'];
                } else { // insert category
                    $return_this .= "Inserting New Category: " . ${'a_cat_' . $c_id}['cat_name'] . "<br>";
                    $category_id = $this->o_cat->createCategory($a_category_values);
                    if ($category_id === false) {
                        $this->o_db->rollbackTransaction();
                        exit('Could not insert the category '
                            . ${'a_cat_' . $c_id}['cat_name'] . '('
                            . $cat_old_cat_id . ')'
                        );
                    }
                    $a_sc_values = array(
                        ':sc_sec_id' => $section_id,
                        ':sc_cat_id' => $category_id
                    );
                    $results = $this->o_cat->createSectionCategory($a_sc_values);
                    if ($results === false) {
                        $this->o_db->rollbackTransaction();
                        exit('Could not create the wer_section_category record');
                    }
                    $return_this .= "Category Inserted";
                }

                foreach (${'a_cat_'. $c_id . '_item_files'} as $item_file_name) {
                    require_once $doc_root . $item_file_name;
                    list($i_name, $extension) = explode('.', $item_file_name);
                    $i_id = str_replace('item_', '', $i_name);
                    $item_old_id = ${'a_item_' . $i_id}['item_old_id'];
                    if ($i_id != $item_old_id) {
                        exit('The file item id !== array item_old_id');
                    }
                    $a_item_values = array(
                        ':item_name'   => ${'a_item_' . $i_id}['item_name'],
                        ':item_active' => ${'a_item_' . $i_id}['item_active'],
                        ':item_old_id' => $item_old_id
                    );
                    if ($this->itemExists($item_old_id)) {
                        $return_this .= "Updating Item: " . ${'a_item_' . $i_id}['item_name'] . "<br>";
                        $a_org_item = $this->o_item->readItemByOldItemId($item_old_id);
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
                        $return_this .= "Item Updated Successfully<br>";
                    } else {
                        $return_this .= "Inserting New Item: {$a_item_values[':item_name']}<br>";
                        $current_date = date('Y-m-d H:i:s');
                        $a_item_values[':item_created_on'] = $current_date;
                        $a_item_values[':item_updated_on'] = $current_date;
                        $item_id = $this->o_item->createItem($a_item_values);
                        if ($item_id === false) {
                            $this->o_db->rollbackTransaction();
                            exit('Could not insert a new item ' . ${'a_item_' . $i_id}['item_name']);
                        }
                        $return_this .= "Item Inserted<br>";

                        $return_this .= "Inserting Category Item record<br>";
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
                    // $a_item needs to be changed to ${'a_item_'. $i_id . '_data'}
                    foreach (${'a_item_'. $i_id . '_data'} as $a_field_data) {
                        error_log('FIELD DATA: ' . var_export($a_field_data, true));

                        $a_field = $this->o_item->readFieldByName($a_field_data['field_key']);
                        $a_item_data = array(
                            'data_field_id'   => $a_field['field_id'],
                            'data_item_id'    => $item_id,
                            'data_text'       => $a_field_data['field_data'],
                            'data_updated_on' => $current_date
                        );
                        if ($this->itemDataExists($item_id, $a_field['field_id'])) {
                            $return_this .= "Updating item data record: {$a_field['field_id']}<br>";
                            $a_old_item_data = $this->o_item->readItemDataByFieldItemIds($item_id, $a_field['field_id']);
                            if ($a_old_item_data === false) {
                                $this->o_db->rollbackTransaction();
                                exit('Could not read old item data during update');
                            }
                            $a_item_data['data_id'] = $a_old_item_data['data_id'];
                            $a_results = $this->o_item->updateItemData($a_item_data);
                            if ($a_results === false) {
                                $this->o_db->rollbackTransaction();
                                exit('Could not updata an existing item data record.');
                            }
                            $return_this .= "Record Updated<br>";
                        } else {
                            $return_this .= "Inserting new item data record<br>";
                            $a_item_data['data_created_on'] = $current_date;
                            $new_item_data_id = $this->o_item->createItemData($a_item_data);
                            if ($new_item_data_id === false) {
                                $this->o_db->rollbackTransaction();
                                exit('Could not insert a new item data record.');
                            }
                            $return_this .= "Record created<br>";
                        }
                    } // foreach item data
                } // foreach item
            } // foreach category
        } // foreach section file
        $this->o_db->commitTransaction();
        return $this->render(
            'WerImportBundle:Main:index.html.twig',
            array(
                'title'       => 'Import',
                'description' => '',
                'site_url'    => "http://{$_SERVER['SERVER_NAME']}",
                'body_text'   => $return_this
            )
        );

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
    private function itemDataExists($item_id = '', $field_id = '', $field_name = '')
    {
        if ($item_id == '') { return false; }
        if ($field_id == '' && $field_name == '') { return false; }
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
        $a_results = $this->o_sec->readSectionByOldCatId($old_id);
        if ($a_results !== false) {
            return true;
        }
        return false;
    }
}
