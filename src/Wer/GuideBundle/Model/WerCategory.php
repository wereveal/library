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
    /**
     *  Adds a new record to the wer_category table
    **/
    public function createCategory()
    {
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
    /**
     *  Updates the wer_category table
     *  @return bool success or failure
    **/
    public function updateCategory()
    {
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

}
