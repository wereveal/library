<?php
/**
 *  Handles all the database needs (CRUD) for the Fields
 *  @file Field.php
 *  @class Field
 *  @ingroup guide_bundle
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

class Field
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
    /**
     *  Creates a record in the wer_field table
     *  @param array $a_values
     *  @return mixed returns the new id or false on failure
    **/
    public function createField($a_values = '')
    {
    }
    /**
     *  Creates a record in the wer_field_option table
     *  @param array $a_values
     *  @return mixed new id or false on failure
    **/
    public function createFieldOption($a_values = '')
    {
    }
    /**
     *  Creates a new record in the wer_field_type tables
     *  @param array $a_values
     *  @return mixed new id or false on failure
    **/
    public function createFieldType($a_values = '')
    {
    }
    /**
     *  Reads one or more records from the wer_field table
     *  @param array $a_values array of field id
     *  @return array record(s)
    **/
    public function readField($a_values = '')
    {
        $a_return_values = array();
        if ($a_values != '') {
            $sql .= "
                SELECT *
                FROM wer_field
                WHERE field_id = :field_id
            ";
            $a_return_values = $this->o_db->search($sql, $a_values, 'assoc');
        } else {
            $sql = "
                SELECT *
                FROM wer_field
            ";
            $a_return_values = $this->o_db->search($sql);
        }
        if (count($a_return_values) > 0) {
            return $a_return_values;
        }
        return false;
    }
    /**
     *  Reads one or more records from wer_field_option table
     *  @param array $a_values
     *  @return array record(s)
    **/
    public function readFieldOption($a_values = '')
    {
    }
    /**
     *  Returns the record of the field specified by name
     *  @param id $field_name
     *      field_name in wer_field is a unique indexed field
     *  @return mixed array or bool (false)
    **/
    public function readFieldByName($field_name = '')
    {
        $sql = "SELECT * FROM wer_field WHERE field_name LIKE :field_name";
        $a_search_values = array(':field_name' => $field_name);
        $a_values = $this->o_db->search($sql, $a_search_values);
        if (count($a_values) > 0) {
            return $a_values[0];
        }
        return false;
    }
    /**
     *  Read one or more records from wer_field_type table
     *  @param array $a_values
     *  @return array record(s)
    **/
    public function readFieldType($a_values = '')
    {
    }
    /**
     *  Updates one or more records in wer_field
     *  @param array $a_values
     *  @return bool success or failure
    **/
    public function updateField($a_values = '')
    {
    }
    /**
     *  Updates one or more records in wer_field_option
     *  @param array $a_values
     *  @return bool success or failure
    **/
    public function updateFieldOption($a_values = '')
    {
    }
    /**
     *  Updates one or more records in wer_field_type
     *  @param array $a_values
     *  @return bool success or failure
    **/
    public function updateFieldType($a_values = '')
    {
    }
    /**
     *  Deletes one or more records from wer_field
     *  @param array $a_values array of field ids
     *  @return bool success or failure
    **/
    public function deleteField($a_values = '')
    {
    }
    /**
     *  Deletes one or more records from wer_field_option
     *  @param array $a_values array of field ids
     *  @return bool success or failure
    **/
    public function deleteFieldType($a_values = '')
    {
    }
    /**
     *  Deletes one or more records from wer_field_option
     *  @param array $a_values array of field ids
     *  @return bool success or failure
    **/
    public function deleteFieldOption($a_values = '')
    {
    }

### Utilities ###

}
