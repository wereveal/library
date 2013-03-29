<?php
/**
 *  Handles all the database needs (CRUD) for the Sections
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
     *  @return bool success or failure
    **/
    public function createSection($a_query_values)
    {
        return false;
    }
    ### Read Methods ###
    /**
     *  Returns a record from the wer_section table
     *  @param int $old_id
     *  @return array
    **/
    public function readSectionByOldItemId($old_id = '')
    {
        $sql = "
            SELECT * 
            FROM wer_section 
            WHERE section_old_section_id = :section_old_section_id"
        ;
        $a_query_values = array(':section_old_section_id' => $old_id);
        $a_results = $this->o_db->search($sql, $a_query_values);
        if (count($a_results) > 0) {
            return $a_results[0];
        } else {
            return false;
        }
    }
    
    ### Update Methods ###
    /**
     *  Updates a record in the wer_section table
     *  @param array $a_query_values
     *  @return bool success or failure
    **/
    public function updateSection($a_query_values = '')
    {
        return false;
    }
    
    ### Delete Methods ###
}
