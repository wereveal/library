<?php
/**
 *  Handles all the database needs (CRUD) for the Category
 *  @file Category.php
 *  @class Category
 *  @ingroup guide_
 *  @author William E Reveal <wer@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2013-03-29 09:02:46
 *  @par Change log
 *      v0.1.0 - initial version
 *  @par Guide v0.1
**/
namespace Wer\Guide\Model;

use Wer\Framework\Library\Elog;
use Wer\Framework\Library\Database;

class Category
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
     *  Adds a new record to the wer_category_relations table
     *  @return bool success or failure
    **/
    public function createCategoryRelations()
    {
    }
    /**
     *  Adds a new bridge record in the wer_section_category table.
     *  @param array $a_values must have both sc_sec_id and sc_cat_id key=>value pairs
     *  @return mixed int $new_record_id or bool false on failure
    **/
    public function createSectionCategory($a_values = '')
    {
        if ($a_values == '') { return false; }
        $sql = "
            INSERT INTO wer_section_category (
                sc_sec_id,
                sc_cat_id
            ) VALUES (
                :sc_sec_id,
                :sc_cat_id
            )
        ";
        $a_results = $this->o_db->findMissingKeys(array('sc_sec_id', 'sc_cat_id'), $a_values);
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
     *  @param array $a_search_for optional, an assoc array of field=>value pairs.
     *      fields must be valid fields for the wer_category table
     *      if not specified, returns all categories
     *  @param array $a_search_parameters optional allows one to specify various settings
     *      array(
     *          'search_type' => 'AND', // can also be or
     *          'limit_to' => '', // limit the number of records to return
     *          'starting_from' => '' // which record to start a limited return
     *          'comparison_type' => '=' // what kind of comparison to use for ALL WHEREs
     *          'order_by' => 'column_name' // name of the column(s) to sort by
     *      )
     *      Not all parameters need to be in the array, if doesn't exist, the default setting will be used.
     *  @return mixed, array of records or bool success or failure
    **/
    public function readCategory($a_search_for = '', $a_search_parameters = '')
    {
        $where = '';
        if (isset($a_search_parameters['order_by']) === false) {
            if (is_array($a_search_parameters)) {
                $a_search_parameters['order_by'] = 'cat_order ASC';
            } else {
                $a_search_parameters = array('order_by' => 'cat_order ASC');
            }
        }
        $this->o_elog->write("a_search_for:\n" . var_export($a_search_for, TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $where = $this->o_db->buildSqlWhere($a_search_for, $a_search_parameters);
        $sql = "SELECT * \nFROM wer_category \n{$where}";
        $this->o_elog->write("SELECT category sql: \n" . $sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->search($sql, $a_search_for);
        if ($results !== false && count($results) > 0) {
            return $results[0];
        }
        return false;
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
     *  Returns the categories for a particular section
     *  @param int $sec_id optional if not specified returns all categories for all sections sorted by section
     *  @param array $a_cat_pairs optional if not specified all categories for section(s)
     *  @return array $a_categories
    **/
    public function readCatBySec($sec_id = '')
    {
        $where = '';
        $a_search_values = '';
        if ($sec_id != '') { // build the where for the section
            $where .= "AND sec.sec_id = :sec_id \n";
            $a_search_values = array(':sec_id' => $sec_id);
        }
        $sql = "
            SELECT sec.*, cat.*
            FROM wer_section AS sec, wer_category AS cat, wer_section_category as sc
            WHERE sc.sc_sec_id = sec.sec_id
            AND sc.sc_cat_id = cat.cat_id
        ";
        $order_by = "ORDER BY sec.sec_order ASC, cat.cat_order ASC";
        $total_sql = $sql . $where . $order_by;
        return $this->o_db->search($total_sql, $a_search_values);
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
     *  Returns the id of the first category.
     *  First category is based on cat_order field in wer_category table
     *  @param none
     *  @return array first record from the results (should only be one anyway)
    **/
    public function readFirstCategory()
    {
        $sql = "
            SELECT cat_id, cat_name, cat_order
            FROM wer_category
            WHERE cat_active = 1
            ORDER BY cat_order ASC
            LIMIT 1
        ";
        $results = $this->o_db->search($sql);
        if ($results !== false && count($results) > 0) {
            return $results[0];
        }
        return false;
    }
    /**
     *  Gets the records from wer_section_category which match the section id
     *  @param int $sec_id
     *  @return array $a_records
    **/
    public function readSectionCategoryByCatId($cat_id = '')
    {
        $sql = "
            SELECT *
            FROM wer_section_category
            WHERE sc_cat_id = :sc_cat_id
        ";
        $a_results = $this->o_db->search($sql, array(':sc_cat_id' => $cat_id));
        if (count($a_results) > 0) {
            return $a_results;
        }
        return false;
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
        $a_cat_values = $this->setRequiredCatKeys($a_cat_values, 'update');
        if ($a_cat_values === false) { return false; }
        $a_cat_values = $this->o_db->prepareKeys($a_cat_values);
        $set_sql = $this->o_db->buildSqlSet($a_cat_values, array(':cat_id'));
        $sql = "
            UPDATE wer_category
            {$set_sql}
            WHERE cat_id = :cat_id
        ";
        return $this->o_db->update($sql, $a_cat_values, true);
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
    public function setRequiredCatKeys($a_values = '', $new_or_update = 'new')
    {
        $a_required_keys = array(
            'cat_id',
            'cat_name',
            'cat_description',
            'cat_image',
            'cat_order',
            'cat_active',
            'cat_old_cat_id'
        );
        $a_values = $this->o_db->removeBadKeys($a_required_keys, $a_values);
        $a_missing_keys = $this->o_db->findMissingKeys($a_required_keys, $a_values);
        foreach ($a_missing_keys as $key) {
            switch ($key) {
                case 'cat_id':
                    if ($new_or_update == 'update') { // update requires a valid id
                        return false;
                    }
                    break;
                case 'cat_name':
                    if ($new_or_update == 'new') {
                        return false;
                    }
                    break;
                case 'cat_description':
                    if ($new_or_update == 'new') {
                        $a_values[':cat_description'] = '';
                    }
                    break;
                case 'cat_image':
                    if ($new_or_update == 'new') {
                        $a_values[':image'] = '';
                    }
                    break;
                case 'cat_order':
                    if ($new_or_update == 'new') {
                        $a_values[':cat_order'] = 0;
                    }
                    break;
                case 'cat_active':
                    if ($new_or_update == 'new') {
                        $a_values[':cat_active'] = 1;
                    }
                    break;
                case 'cat_old_id':
                    if ($new_or_update == 'new') {
                        $a_values[':cat_old_id'] = '';
                    }
                    break;
                default:
                    return false;
            }
        }
        return $a_values;
    }

}
