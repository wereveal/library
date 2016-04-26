<?php
/**
 * @brief     Does all the complex database CRUD stuff for the navigation.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/NavComplexModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.4
 * @date      2016-04-23 15:09:58
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.4 - added new method save                        - 2016-04-23 wer
 * - v1.0.0-alpha.3 - Changed sql, removed redundant methods       - 2016-04-18 wer
 * - v1.0.0-alpha.2 - Added new method getNavListAll               - 2016-04-15 wer
 * - v1.0.0-alpha.1 - Refactoring changes                          - 2016-03-19 wer
 * - v1.0.0-alpha.0 - Initial version                              - 2016-02-25 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class NavComplexModel.
 * @class   NavComplexModel
 * @package Ritc\Library\Models
 */
class NavComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /** @var  NavgroupsModel */
    private $o_ng;
    /** @var string */
    private $select_sql;
    /** @var string */
    private $select_order_sql;

    /**
     * NavAllModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'navigation');
        $this->setSelectSql();
        $this->setSelectOrderSql();
        $this->o_ng = new NavgroupsModel($this->o_db);
    }

    /**
     * Returns the search results for all nav records for a navgroup.
     * @param int $ng_id Optional, will use the default navgroup if not provided.
     * @return array|false
     */
    public function getNavList($ng_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($ng_id == -1) {
            $ng_id = $this->o_ng->retrieveDefaultNavgroup();
            if ($ng_id == -1) {
                return false;
            }
        }
        $where = "AND ng.ng_id = :ng_id\n";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':ng_id' => $ng_id];
        $this->logIt("SQL: " . $sql, LOG_OFF, $meth . __LINE__);
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
     * Gets all the navigation records with related values.
     * @return bool|array
     */
    public function getNavListAll()
    {
        $meth = __METHOD__ . '.';
        $select_sql =<<<EOT
SELECT
    n.nav_id as 'nav_id',
    n.nav_parent_id as 'parent_id',
    n.url_id as 'url_id',
    u.url_text as 'url',
    n.nav_name as 'name',
    n.nav_text as 'text',
    n.nav_description as 'description',
    n.nav_css as 'css',
    n.nav_level as 'level',
    n.nav_order as 'order'
FROM {$this->db_prefix}urls as u
JOIN {$this->db_prefix}navigation as n
    ON u.url_id = n.url_id
ORDER BY
    n.nav_parent_id ASC,
    n.nav_level ASC,
    n.nav_order ASC,
    n.nav_name ASC
EOT;
        $this->logIt("SQL: {$select_sql}", LOG_OFF, $meth . __LINE__);
        $results = $this->o_db->search($select_sql);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
        else {
            return $results;
        }
    }

    /**
     * Returns the Navigation list by navgroup name.
     * @param string $navgroup_name Optional, will use default navgroup if not supplied.
     * @return bool|mixed
     */
    public function getNavListByName($navgroup_name = '')
    {
        $meth = __METHOD__ . '.';
        if ($navgroup_name == '') {
            $navgroup_name = $this->o_ng->retrieveDefaultNavgroupName();
        }
        $where = "AND ng.ng_name = :ng_name\n";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':ng_name' => $navgroup_name];
        $this->logIt("SQL: " . $sql, LOG_OFF, $meth . __LINE__);
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
     * @param int $parent_id Required. Parent id of the navigation record.
     * @param int $ng_id     Required. Id of the navigation group it belongs in.
     * @return bool|mixed
     */
    public function getNavListByParent($parent_id = -1, $ng_id = -1) {
        $meth = __METHOD__ . '.';
        if ($parent_id == -1 || $ng_id == -1) {
            return false;
        }
        $where =<<<EOT
AND n.nav_parent_id = :parent_id
AND n.nav_id != :parent_nav_id
AND map.ng_id = :map_ng_id

EOT;
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [
            ':parent_id'     => $parent_id,
            ':parent_nav_id' => $parent_id,
            ':map_ng_id'     => $ng_id
        ];
        $this->logIt("SQL: {$sql}", LOG_OFF, $meth . __LINE__);
        $log_message = 'search for ' . var_export($a_search_for, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
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
     * Returns a single nav record by nav_id with the url_text.
     * @param int $nav_id
     * @return bool|array
     */
    public function getNavRecord($nav_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($nav_id == -1) {
            return false;
        }
        $sql_and =<<<EOT
AND n.nav_id = :nav_id

EOT;
        $sql = $this->select_sql . $sql_and . $this->select_order_sql;
        $this->logIt("SQL:\n{$sql}", LOG_ON, $meth . __LINE__);
        $a_search_for = [':nav_id' => $nav_id];
        $results = $this->o_db->search($sql, $a_search_for);
        if ($results === false || !isset($results[0])) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
        else {
            return $results[0];
        }
    }

    /**
     * Attempt to get all the sub navigation for the parent, recursively.
     * @param int $parent_id Required. Parent id of the navigation record.
     * @param int $ng_id     Required. Id of the navigation group it belongs in.
     * @return array
     */
    public function getChildrenRecursive($parent_id = -1, $ng_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($parent_id == -1 || $ng_id == -1) {
            return false;
        }
        $a_new_list = array();
        $a_results = $this->getNavListByParent($parent_id, $ng_id);
        $log_message = 'Parent Values ' . var_export($a_results, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        if ($a_results !== false && count($a_results) > 0) {
            foreach ($a_results as $a_nav) {
                $a_results = $this->getChildrenRecursive($a_nav['nav_id'], $ng_id);
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
        else {
            return [];
        }
    }

    /**
     * Returns array of the top level nav items for a navgroup.
     * @param int $ng_id
     * @return bool|array
     */
    public function getTopLevelNavList($ng_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($ng_id == -1) {
            return false;
        }
        $where = "AND ng.ng_id = :ng_id\nAND n.nav_level = :nav_level\n";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':ng_id' => $ng_id, ':nav_level' => 1];
        $this->logIt("SQL: " . $sql, LOG_OFF, $meth . __LINE__);
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
     * Creates the array used to build a nav
     * @param int $ng_id
     * @return array|false
     */
    public function createNavArray($ng_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($ng_id == -1) {
            $ng_id = $this->o_ng->retrieveDefaultNavgroup();
            if ($ng_id == -1) {
                return false;
            }
        }
        $a_new_list = array();
        $a_top_list = $this->getTopLevelNavList($ng_id);
        $this->logIt("top level nav: " . var_export($a_top_list, true), LOG_OFF, $meth . __LINE__);
        if ($a_top_list === false) { return false; }
        foreach ($a_top_list as $a_nav) {
            $this->logIt(var_export($a_nav, true), LOG_OFF, $meth . __LINE__);
            $a_results = $this->getChildrenRecursive($a_nav['nav_id'], $ng_id);
            $log_message = 'Recursive Results ' . var_export($a_results, TRUE);
            $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

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
    n.nav_id as 'nav_id',
    n.nav_parent_id as 'parent_id',
    n.url_id as 'url_id',
    u.url_text as 'url',
    n.nav_name as 'name',
    n.nav_text as 'text',
    n.nav_description as 'description',
    n.nav_css as 'css',
    n.nav_level as 'level',
    n.nav_order as 'order',
    ng.ng_id,
    ng.ng_name
FROM {$this->db_prefix}urls as u
JOIN {$this->db_prefix}navigation as n
    ON n.url_id = u.url_id
JOIN {$this->db_prefix}nav_ng_map as map
    ON n.nav_id = map.nav_id
JOIN {$this->db_prefix}navgroups as ng
    ON ng.ng_id = map.ng_id
WHERE n.nav_active = 1

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
ORDER BY
    ng.ng_id ASC,
    n.nav_parent_id ASC,
    n.nav_level ASC,
    n.nav_order ASC,
    n.nav_name ASC
EOT;
        }
        $this->select_order_sql = $string;
    }

    /**
     * Does a transaction saving the navigation record and associated nav to navgroup map record.
     * This handles both new records and updating old ones.
     * @param array $a_post
     * @return bool
     */
    public function save(array $a_post = [])
    {
        $meth = __METHOD__ . '.';
        if ($a_post == []) {
            $this->error_message = "An array with the save values was not supplied.";
            return false;
        }
        $a_possible_keys = [
            'nav_id',
            'url_id',
            'nav_parent_id',
            'ng_id',
            'nav_name',
            'nav_text',
            'nav_description',
            'nav_css',
            'nav_level',
            'nav_order',
            'nav_active'
        ];
        $action = '';
        foreach ($a_possible_keys as $key_name) {
            switch ($key_name) {
                case 'nav_id':
                    if (isset($a_post[$key_name]) && $a_post[$key_name] != '') {
                        $action = 'update';
                    }
                    else {
                        $action = 'create';
                    }
                    break;
                case 'url_id':
                    if (!isset($a_post[$key_name]) || intval($a_post[$key_name]) == 0) {
                        $action = 'error';
                        $this->error_message .= 'A URL must be selected. ';
                    }
                    break;
                case 'nav_parent_id':
                    if (!isset($a_post[$key_name]) || intval($a_post[$key_name]) == 0) {
                        $action = 'error';
                        $this->error_message .= 'A Parent must be selected. ';
                    }
                    break;
                case 'ng_id':
                    if (!isset($a_post[$key_name]) || intval($a_post[$key_name]) == 0) {
                        $action = 'error';
                        $this->error_message .= 'A navigation group must be selected. ';
                    }
                    break;
                case 'nav_name':
                    if (!isset($a_post[$key_name]) || $a_post[$key_name] == '') {
                        $action = 'error';
                        $this->error_message .= 'A Name must be given for the navigation record. ';
                    }
                    break;
                case 'nav_text':
                case 'nav_description':
                    if (!isset($a_post[$key_name]) || $a_post[$key_name] == '') {
                        $a_post[$key_name] = $a_post['nav_name'];
                    }
                    break;
                case 'nav_level':
                case 'nav_order':
                case 'nav_active':
                    if (!isset($a_post[$key_name]) || intval($a_post[$key_name]) == 0) {
                        $a_post[$key_name] = 1;
                    }
                    break;
                default:
                // no default action needed
            }
        }
        if ($action == 'error' || $action == '') {
            return false;
        }
        $o_nav = new NavigationModel($this->o_db);
        $o_map = new NavNgMapModel($this->o_db);
        $old_ng_id = false;
        if ($action == 'update') {
            $old_record = $this->getNavRecord($a_post['nav_id']);
            $log_message = 'Old Nav Record ' . var_export($old_record, TRUE);
            $this->logIt($log_message, LOG_ON, $meth . __LINE__);

            if ($old_record === false) {
                $this->error_message .= 'Could not retrieve old navigation record. ';
                return false;
            }
            $old_ng_id = $old_record['ng_id'] != $a_post['ng_id']
                ? $old_record['ng_id']
                : false;
        }
        $this->o_db->startTransaction();
        if ($action = 'create') {
            $results = $o_nav->create($a_post);
            if ($results) {
                $a_values = [
                    'ng_id'  => $a_post['ng_id'],
                    'nav_id' => $a_post['nav_id']
                ];
                $results = $o_map->create($a_values);
            }
        }
        else {
            $results = $o_nav->update($a_post);
            if ($results) {
                if ($old_ng_id) {
                    $results = $o_map->delete($old_ng_id, $a_post['nav_id']);
                    if ($results) {
                        $a_values = [
                            'ng_id'  => $a_post['ng_id'],
                            'nav_id' => $a_post['nav_id']
                        ];
                        $results = $o_map->create($a_values);
                    }
                }
            }
        }
        if ($results) {
            return $this->o_db->commitTransaction();
        }
        else {
            $this->error_message .= $this->o_db->retrieveFormatedSqlErrorMessage();
            $this->o_db->rollbackTransaction();
            return false;
        }
    }
}
