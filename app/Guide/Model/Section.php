<?php
/**
 *  Handles all the database needs (CRUD) for the Sections
 *  @file Section.php
 *  @class Section
 *  @ingroup guide controller
 *  @author William E Reveal <wer@revealitconsulting.com>
 *  @version 0.2.0
 *  @date 2013-05-09 12:36:02
 *  @par Change log
 *      v0.2.0 - testing phase for the Read Methods
 *      v0.1.0 - initial version 03/29/2013
 *  @par Guide v0.1
**/
namespace Wer\Guide\Model;

use Wer\Framework\Library\Elog;
use Wer\Framework\Library\Database;

class Section
{
    protected $o_db;
    protected $o_elog;

    public function __construct()
    {
        $this->o_elog = Elog::start();
        $this->o_db = Database::start();
        if ($this->o_db->connect() === false) {
            exit("Could not connect to the database");
        }
    }

    ### Create Methods ###
    /**
     *  Creates a new record in the wer_section table
     *  @param array $a_query_values
     *  @return mixed new record id or failure
    **/
    public function createSection($a_query_values)
    {
        $sql = "
            INSERT INTO wer_section (
                sec_name, sec_title, sec_description, sec_image, sec_order, sec_active, sec_old_cat_id
            ) VALUES (
                :sec_name, :sec_title, :sec_description, :sec_image, :sec_order, :sec_active, :sec_old_cat_id
            )
        ";
        if ((isset($a_query_values['sec_name']) && $a_query_values['sec_name'] == '')
            || (isset($a_query_values[':sec_name']) && $a_query_values[':sec_name'] == '')
        ) {
            return false; // sec_name is required.
        }
        $a_query_values = $this->setRequiredSectionKeys($a_query_values);
        if ($a_query_values === false) {
            return false;
        }
        if ($this->o_db->insert($sql, $a_query_values, 'wer_section')) {
            $a_new_ids = $this->o_db->getNewIds();
            return $a_new_ids[0];
        }
        return false;
    }

    ### Read Methods ###
    /**
     *  Returns one or more records from the wer_section table
     *  @param array $a_search_for optional, assoc array field_name=>field_value
     *  @param array $a_search_parameters optional allows one to specify various settings
     *      array(
     *          'search_type' => 'AND', // can also be or
     *          'limit_to' => '', // limit the number of records to return
     *          'starting_from' => '' // which record to start a limited return
     *          'comparison_type' => '=' // what kind of comparison to use for ALL WHEREs
     *          'order_by' => 'column_name' // name of the column(s) to sort by
     *      )
     *      Not all parameters need to be in the array, if doesn't exist, the default setting will be used.
     *  @return array $a_records
    **/
    public function readSection($a_search_for = '', $a_search_parameters = '')
    {
        if (isset($a_search_parameters['order_by']) === false) {
            if (is_array($a_search_parameters)) {
                $a_search_parameters['order_by'] = 'sec_order ASC';
            } else {
                $a_search_parameters = array('order_by' => 'sec_order ASC');
            }
        }
        $sql = "SELECT * \nFROM wer_section \n";
        $sql .= $this->o_db->buildSqlWhere($a_search_for, $a_search_parameters);
        $this->o_elog->write('SQL: ' . $sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $a_results = $this->o_db->search($sql, $a_search_for);
        $this->o_elog->write('Sections: ' . var_export($a_results , TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($a_results === false) {
            $this->o_elog->write($this->o_db->getVar('sql_error_message'), LOG_OFF, __METHOD__ . '.' . __LINE__);
        }
        return $a_results;
    }
    /**
     *  Returns a record from the wer_section table
     *  @param int $record_id required
     *  @return array $a_record
    **/
    public function readSectionById($record_id = '')
    {
        $sql = "
            SELECT *
            FROM wer_section
            WHERE sec_id = :sec_id
        ";
        $a_results = $this->o_db->search($sql, array(':sec_id' => $record_id));
        if (count($a_results) > 0) {
            return $a_results[0];
        }
        return false;
    }
    /**
     *  Returns a record from the wer_section table
     *  @param int $old_id
     *  @return array
    **/
    public function readSectionByOldCatId($old_id = '')
    {
        $sql = "
            SELECT *
            FROM wer_section
            WHERE sec_old_cat_id = :sec_old_cat_id
        ";
        $a_query_values = array(':sec_old_cat_id' => $old_id);
        $a_results = $this->o_db->search($sql, $a_query_values);
        if (count($a_results) > 0) {
            return $a_results[0];
        }
        return false;
    }
    /**
     *  Gets the record from wer_section_category which matches both section_id and category_id
     *  @param int $sec_id
     *  @param int $cat_id
     *  @return array $a_record  should always be a single record
    **/
    public function readSectionCategoryBySecCat($sec_id = '', $cat_id = '')
    {
        $sql = "
            SELECT *
            FROM wer_section_category
            WHERE sc_sec_id = :sc_sec_id
            AND sc_cat_id = :sc_cat_id
        ";
        $a_results = $this->o_db->search($sql, array(':sc_sec_id' => $sec_id, ':sc_cat_id' => $cat_id));
        if (count($a_results) > 0) {
            return $a_results[0];
        }
        return false;
    }
    /**
     *  Gets the records from wer_section_category which match the section id
     *  @param int $sec_id
     *  @return array $a_records
    **/
    public function readSectionCategoryBySecId($sec_id = '')
    {
        $sql = "
            SELECT *
            FROM wer_section_category
            WHERE sc_sec_id = :sc_sec_id
        ";
        $a_results = $this->o_db->search($sql, array(':sc_sec_id' => $sec_id));
        if (count($a_results) > 0) {
            return $a_results;
        }
        return false;
    }

    ### Update Methods ###
    /**
     *  Updates a record in the wer_section table
     *  @param array $a_query_values
     *  @return bool success or failure
    **/
    public function updateSection($a_query_values = '')
    {
        $sql = "
            UPDATE wer_section
            SET sec_name        = :sec_name,
                sec_title       = :sec_title,
                sec_description = :sec_description,
                sec_image       = :sec_image,
                sec_order       = :sec_order,
                sec_active      = :sec_active,
                sec_old_cat_id  = :sec_old_cat_id
            WHERE sec_id = :sec_id
        ";
        $a_missing_keys = $this->o_db->findMissingKeys(array('sec_id', 'sec_name'), $a_query_values);
        if (count($a_missing_keys) > 0) { // the id or name is missing
            return false;
        }
        $section_id = isset($a_query_values['sec_id']) ? $a_query_values['sec_id'] : $a_query_values[':sec_id'];
        $a_old_record = $this->readSectionById($section_id);
        $a_query_values = $this->setRequiredSectionKeys($a_query_values, $a_old_record);
        if ($a_query_values === false) {
            return false;
        }
        return $this->o_db->update($sql, $a_query_values, true);
    }

    ### Delete Methods ###
    /**
     *  Deletes the record specified by id
     *  @param int $record_id required
     *  @return bool success or failure
    **/
    public function deleteSection($record_id = '')
    {
        if($record_id == '' || !is_numeric($record_id)) {
            return false;
        }
        $a_results = $this->readSectionCategoryBySecId($record_id);
        if ($a_results !== false && count($a_results) > 0) {
            return false; // a category still exists in the section
        }
        $sql = "DELETE FROM wer_section WHERE sec_id = :sec_id";
        return $this->o_db->delete($sql, array(':sec_id' => $record_id), true);
    }

    ### Utilities ###

    /**
     *  Creates a new array that has all the required keys.
     *  @param array $a_required_keys required
     *  @param array $a_old_record optional for updates
     *      Makes sure that we don't overide existing values when the query values are missing.
     *  @return array or bool
    **/
    public function setRequiredSectionKeys($a_query_values = '', $a_old_record = '')
    {
        $a_required_keys = array(
            'sec_id',
            'sec_name',
            'sec_title',
            'sec_description',
            'sec_image',
            'sec_order',
            'sec_active',
            'sec_old_cat_id'
        );
        $a_query_values = $this->o_db->removeBadKeys($a_required_keys, $a_query_values);
        $a_missing_keys = $this->o_db->findMissingKeys($a_required_keys, $a_query_values);
        if (is_array($a_old_record)) {
            $a_old_record = $this->o_db->prepareKeys($a_old_record);
        }
        foreach ($a_missing_keys as $key) {
            switch ($key) {
                case 'sec_id':
                    /*
                     *  probably a create,
                     *  if an update, then the update code needs to
                     *  check for sec_id there
                     */
                    break;
                case 'sec_name':
                    /* required for both create and update */
                    return false;
                case 'sec_title':
                    $a_query_values[':sec_title'] =
                        isset($a_old_record[':sec_title'])
                        ? $a_old_record['sec_title']
                        : isset($a_query_values['sec_name'])
                            ? $a_query_values['sec_name']
                            : '';
                        break;
                case 'sec_description':
                    $a_query_values[':sec_description'] =
                        isset($a_old_record[':sec_description'])
                        ? $a_old_record[':sec_description']
                        : '';
                    break;
                case 'sec_image':
                    $a_query_values[':sec_image'] =
                        isset($a_old_record[':sec_image'])
                        ? $a_old_record[':sec_image']
                        : '';
                    break;
                case 'sec_order':
                    $a_query_values[':sec_order'] =
                        isset($a_old_record[':sec_order'])
                        ? $a_old_record[':sec_order']
                        : 0;
                    break;
                case 'sec_active':
                    $a_query_values[':sec_active'] =
                        isset($a_old_record[':sec_active'])
                        ? $a_old_record[':sec_active']
                        : 1;
                    break;
                case 'sec_old_cat_id':
                    $a_query_values[':sec_old_cat_id'] =
                        isset($a_old_record[':sec_old_cat_id'])
                        ? $a_old_record[':sec_old_cat_id']
                        : '';
                    break;
                default:
                    return false;
            }
        }
        return $a_query_values;
    }
}
