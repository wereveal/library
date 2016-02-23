<?php
/**
 *  @brief     Does all the database CRUD stuff.
 *  @ingroup   ritc_library models
 *  @file      MenusModel.php
 *  @namespace Ritc\Library\Models
 *  @class     MenusModel
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0
 *  @date      2015-11-27 14:59:00
 *  @note <pre><b>Change Log</b>
 *      v1.0.0   - take out of beta                             - 11/27/2015 wer
 *      v1.0.0β1 - Initial version                              - 10/30/2015 wer
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
            'menu_order',
            'menu_active'
        ];
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
     * Returns the search results.
     * @param array $a_search_for    optional defaults to [] and returns all records
     * @param array $a_search_params optional defaults to ['order_by' => 'm.menu_parent_id ASC, m.menu_order ASC, m.menu_name ASC'];
     * @return array
     */
    public function readMenu(
        array $a_search_for    = [],
        array $a_search_params = ['order_by' => 'm.menu_parent_id ASC, m.menu_order ASC, m.menu_name ASC']
    ) {
        $sql_where = $this->o_db->buildSqlWhere($a_search_for, $a_search_params);
        if (strpos($sql_where, 'menu_active') === false) {
            $sql_where = "m.menu.active = 1
            AND {$sql_where}";
        }
        $sql = <<<EOT
            SELECT
                p.page_id as 'id',
                p.page_url as 'url',
                p.page_description as 'description',
                m.menu_parent_id as 'parent_id',
                m.menu_name as 'name',
                m.menu_css as 'css',
                m.menu_order as 'order'
            FROM {$this->db_prefix}page as p, {$this->db_prefix}menus as m
            WHERE p.page_id = m.menu_page_id
            AND {$sql_where}
EOT;
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
     * Creates the array used to build a menu, this is only a two level array
     * @param int $parent_id optional, allows only a partial menu to be built
     * @param int $page_id   optional, allows a menu to be build on a page id only
     * @return array
     */
    public function createNavArray($parent_id = -1, $page_id = -1)
    {
        $get_all = $parent_id === -1 && $page_id === -1;
        $a_search_params = ['order_by' => 'm.menu_parent_id ASC, m.menu_order ASC, m.menu_name ASC'];
        $a_search_for = ['menu_active' => 1];
        if ($get_all) {
            $results = $this->readMenu();
        }
        else {
            if ($parent_id >= 0) {
                $a_search_for['menu_parent_id'] = $parent_id;
            }
            if ($page_id > 0) {
                $a_search_for['menu_page_id'] = $page_id;
            }
            $results = $this->readMenu($a_search_for, $a_search_params);
        }
        if ($results === false) {
            return false;
        }
        else {
            $a_menu_links = array();
            // time to get the top level menus
            foreach ($results as $menu) {
                if ($menu['id'] == $menu['parent_id']) {
                    $a_menu_links[$menu['id']] = [
                        'text'        => $menu['name'],
                        'url'         => $menu['url'],
                        'description' => $menu['description'],
                        'name'        => $menu['name'],
                        'class'       => $menu['class'],
                        'extras'      => '',
                        'submenu'     => array()
                    ];
                }
            }
            // now a second loop
            foreach ($results as $menu) {
                foreach ($a_menu_links as $key => $parent_menu) {
                    if ($menu['parent_id'] == $key) {
                        $a_menu_links[$key]['submenu'][$menu['id']] = [
                            'text'        => $menu['name'],
                            'url'         => $menu['url'],
                            'description' => $menu['description'],
                            'name'        => $menu['name'],
                            'class'       => $menu['class'],
                            'extras'      => '',
                            'submenu'     => array()
                        ];
                    }
                    else {
                        if (count($a_menu_links[$key]['submenu']) > 0) {
                            foreach
                        }
                    }
                }
            }
            return $a_menu_links;
        }
    }

    private function addSubMenu(array $a_menu = [], array $a_parent_menu = [])
    {

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
