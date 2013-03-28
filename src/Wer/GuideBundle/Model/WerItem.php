<?php
/**
 *  Handles all the database needs (CRUD) for the Items
**/
namespace Wer\GuideBundle\Model;

use Wer\FrameworkBundle\Library\Elog;
use Wer\FrameworkBundle\Library\Database;

class WerItem
{
    protected $o_db;
    protected $o_elog;
    protected $o_model_field;

    public function __construct()
    {
        $this->o_elog = Elog::start();
        $this->o_db = Database::start();
        if ($this->o_db->connect() === false) {
            exit("Could not connect to the database");
        }
        $this->o_model_field = new WerField();
    }

    ### READ methods ###
    /**
     *  Returns the record for the Item specified by item_old_id
     *  @param int $old_item_id
     *  @return mixed array or false
    **/
    public function readItemByOldItemId($old_item_id = '')
    {
        if ($old_item_id == '') {
            return false;
        }
        $sql = "SELECT * FROM wer_item WHERE item_old_id = :item_old_id";
        $a_search_values = array(':item_old_id' => $old_item_id);
        $a_values = $this->o_db->search($sql, $a_search_values);
        if (count($a_values) > 0) {
            return $a_values[0];
        }
        return false;
    }
    /**
     *  Returns the record for the item data specified
     *  @param int $item_id
     *  @param int $field_id optional if $field_name is specified else required
     *  @param str $field_name optional not used if $field_id is specified
     *  @return mixed array or false
    **/
    public function readItemData($item_id = '', $field_id = '', $field_name = '')
    {
        if ($item_id == '' || ($field_id == '' && $field_name == '')) {
            return false;
        }
        if ($field_id == '') {
            $field_sql = "
                SELECT field_id
                FROM wer_field
                WHERE field_name = :field_name
            ";
            $a_search_values = array(':field_name' => $field_name);
            $a_field = $this->o_db->search($field_sql, $a_search_values);
            if (count($a_field) > 0) {
                $field_id = $a_field[0]['field_id'];
            } else {
                return false;
            }
        }
        if ($field_id != '') {
            $sql = "
                SELECT *
                FROM wer_item_data
                WHERE data_item_id = :data_item_id
                AND data_field_id = :data_field_id
            ";
            $a_search_values = array(
                ':data_item_id'  => $field_id,
                ':data_field_id' => $field_id
            );
            $a_item_data = $this->o_db->search($sql, $a_search_values);
            if (count($a_item_data) > 0) {
                return $a_item_data[0];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
