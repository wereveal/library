<?php
/**
 *  Handles all the database needs (CRUD) for the Sections
 *  @file WerSection.php
 *  @class WerSection
 *  @ingroup guide classes
 *  @author William E Reveal <wer@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2013-03-29 09:02:46
 *  @par Change log
 *      v0.1.0 - initial version
 *  @par Guide v0.1
**/
namespace Wer\GuideBundle\Model;

use Wer\FrameworkBundle\Library\Elog;
use Wer\FrameworkBundle\Library\Database;

class WerSection
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
                sec_name, sec_description, sec_image, sec_order, sec_active, sec_old_cat_id
            ) VALUES (
                :sec_name, :sec_description, :sec_image, :sec_order, :sec_active, :sec_old_cat_id
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
        return $this->o_db->modify($sql, $a_query_values, true);
    }

    ### Delete Methods ###

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
