<?php
/**
 *  Handles all the database needs (CRUD) for the Category
**/

namespace Wer\GuideBundle\Model;

class WerCategory extends ContainerAware
{
    private $o_db;

    /**
     * Stuff to do when the object is created
    **/
    public function __construct()
    {
        $this->o_db = $this->get('database_connection');
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

    ### Setters and Getters ###

}
