<?php
/**
 *  Handles all the database needs (CRUD) for the Category
**/
namespace Wer\GuideBundle\Model;

use Wer\FrameworkBundle\Library\Elog;
use Wer\FrameworkBundle\Library\Database;

class WerCategory
{
    private $o_db;
    protected $o_elog;

    /**
     * Stuff to do when the object is created
    **/
    public function __construct()
    {
        $this->o_elog = Elog::start();
        $this->o_db = Database::start();
        if ($this->o_db->connect() === false) {
            exit("Could not connect to the database");
        }
    }

    ### CREATE methods ###
    /**
     *  Adds a new record to the wer_category table
     *  @param array $a_cat_values required defaults to empty
     *  @return mixed int new category id or bool false
    **/
    public function createCategory($a_cat_values = '')
    {
        if ($a_cat_values == '') { return false; }
        $sql = "
            INSERT INTO wer_category (
                cat_name,
                cat_description,
                cat_image,
                cat_order,
                cat_active,
                cat_old_cat_id
            ) VALUES (
                :cat_name,
                :cat_description,
                :cat_image,
                :cat_order,
                :cat_active,
                :cat_old_cat_id
            )
        ";
        $a_cat_values = $this->setRequiredCatKeys($a_cat_values);
        if ($this->o_db->insert($sql, $a_cat_values, true) === true) {
            $a_ids = $this->o_db->getNewIds();
            return $a_ids[0];
        }
        return false;
    }
    /**
     *  Adds a new record to the wer_category_item table
     *  @return bool success or failure
    **/
    public function createCategoryItem()
    {
    }
    /**
     *  Adds a new record to the wer_category_relations table
     *  @return bool success or failure
    **/
    public function createCategoryRelations()
    {
    }
    /**
     *  Adds a new bridge record in the wer_section_category table.
     *  @param array $a_values
     *  @return mixed int $new_record_id or bool false on failure
    **/
    public function createSectionCategory($a_values = '')
    {
        if ($a_values == '') { return false; }
        $sql = "
            INSERT INTO wer_section_category (
                sc_section_id,
                sc_category_id
            ) VALUES (
                :sc_section_id,
                :sc_category_id
            )
        ";
        $a_results = $this->o_db->findMissingKeys(array('sc_section_id', 'sc_category_id'), $a_values);
        if (count($a_results) > 0) {
            return false;
        }
        $results = $this->o_db->insert($sql, $a_values, 'wer_section_category');
        if ($results !== false) {
            $a_ids = $this->o_db->getNewIds();
            return $a_ids[0];
        }
        return false;
    }

    ### READ methods ###
    /**
     *  Gets the data from wer_category table
     *  @return bool success or failure
    **/
    public function readCategory()
    {
    }
    /**
     *  Returns the category record found
     *  @param int $old_cat_id
     *  @return mixed array of record or false if none found or sql error
    **/
    public function readCatByOldCatId($old_cat_id = '')
    {
        $sql = "SELECT * FROM wer_category WHERE cat_old_cat_id = :cat_old_cat_id";
        $a_search_values = array(':cat_old_cat_id' => $old_cat_id);
        $a_values = $this->o_db->search($sql, $a_search_values);
        if (count($a_values) === 1) {
            return $a_values[0];
        }
        return false;
    }
    /**
     *  Gets the data from wer_category_item table
     *  @return bool success or failure
    **/
    public function readCategoryItem()
    {
    }
    /**
     *  Gets the data from wer_category_relations tables
     *  @return bool success or failure
    **/
    public function readCategoryRelations()
    {
    }

    ### UPDATE methods ###
    /**
     *  Updates the wer_category table
     *  @param array $a_cat_values
     *  @return bool success or failure
    **/
    public function updateCategory($a_cat_values = '')
    {
        if ($a_cat_values == '') { return false; }
        $sql = "
            UPDATE wer_category
            SET cat_name        = :cat_name,
                cat_description = :cat_description,
                cat_order       = :cat_order,
                cat_active      = :cat_active,
                cat_old_cat_id  = :cat_old_cat_id
            WHERE cat_id = :cat_id
        ";
        if (count($this->o_db->findMissingKeys(array('cat_name'), $a_values)) > 0) { // cat_name is required
            return false;
        }
        $a_cat_values = $this->setRequiredCatKeys($a_cat_values);
        return $this->o_db->modify($sql, $a_cat_values, true);
    }
    /**
     *  Updates the wer_category_item table
     *  @return bool success or failure
    **/
    public function updateCategoryItem()
    {
    }
    /**
     *  Updates the wer_category_relations tables
     *  @return bool success or failure
    **/
    public function updateCategoryRelations()
    {
    }
    /**
     *  Deletes a record in the wer_category table
     *  @return bool success or failure
    **/

    ### DELETE methods ###
    public function deleteCategory()
    {
    }
    /**
     *  Deletes a record in the wer_category_item tables
     *  @return bool success or failure
    **/
    public function deleteCategoryItem()
    {
    }
    /**
     *  Deletes a record in the wer_category_relations table
     *  @return bool success or failure
    **/
    public function deleteCategoryRelations()
    {
    }

    ### Setters and Getters ###
    ### Utilities ###
    /**
     *  Sets the required keys for the wer_item table
     *  @param array $a_values required
     *  @return array $a_values
    **/
    public function setRequiredCatKeys($a_values = '')
    {
        $a_required_keys = array(
            'cat_id',
            'cat_name',
            'cat_description',
            'cat_order',
            'cat_active',
            'cat_old_cat_id'
        );
        $a_values = $this->o_db->removeBadKeys($a_required_keys, $a_values);
        $a_missing_keys = $this->o_db->findMissingKeys($a_required_keys, $a_values);
        foreach ($a_missing_keys as $key) {
            switch ($key) {
                case 'cat_id':
                    /* probably a create, updates need to check for id in that method */
                    break;
                case 'cat_name':
                    return false;
                    break;
                case 'cat_description':
                    $a_values[':cat_description'] = '';
                    break;
                case 'cat_order':
                    $a_values[':cat_order'] = 0;
                    break;
                case 'cat_active':
                    $a_values[':cat_active'] = 1;
                    break;
                case 'cat_old_id':
                    $a_values[':cat_old_id'] = '';
                    break;
            }
        }
    }

}
