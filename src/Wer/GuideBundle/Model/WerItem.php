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
     *  Adds a new category item record to the wer_category_item table.
     *  The connector between the item and its parent category
     *  @param array $a_values
     *  @return int $record_id ID of th new record
    **/
    public function createCategoryItem($a_values = '')
    {
        if ($a_values == '') { return false; }
        $a_required_keys = array(
            'ci_category_id',
            'ci_item_id',
            'ci_order'
        );
        $a_values = $this->o_db->removeBadKeys($a_required_keys, $a_values);
        $a_missing_keys = $this->o_db->findMissingKeys($a_required_keys, $a_values);
        foreach($a_missing_keys as $key) {
            switch ($key) {
                case 'ci_category_id':
                    return false;
                case 'ci_item_id':
                    return false;
                case 'ci_order':
                    $a_values[':ci_order'] = 0;
                default:
                    $a_values[':' . $key] = '';
            }
        }
        $sql = "
            INSERT INTO wer_category_item (
                ci_category_id,
                ci_item_id,
                ci_order
            ) VALUES (
                :ci_category_id,
                :ci_item_id,
                :ci_order
            )
        ";
        $results = $this->o_db->insert($sql, $a_values, 'wer_category_item');
        if ($results === false) {
            return false;
        }
        $a_ids = $this->o_db->getNewIds();
        return $a_ids[0];
    }
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
        $a_values = $this->setRequiredItemKeys($a_values, 'new');
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
    /**
     *  Creates an item data record in the wer_item_data table
     *  @param array $a_values
     *  @return bool success or failure
    **/
    public function createItemData($a_values = '')
    {
        if ($a_values == '') { return false; }
        $a_required_keys = array('data_field_id', 'data_item_id', 'data_text');
        if (count($this->o_db->findMissingKeys($a_required_keys, $a_values)) > 0) {
            return false;
        }
        if (count($this->o_db->findMissingValues($a_required_keys, $a_values)) > 0) {
            return false;
        }
        $a_values = $this->setRequiredItemDataKeys($a_values, 'new');
        if ($a_values === false) { return false; }
        $sql = "
            INSERT INTO wer_item_data (
                data_field_id,
                data_item_id,
                data_text,
                data_created_on,
                data_updated_on
            ) VALUES (
                :data_field_id,
                :data_item_id,
                :data_text,
                :data_created_on,
                :data_updated_on
            )
        ";
        // error_log(var_export($a_values, true));
        $results = $this->o_db->insert($sql, $a_values, 'wer_item_data');
        if ($results === false) {
            return false;
        }
        $a_ids = $this->o_db->getNewIds();
        return $a_ids[0];
    }

    ### READ methods ###
    /**
     *  Returns the values for the field
     *  @param int $field_id
     *  @return mixed array $field_values or bool false
    **/
    public function readFieldById($field_id = '')
    {
        if ($old_field_id == '') { return false; }
        $sql = '
            SELECT *
            FROM wer_field
            WHERE field_id = :field_id
        ';
        $results = $this->o_db->search($sql, array(':field_id' => $field_id));
        if (count($results) > 0) {
            return $results[0];
        } else {
            return false;
        }
    }
    /**
     *  Retuns the values for the field
     *  @param str $field_name
     *  @return mixed array field values or false
    **/
    public function readFieldByName($field_name = '')
    {
        if ($field_name == '') {
            return false;
        }
        $sql = "SELECT * FROM wer_field WHERE field_name = :field_name";
        $results = $this->o_db->search($sql, array(':field_name' => $field_name));
        if (count($results) > 0) {
            return $results[0];
        } else {
            return false;
        }
    }
    /**
     *  Returns the values for the field
     *  @param int $old_field_id
     *  @return mixed array $field_values or bool false
    **/
    public function readFieldByOldId($old_field_id = '')
    {
        if ($old_field_id == '') { return false; }
        $sql = '
            SELECT *
            FROM wer_field
            WHERE field_old_field_id = :field_old_field_id
        ';
        $results = $this->o_db->search($sql, array(':field_old_field_id' => $old_field_id));
        if (count($results) > 0) {
            return $results[0];
        } else {
            return false;
        }
    }
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
    /**
     *  Updates a record in the wer_item table
     *  @param array $a_values all the values to be saved and the item id
     *  @return bool true or false
    **/
    public function updateItem($a_values = '')
    {
        if ($a_values == '') { return false; }
        $a_values = $this->setRequiredItemKeys($a_values, 'update');
        if ($a_values === false) { return false; }

        $a_values = $this->o_db->prepareKeys($a_values);
        $set_sql = $this->o_db->buildSqlSet($a_values, array(':item_id'));

        $sql = "
            UPDATE wer_item
            {$set_sql}
            WHERE item_id = :item_id
        ";
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Updates a record in the wer_item_data table
     *  @param array $a_values required
     *  @return bool true or false
    **/
    public function updateItemData($a_values = '')
    {
        if ($a_values == '') { return false; }
        $a_values = $this->setRequiredItemDataKeys($a_values, 'update');
        if ($a_values === false) { return false; }
        error_log(var_export($a_values, true));
        $a_values = $this->o_db->prepareKeys($a_values);
        error_log('Again: ' . var_export($a_values, true));
        $set_sql = $this->o_db->buildSqlSet($a_values, array(':data_id'));

        $sql = "
            UPDATE wer_item_data
            {$set_sql}
            WHERE data_id = :data_id
        ";
        error_log('update sql: ' . $sql);
        return $this->o_db->update($sql, $a_values, true);
    }

    ### DELETE methods ###

    ### Utilities ###
    /**
     *  Sets the required keys for the wer_item table
     *  @param array $a_values required
     *  @param bool $new_or_update optional new records require all fields
     *      but an update only requires item_id and item_updated_on
     *  @return array $a_values
    **/
    public function setRequiredItemKeys($a_values = '', $new_or_update = 'new')
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

        foreach ($a_missing_keys as $key) {
            switch ($key) {
                case 'item_id':
                    if ($new_or_update == 'update') { // update requires a valid id
                        return false;
                    }
                    break;
                case 'item_name':
                    if ($new_or_update == 'new') {
                        return false;
                    }
                    break;
                case 'item_created_on':
                    if ($new_or_update == 'new') {
                        $a_values[':item_created_on'] = $current_timestamp;
                    }
                    break;
                case 'item_updated_on':
                    $a_values[':item_updated_on'] = $current_timestamp;
                    break;
                case 'item_active':
                    if ($new_or_update == 'new') {
                        $a_values[':item_active'] = 1;
                    }
                    break;
                case 'item_old_id':
                    if ($new_or_update == 'new') {
                        $a_values[':item_old_id'] = '';
                    }
                    break;
            }
        }
        return $a_values;
    }
    /**
     *  Sets the required keys for the wer_item_data table
     *  @param array $a_values required *  @param bool $new_or_update optional defaults to new
     *  @return array $a_values
    **/
    public function setRequiredItemDataKeys($a_values = '', $new_or_update = 'new')
    {
        $a_required_keys = array(
            'data_id',
            'data_field_id',
            'data_item_id',
            'data_text',
            'data_created_on',
            'data_updated_on'
        );
        $a_values = $this->o_db->removeBadKeys($a_required_keys, $a_values);
        $a_missing_keys = $this->o_db->findMissingKeys($a_required_keys, $a_values);
        $current_timestamp = date('Y-m-d H:i:s');

        foreach ($a_missing_keys as $key) {
            switch ($key) {
                case 'data_id':
                    if ($new_or_update == 'update') {
                        return false;
                    } // else it is a new record so there is no data_id yet.
                    break;
                case 'data_field_id':
                case 'data_item_id':
                case 'data_text':
                    if ($new_or_update == 'new') {
                        return false;
                    }
                    break;
                case 'data_created_on':
                    if ($new_or_update == 'new') {
                        $a_values[':data_created_on'] = $current_timestamp;
                    }
                    break;
                case 'data_updated_on':
                    $a_values[':data_updated_on'] = $current_timestamp;
                    break;
                default:
                    return false;
            }
        }
        return $a_values;
    }
}
