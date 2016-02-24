<?php
/**
 *  @brief     Does all the database CRUD stuff for the menus.
 *  @ingroup   ritc_library models
 *  @file      MenusModel.php
 *  @namespace Ritc\Library\Models
 *  @class     MenusModel
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0 β1
 *  @date      2016-02-24 13:24:09
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 β1 - Initial version                              - 02/24/2016 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class MenusModel implements ModelInterface
{
    use LogitTraits;

    /**
     * @var array
     */
    private $a_field_names = array();
    /**
     * @var string
     */
    private $db_prefix;
    /**
     * @var string
     */
    private $db_type;
    /**
     * @var string
     */
    private $error_message;
    /**
     * @var \Ritc\Library\Services\DbModel
     */
    private $o_db;
    /**
     * @var string
     */
    private $read_menu_sql;
    /**
     * @var string
     */
    private $read_menu_order_sql;

    /**
     * PageModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
        $this->a_field_names = [
            'menu_id',
            'menu_page_id',
            'menu_parent_id',
            'menu_name',
            'menu_css',
            'menu_level',
            'menu_order',
            'menu_active'
        ];
        $this->setReadMenuSql();
        $this->setReadMenuOrderSql();
    }

    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return string|bool
     */
    public function create(array $a_values)
    {
        $meth = __METHOD__ . '.';
        if ($a_values == array()) { return false; }
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        $a_required_keys = [
            'menu_page_id',
            'menu_name',

        ];
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            $this->error_message = 'Did not have a page id and/or menu name.';
            return false;
        }

        $insert_value_names = $this->o_db->buildSqlInsert($a_values, $this->a_field_names);
        $sql = "
            INSERT INTO {$this->db_prefix}menus (
            {$insert_value_names}
            )
        ";
        $a_table_info = [
            'table_name'  => "{$this->db_prefix}menus",
            'column_name' => 'menu_id'
        ];
        $results = $this->o_db->insert($sql, $a_values, $a_table_info);
        $this->logIt('Insert Results: ' . $results, LOG_OFF, $meth . __LINE__);
        $this->logIt('db object: ' . var_export($this->o_db, TRUE), LOG_OFF, $meth . __LINE__);
        if ($results) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, $meth . __LINE__);
            return $ids[0];
        }
        else {
            $this->error_message = 'The menu could not be saved.';
            return false;
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['menu_parent_id ASC, menu_order ASC, menu_name ASC']
     * @return array
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == []
                ? ['order_by' => 'menu_parent_id ASC, menu_order ASC, menu_name ASC']
                : $a_search_params;
            $a_search_values = Arrays::removeUndesiredPairs($a_search_values, $this->a_field_names);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY menu_parent_id ASC, menu_order ASC, menu_name ASC";
        }
        $select_me = '';
        foreach ($this->a_field_names as $name) {
            $select_me .= $select_me == ''
                ? $name
                : ', ' . $name;
        }
        $sql = "
            SELECT {$select_me}
            FROM {$this->db_prefix}menus
            {$where}
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__);
        $this->logIt("Search Values: " . var_export($a_search_values, true), LOG_OFF);
        $results = $this->o_db->search($sql, $a_search_values);
        return $results;
    }

    /**
     * Generic update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values)
    {
        $meth = __METHOD__ . '.';
        if (!isset($a_values['menu_id'])
            || $a_values['menu_id'] == ''
            || (is_string($a_values['menu_id']) && !is_numeric($a_values['menu_id']))
        ) {
            $this->error_message = 'The Menu Id was not supplied.';
            return false;
        }
        $a_values = Arrays::removeUndesiredPairs($a_values, $this->a_field_names);
        $set_sql = $this->o_db->buildSqlSet($a_values, ['menu_id']);
        $sql = "
            UPDATE {$this->db_prefix}menus
            {$set_sql}
            WHERE menu_id = :menu_id
        ";
        $this->logIt($sql, LOG_OFF, $meth . __LINE__);
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        $results = $this->o_db->update($sql, $a_values, true);
        if ($results) {
            return true;
        }
        else {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
    }

    /**
     * Generic deletes a record based on the id provided.
     * @param int $menu_id
     * @return array
     */
    public function delete($menu_id = -1)
    {
        if ($menu_id == -1) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}menus
            WHERE menu_id = :menu_id
        ";
        $results = $this->o_db->delete($sql, array(':menu_id' => $menu_id), true);
        $this->logIt(var_export($results, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($results) {
            return true;
        }
        else {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
    }

    /**
     * Returns the search results for all menu records.
     * @return array
     */
    public function readMenu() {
        $sql = $this->read_menu_sql . $this->read_menu_order_sql;
        $results = $this->o_db->search($sql);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
        else {
            return $results;
        }
    }

    /**
     * Returns the menu items based on level and parent ID.
     * @param int $level           required
     * @param int $menu_parent_id  optional, if not provided, all menus of that level are returned.
     * @return array|bool
     */
    public function readMenuByLevel($level = -1, $menu_parent_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($level < 1) {
            return false;
        }
        elseif ($menu_parent_id == -1) {
            $where = "    AND m.menu_level = :menu_level\n";
            $a_search_for = [':menu_level' => $level];
        }
        else {
            $where = "    AND m.menu_level = :menu_level\n    AND m.menu_parent_id = :menu_parent_id\n";
            $a_search_for = [
                ':menu_level'     => $level,
                ':menu_parent_id' => $menu_parent_id
            ];
        }
        $sql = $this->read_menu_sql . $where . $this->read_menu_order_sql;
        $this->logIt($sql, LOG_OFF, $meth . __LINE__);
        $results = $this->o_db->search($sql, $a_search_for);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
        else {
            return $results;
        }
    }

    /**
     * Returns an array of menu records based on page id.
     * Gets the top parent and all the children that are related to that page.
     * @param int $page_id
     * @return array|bool
     */
    public function readMenuByPageId($page_id = -1)
    {
        if ($page_id == -1) {
            return false;
        }
        $a_search_for = [':menu_page_id' => $page_id];
        $results = $this->read($a_search_for);
        if ($results !== false && count($results) > 0) {
            $menu_id = $results[0]['menu_id'];
            $parent_id = $results[0]['menu_parent_id'];
            if ($menu_id != $parent_id) {
                $menu_id = $this->findTopMenu($menu_id);
            }
            return $this->readMenuByParentId($menu_id);
        }
        else {
            return false;
        }
    }

    /**
     * Returns an array of menu records that have the given menu id as menu_parent_id.
     * Note that the menu_id needs to be a parent of menu records.
     * Else it will return no records.
     * @param int $menu_id
     * @return bool|array
     */
    public function readMenuByParentId($menu_id = -1)
    {
        if ($menu_id < 1) {
            return false;
        }
        $sql = $this->read_menu_sql;
        $where = "    AND m.menu_parent_id = :menu_parent_id\n";
        $sql .= $where . $this->read_menu_order_sql;
        $a_search_for = [':menu_parent_id' => $menu_id];
        $results = $this->o_db->search($sql, $a_search_for);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
        else {
            return $results;
        }
    }

    /**
     * Returns the menu id of the top most menu for the given menu id.
     * @param int $menu_id
     * @return int
     */
    public function findTopMenu($menu_id = -1)
    {
        if ($menu_id == -1) {
            return 0;
        }
        $a_search_for = [':menu_id' => $menu_id];
        $results = $this->read($a_search_for);
        if ($results !== false && count($results) > 0) {
            $menu_id = $results[0]['menu_id'];
            $parent_id = $results[0]['menu_parent_id'];
            if ($menu_id != $parent_id) {
                return $this->findTopMenu($parent_id);
            }
            else {
                return $menu_id;
            }
        }
        return 0;
    }

    /**
     * Creates the array used to build a menu
     * @param int $page_id   optional, allows a menu to be build on a page id only
     * @param int $parent_id optional, allows only a partial menu to be built
     * @return array
     */
    public function createNavArray($page_id = -1, $parent_id = -1)
    {
        if ($page_id > 0) {
            $results = $this->readMenuByPageId($page_id);
        }
        elseif ($parent_id > 0) {
            $results = $this->readMenuByParentId($parent_id);
        }
        else {
            $results = $this->readMenu();
        }

        // do some sort of loopy loop here

        return $results; // temp
    }

    /**
     * Checks to see the the menu has children.
     * @param int $menu_id
     * @return bool
     */
    private function menuHasChildren($menu_id = -1)
    {
        if ($menu_id == -1) {
            return false;
        }
        $sql = "
            SELECT DISTINCT menu_level
            FROM {$this->db_prefix}menus
            WHERE menu_parent_id = :menu_parent_id
            AND menu_id != menu_parent_id
        ";
        $a_search_values = [':menu_parent_id' => $menu_id, ];
        $results = $this->o_db->search($sql, $a_search_values);
        if ($results !== false && count($results) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getReadMenuString()
    {
        return $this->read_menu_sql;
    }

    /**
     * @param string $read_menu_sql normally not set.
     */
    public function setReadMenuSql($read_menu_sql = '')
    {
        if ($read_menu_sql == '') {
            $read_menu_sql =<<<EOT
SELECT
    p.page_id as 'page_id',
    p.page_url as 'url',
    p.page_description as 'description',
    m.menu_id as 'menu_id',
    m.menu_parent_id as 'parent_id',
    m.menu_name as 'name',
    m.menu_css as 'css',
    m.menu_level as 'level',
    m.menu_order as 'order'
FROM {$this->db_prefix}page as p, {$this->db_prefix}menus as m
WHERE p.page_id = m.menu_page_id
    AND m.menu_active = 1

EOT;
        }
        $this->read_menu_sql = $read_menu_sql;
    }

    /**
     * @return string
     */
    public function getReadMenuOrderSql()
    {
        return $this->read_menu_order_sql;
    }

    /**
     * Sets the string for the ORDER BY statement used in several ReadMenuX methods.
     * @param string $string optional
     */
    public function setReadMenuOrderSql($string = '')
    {
        if ($string == '') {
            $string =<<<EOT
ORDER BY m.menu_level ASC, m.menu_order ASC, m.menu_name ASC
EOT;
        }
        $this->read_menu_order_sql = $string;
    }

    /**
     * Implements the ModelInterface method, getErrorMessage.
     * return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }
}
