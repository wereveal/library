<?php
/**
 *  Handles all the database needs (CRUD) for the Items
 *  @file WerItem.php
 *  @class WerItem
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

    ### CREATE methods ###
    /**
     *  Creates a new item record
     *  @param array $a_values required defaults to empty
     *  @return mixed int $new_item_id or bool false
    **/
    public function createItem($a_values = '')
    {
        if ($a_values == '') { return false; }
        $current_timestamp = date('Y-m-d H:i:s');
        if (count($this->o_db->findMissingKeys(array('item_name'), $a_values)) > 0) { // item_name is required
            return false;
        }
        $a_values = $this->setRequiredItemKeys($a_values);
        $sql = "
            INSERT INTO wer_item (
                item_name,
                item_created_on,
                item_updated_on,
                item_active,
                item_old_id
            ) VALUES (
                :item_name,
                :item_created_on,
                :item_updated_on,
                :item_active,
                :item_old_id
            )
        ";
        $results = $this->o_db->insert($sql, $a_values, 'wer_item');
        if ($results === false) {
            return false;
        }
        $a_ids = $this->o_db->getNewIds();
        return $a_ids[0];
    }
    ### READ methods ###
    /**
     *  Generic Read, returns one or more records from the wer_item table
     *  @param array $a_search_pairs optional, field=>value to seach upon
     *  @param str $search_type optional, values are normally AND and OR
     *  @param int $limit_to optional, limit the number of records to
     *  @param int $starting_from optional, starting from record #
     *  @return array $a_records an array of array(s)
    **/
    public function readItem($a_search_pairs = '', $search_type = 'AND', $limit_to = '', $starting_from = '')
    {
        $sql = "SELECT * FROM wer_item ";
        $where = '';
        if ($a_search_pairs != '' && is_array($a_search_pairs)) {
            $a_search_pairs = $this->o_db->prepareKeys($a_search_pairs);

            foreach ($a_search_pairs as $key => $value) {
                $field_name = str_replace(':', '', $key);
                if ($where == '') {
                    $where .= "WHERE {$field_name} = {$key} ";
                } else {
                    $where .= "{$search_type} {$field_name} = {$key} ";
                }
            }
        }
        $limit = '';
        if ($limit_to != '') {
            if ($starting_from != '') {
                --$starting_from; // limit offset starts at 0 so if we want to start at record 6 the LIMIT offset is 5
                $limit = "LIMIT {$starting_from}, {$limit_to}";
            } else {
                $limit = "LIMIT {$limit_to}";
            }
        }
        $sql .= $where . 'ORDER BY item_name '. $limit;
        return $this->o_db->search($sql, $a_search_pairs);
    }
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
        $a_search_values = array(':item_old_id' => $old_item_id);
        $a_values = $this->readItem($a_search_values);
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

    ### UPDATE methods ###

    ### DELETE methods ###

    ### Utilities ###

    /**
     *  Sets the required keys for the wer_item table
     *  @param array $a_values required
     *  @param array $a_old_record optional
     *  @return array $a_values
    **/
    public function setRequiredItemKeys($a_values = '', $a_old_record = '')
    {
        $a_required_keys = array(
            'item_id',
            'item_name',
            'item_created_on',
            'item_updated_on',
            'item_active',
            'item_old_id'
        );
        $a_values = $this->o_db->removeBadKeys($a_required_keys, $a_values);
        $a_missing_keys = $this->o_db->findMissingKeys($a_required_keys, $a_values);
        $current_timestamp = date('Y-m-d H:i:s');
        if (is_array($a_old_record)) {
            $a_old_record = $this->o_db->prepareKeys($a_old_record);
        }
        foreach ($a_missing_keys as $key) {
            switch ($key) {
                case 'item_id':
                    /* probably a create, updates need to check for id in that method */
                    break;
                case 'item_name':
                    return false;
                    break;
                case 'item_created_on':
                    $a_values[':item_created_on'] =
                        isset($a_old_record[':item_created_on'])
                        ? $a_old_record[':item_created_on']
                        : $current_timestamp;
                    break;
                case 'item_updated_on':
                    $a_values[':item_updated_on'] = $current_timestamp;
                    break;
                case 'item_active':
                    $a_values[':item_active'] =
                        isset($a_old_record[':item_active'])
                        ? $a_old_record[':item_active']
                        : 1;
                    break;
                case 'item_old_id':
                    $a_values[':item_old_id'] =
                        isset($a_old_record[':item_old_id'])
                        ? $a_old_record[':item_old_id']
                        : '';
                    break;
            }
        }
        return $a_values;
    }
}
