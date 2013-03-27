<?php
/**
 *  Handles all the database needs (CRUD) for the Category
**/

namespace Wer\GuideBundle\Model;

class CategoryModel
{
    private $cat_id;
    private $cat_name;
    private $cat_description;
    private $cat_image;
    private $cat_order;
    private $cat_active;
    private $cat_old_cat_id;
    private $ci_id;
    private $ci_category_id;
    private $ci_object_id;
    private $ci_order;
    private $cr_id;
    private $cr_parent_id;
    private $cr_child_id;

    private $connection;

    /**
     * Stuff to do when the object is created
    **/
    public function __construct()
    {
        $this->connection = $this->get('database_connection');
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
}
