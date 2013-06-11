<?php
/**
 *  Handles all the database needs (CRUD) for the Items
 *  @file Item.php
 *  @class Item
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

class Item
{
    protected $o_db;
    protected $o_elog;
    protected $o_cat;
    protected $o_field;

    public function __construct()
    {
        $this->o_elog = Elog::start();
        $this->o_db = Database::start();
        if ($this->o_db->connect() === false) {
            exit("Could not connect to the database");
        }
        $this->o_field = new Field();
        $this->o_cat   = new Category();
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
        $a_values = $this->requiredItemKeys($a_values, 'new');
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
        $a_values = $this->requiredItemDataKeys($a_values, 'new');
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
        if ($field_id == '') { return false; }
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
     *  @param array $a_search_pairs optional, field=>value to search upon
     *  @param array $a_search_parameters optional allows one to specify various settings
     *      array(
     *          'search_type' => 'AND', // can also be or
     *          'limit_to' => '', // limit the number of records to return
     *          'starting_from' => '' // which record to start a limited return
     *          'comparison_type' => '=' // what kind of comparison to use for ALL WHEREs
     *          'order_by' => 'column_name' // name of column(s) to order by
     *      )
     *      Not all parameters need to be in the array, if doesn't exist, the default setting will be used.
     *  @return array $a_records an array of array(s)
    **/
    public function readItem($a_search_pairs = '', $a_search_parameters = '')
    {
        $sql = "SELECT * FROM wer_item ";
        $sql .= $this->o_db->buildSqlWhere($a_search_pairs, $a_search_parameters);
        $this->o_elog->write('SQL: ' . $sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->o_elog->write('Search Pairs: ' . var_export($a_search_pairs, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->o_elog->write('' . var_export($a_search_parameters, TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql, $a_search_pairs);
    }
    /**
     *  Returns the records from wer_items from a category.
     *  @param int $cat_id required, if '' return array()
     *  @param array $a_search_params optional
     *  @param array $a_search_pairs optional
     *  @return array $a_items
    **/
    public function readItemByCategory($cat_id = '', $a_search_params = array())
    {
        if ($cat_id == '') {
            return array();
        }
        $sql_where = $this->sqlWhere($a_search_params);
        $sql = "
            SELECT i.*
            FROM wer_item as i, wer_category_item as ci
            WHERE i.item_id = ci.ci_item_id
            AND ci.ci_category_id = :ci_category_id
            {$sql_where}
        ";
        $this->o_elog->write($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql, array(':ci_category_id' => $cat_id));
    }
    /**
     *  Returns the records from wer_item.
     *  A shortcut which uses the main method readItem()
     *  @param str $item_name
     *  @return array $a_items
    **/
    public function readItemByName($item_name = '')
    {
        return $this->readItem(array(':item_name' => $item_name), array('comparison_type' => 'LIKE', 'limit_to' => 1));
    }
    /**
     *  Returns the records that match the first letter of the item name with the param
     *  @param str $the_letter required
     *  @param int $limit_to optional limit the number of records found
     *  @return array $a_records
    **/
    public function readItemByNameFirstLetter($the_letter = 'A', $start = 0, $limit_to = '')
    {
        // error_log("In ITEM===> the letter: $the_letter  start: $start  limit_to: $limit_to");
        $the_letter = substr(trim($the_letter), 0, 1);
        if ($the_letter == '') {
            return false;
        }
        $a_search_pairs = array(':item_name' => $the_letter . '%');
        $a_search_parameters = array(
            'comparison_type' => 'LIKE',
            'starting_from'   => $start,
            'limit_to'        => $limit_to
        );
        return $this->readItem($a_search_pairs, $a_search_parameters);
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
        $a_search_values = array('item_old_id' => $old_item_id);
        return $this->readItem($a_search_values);
    }
    /**
     *  Returns items from specified section.
     *  This normally is used to return either a set number of random or featured
     *  items from a specific section. However, search params can change what item records
     *  from a section will be returned.
     *  @param int $section_id required, if '' return array()
     *  @param array $a_search_params optional, defaults as follows
     *      array(
     *          'is_active' => true,
     *          'is_random' => true, // used if is_featured is false
     *          'is_featured' => true, // affects the use of is_random
     *          'limit_to' => 10, // limit the number of records to return
     *          'order_by' => 'i.item_name' // not used if is_random === true
     *      )
     *      Not all parameters need to be in the array, if doesn't exist, the default setting will be used.
     *  @return array $a_items
    **/
    public function readItemBySection($section_id = '', $a_search_params = array())
    {
        if ($section_id == '') {
            return array();
        }
        $sql_where = $this->sqlWhere($a_search_params);
        $sql = "
            SELECT i.*
            FROM wer_item as i, wer_category_item as ci, wer_section_category as sc
            WHERE i.item_id = ci.ci_item_id
            AND ci.ci_category_id = sc.sc_cat_id
            AND sc.sc_sec_id = :sc_sec_id
            {$sql_where}
        ";
        $a_search_pairs = array(":sc_sec_id" => $section_id);
        return $this->o_db->search($sql, $a_search_pairs);
    }
    /**
     *  Gets the number of records in items that match the parameters.
     *  @param array $a_search_pairs
     *  @return int record count
    **/
    public function readItemCount($a_search_pairs = '', $a_search_parameters = '')
    {
        if ($a_search_pairs == '') { return 0; }
        $sql = "SELECT COUNT(*) as count FROM wer_item ";
        $sql .= $this->o_db->buildSqlWhere($a_search_pairs, $a_search_parameters);
        $results = $this->o_db->search($sql, $a_search_pairs);
        return $results[0]['count'];
    }
    /**
     *  Returns the record for the item data specified
     *  @param array $a_values WHERE search params
     *  @param array $a_search_parameters optional defaults to Database class values
     *      array(
     *          'search_type' => 'AND', // can also be or
     *          'limit_to' => '', // limit the number of records to return
     *          'starting_from' => '' // which record to start a limited return
     *          'comparison_type' => '=' // what kind of comparison to use for ALL WHEREs
     *          'order_by' => '' // column name(s) to sort by eg column_name [ASC,DESC][, column_name]
     *      )
     *  @return mixed array or false
    **/
    public function readItemData($a_values = '', $a_search_parameters = '')
    {
        if ($a_values == '' || !is_array($a_values)) {
            $a_values = array();
        }
        $a_new_values = array();
        foreach ($a_values as $key => $value) {
            $key = preg_replace('/^data_/', 'd.data_', $key);
            $key = preg_replace('/^field_/', 'f.field_', $key);
            $a_new_values[$key] = $value;
        }
        $where = $this->o_db->buildSqlWhere($a_new_values, $a_search_parameters);
        $sql = "
            SELECT d.data_id, f.field_name, d.data_text
            FROM wer_item_data as d, wer_field as f
            {$where}
            AND d.data_field_id = f.field_id
            ORDER BY f.field_id
        ";
        $this->o_elog->write('sql: ' . $sql . "\n", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->search($sql, $a_values);
        if ($results === false) {
            $this->o_elog->write('Error Msg: ' . $this->o_db->getSqlErrorMessage(), LOG_OFF, __METHOD__ . '.' . __LINE__);
        }
        $this->o_elog->write('Item data: ' . var_export($results, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $results;
    }
    /**
     *  Returns a list of items which are marked as featured
     *  @param int $num_of_records
     *  @return array $a_items
    **/
    public function readItemFeatured($num_of_records = 10)
    {
        return $this->readItem(
            array('item_featured' => '1'),
            array('limit_to' => $num_of_records, 'order_by' => 'item_name')
        );
    }
    /**
     *  Returns the ids of the records
     *  @param int $num_of_records optional
     *  @return array $a_items
    **/
    public function readItemIds($num_of_records = 0)
    {
        $num_of_records = (int) $num_of_records;
        $sql = "
            SELECT item_id
            FROM wer_item
            ORDER BY item_id
        ";
        if ($num_of_records > 0) {
            $sql .= "LIMIT {$num_of_records}";
        }
        return $this->o_db->search($sql);
    }
    /**
     *  Returns a list of random items.
     *  @param int $num_of_records
     *  @return array $a_items
    **/
    public function readItemRandom($num_of_records = 10)
    {
        $db_type = $this->o_db->getVar('db_type');
        $sql = "
            SELECT item_id, item_name
            FROM wer_item
            WHERE item_active = 1
        ";
        switch ($db_type) {
            case 'mysql':
                $sql .= "            ORDER BY rand() LIMIT {$num_of_records}";
                break;
            case 'pgsql':
            case 'sqlite':
                $sql .= "            ORDER BY random() LIMIT {$num_of_records}";
                break;
            default:
                $sql .= '';
        }
        $results = $this->o_db->search($sql);
        return $results;
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
        $a_values = $this->requiredItemKeys($a_values, 'update');
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
        $a_values = $this->requiredItemDataKeys($a_values, 'update');
        if ($a_values === false) { return false; }
        $a_values = $this->o_db->prepareKeys($a_values);
        $set_sql = $this->o_db->buildSqlSet($a_values, array(':data_id'));

        $sql = "
            UPDATE wer_item_data
            {$set_sql}
            WHERE data_id = :data_id
        ";
        return $this->o_db->update($sql, $a_values, true);
    }

    ### DELETE methods ###
    /**
     *  Deletes an item record from wer_item
     *  @param int $item_id
     *  @return bool success or failure
    **/
    public function deleteItem($item_id = '')
    {
        return false;
    }
    /**
     *  Deletes the bridge record between category and item from wer_category_item table.
     *  @param int $cat_id required
     *  @param int $item_id required
     *  @return bool success or failure
    **/
    public function deleteCatetoryItem($cat_id = '', $item_id = '')
    {
        return false;
    }

    ### Utilities ###
    /**
     *  Sets the required keys for the wer_item table
     *  @param array $a_values required
     *  @param bool $new_or_update optional new records require all fields
     *      but an update only requires item_id and item_updated_on
     *  @return array $a_values
    **/
    public function requiredItemKeys($a_values = '', $new_or_update = 'new')
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
                        $a_values['item_created_on'] = $current_timestamp;
                    }
                    break;
                case 'item_updated_on':
                    $a_values['item_updated_on'] = $current_timestamp;
                    break;
                case 'item_active':
                    if ($new_or_update == 'new') {
                        $a_values['item_active'] = 1;
                    }
                    break;
                case 'item_old_id':
                    if ($new_or_update == 'new') {
                        $a_values['item_old_id'] = '';
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
    public function requiredItemDataKeys($a_values = '', $new_or_update = 'new')
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
                        $a_values['data_created_on'] = $current_timestamp;
                    }
                    break;
                case 'data_updated_on':
                    $a_values['data_updated_on'] = $current_timestamp;
                    break;
                default:
                    return false;
            }
        }
        return $a_values;
    }
    /**
     *  Creates the sql for the where, order by and limit
     *  @param array $a_params
     *  @return str $sql
    **/
    public function sqlWhere($a_params = '')
    {
        ### Defaults ###
        $is_active    = true;
        $is_random    = false;
        $is_featured  = true;
        $limit_to     = 10;
        $order_by     = 'i.item_name';

        $sql_active   = "AND i.item_active = 1 \n";
        $sql_featured = "            AND i.item_featured = 1 \n";
        $sql_order_by = "            ORDER BY {$order_by} \n";
        $sql_limit_to = "            LIMIT {$limit_to} \n";
        foreach ($a_params as $key => $value) {
            switch ($key) {
                case 'is_active':
                    $is_active = (bool) $value;
                    break;
                case 'is_random':
                    $is_random = (bool) $value;
                    break;
                case 'is_featured':
                    $is_featured = (bool) $value;
                    break;
                case 'limit_to':
                    $limit_to = (int) $value;
                    if ($limit_to === 0) {
                        $sql_limit_to = "            -- return all records, no limit \n";
                    } else {
                        $sql_limit_to = "            LIMIT {$limit_to} \n";
                    }
                    break;
                case 'order_by':
                    $order_by = $value;
                    $sql_order_by = "            ORDER BY {$order_by} \n";
                    break;
                default:
                    // not valid key, skip it
            }
        }
        if ($is_active === false) {
            $sql_active = "-- active and inactive item records \n";
        }
        if ($is_random === true && $is_featured === false) {
            $sql_featured = "            -- using random and not featured items \n";
            $db_type = $this->o_db->getVar('db_type');
            switch ($db_type) {
                case 'mysql':
                    $sql_order_by = "            ORDER BY rand() \n";
                    break;
                case 'pgsql':
                case 'sqlite':
                    $sql_order_by = "            ORDER BY random() \n";
                    break;
                default:
                    $sql_order_by .= "            -- invalid db type, can't set order by random \n";
            }
        }
        return $sql_active . $sql_featured . $sql_order_by . $sql_limit_to;
    }
}
