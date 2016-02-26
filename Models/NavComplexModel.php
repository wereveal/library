<?php
/**
 *  @brief     Does all the complex database CRUD stuff for the navigation.
 *  @ingroup   ritc_library models
 *  @file      NavComplexModel.php
 *  @namespace Ritc\Library\Models
 *  @class     NavComplexModel
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0 β1
 *  @date      2016-02-25 12:04:44
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 β1 - Initial version                              - 02/25/2016 wer
 *  </pre>
 **/
namespace Ritc\Library\Models;

use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class NavComplexModel
{
    use LogitTraits;

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
    private $select_sql;
    /**
     * @var string
     */
    private $select_order_sql;

    /**
     * NavAllModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }

    /**
     * Returns the search results for all nav records for a navgroup.
     * @param int $ng_id required navgroup id
     * @return array|false
     */
    public function getNavList($ng_id = -1)
    {
        if ($ng_id == -1) {
            return false;
        }
        $where = "AND ng.ng_id = :ng_id\n";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':ng_id' => $ng_id];
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
     * Returns array of the top level nav items for a navgroup.
     * @param int $ng_id
     * @return bool|array
     */
    public function getTopLevelNavList($ng_id = -1)
    {
        if ($ng_id == -1) {
            return false;
        }
        $where = "     AND ng.ng_id = :ng_id\n     AND nav.nav_level = :nav_level\n";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':ng_id' => $ng_id, ':nav_level' => 1];
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
     * Returns an array of nav items based on parent nav id.
     * @param int $parent_id
     * @return bool|mixed
     */
    public function getNavListByParent($parent_id = -1) {
        if ($parent_id == -1) {
            return false;
        }
        $where = "    AND n.nav_parent_id = :parent_id\n    AND n.nav_id != :parent_nav_id";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':parent_id' => $parent_id, ':parent_nav_id' => $parent_id];
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
     * Attempt to get all the sub navigation for the parent, recursively.
     * @param int $parent_id required
     * @return array
     */
    public function getChildrenRecursive($parent_id = -1)
    {
        $a_new_list = array();
        $a_results = $this->getNavListByParent($parent_id);
        if ($a_results !== false && count($a_results) > 0) {
            foreach ($a_results as $a_nav) {
                $a_results = $this->getChildrenRecursive($a_nav['nav_id']);
                $a_new_list[] = [
                    'id'          => $a_nav['nav_id'],
                    'name'        => $a_nav['name'],
                    'url'         => $a_nav['url'],
                    'description' => $a_nav['description'],
                    'class'       => $a_nav['css'],
                    'level'       => $a_nav['level'],
                    'order'       => $a_nav['order'],
                    'parent_id'   => $a_nav['parent_id'],
                    'submenu'     => $a_results
                ];
            }
        }
        return $a_new_list;
    }

    /**
     * Creates the array used to build a nav
     * @param int $ng_id
     * @return array|false
     */
    public function createNavArray($ng_id = -1)
    {
        if ($ng_id == -1) {
            return false;
        }
        $a_new_list = array();
        $a_top_list = $this->getTopLevelNavList($ng_id);
        if ($a_top_list === false) { return false; }
        foreach ($a_top_list as $a_nav) {
            $a_results = $this->getChildrenRecursive($a_nav['nav_id']);
            $a_new_list[] = [
                'id'          => $a_nav['nav_id'],
                'name'        => $a_nav['name'],
                'url'         => $a_nav['url'],
                'description' => $a_nav['description'],
                'class'       => $a_nav['css'],
                'level'       => $a_nav['level'],
                'order'       => $a_nav['order'],
                'parent_id'   => $a_nav['parent_id'],
                'submenu'     => $a_results
            ];
        }
        return $a_new_list;
    }

    /**
     * Returns the number of levels for a given nav list.
     * @param array $a_nav_list
     * @return int
     */
    public function numOfLevels(array $a_nav_list = [])
    {
        $nav_level = 0;
        foreach ($a_nav_list as $a_nav) {
            if ($a_nav['nav_level'] > $nav_level) {
                $nav_level = $a_nav['nav_level'];
            }
        }
        return $nav_level;
    }

    /**
     * Getter for var $select_sql
     * @return string
     */
    public function getSelectSql()
    {
        return $this->select_sql;
    }

    /**
     * @param string $select_sql normally not set.
     */
    public function setSelectSql($select_sql = '')
    {
        if ($select_sql == '') {
            $select_sql =<<<EOT
SELECT
    p.page_id as 'page_id',
    p.page_url as 'url',
    p.page_description as 'description',
    n.nav_id as 'nav_id',
    n.nav_parent_id as 'parent_id',
    n.nav_name as 'name',
    n.nav_css as 'css',
    n.nav_level as 'level',
    n.nav_order as 'order'
FROM
    {$this->db_prefix}page as p,
    {$this->db_prefix}navigation as n,
    {$this->db_prefix}nav_ng_map as map,
    {$this->db_prefix}navgroups as ng
WHERE
    p.page_id = n.nav_page_id
AND n.nav_active = 1
AND ng.ng_id = map.ng_id
AND n.nav_id = map.nav_id

EOT;
        }
        $this->select_sql = $select_sql;
    }

    /**
     * Getter for var $select_order_sql.
     * @return string
     */
    public function getSelectOrderSql()
    {
        return $this->select_order_sql;
    }

    /**
     * Sets the string for the ORDER BY statement used in several ReadNavX methods.
     * @param string $string optional
     */
    public function setSelectOrderSql($string = '')
    {
        if ($string == '') {
            $string =<<<EOT
ORDER BY n.nav_level ASC, n.nav_order ASC, n.nav_name ASC
EOT;
        }
        $this->select_order_sql = $string;
    }

    /**
     * Returns the SQL error message
     * @return string
     */
    public function getErrorMessage()
    {
        return false;
    }
}
