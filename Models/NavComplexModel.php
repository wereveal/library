<?php
/**
 * Class NavComplexModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the database CRUD stuff for the page table plus other app/business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.1.0
 * @date    2018-05-24 16:26:54
 * @change_log
 * - v1.1.0         - Added new method to return site map links         - 2018-05-24 wer
 *                    Made a lot of changes to facilitate new method
 * - v1.0.0         - initial production version                        - 2017-12-12 wer
 * - v1.0.0-alpha.7 - Refactored to use ModelException                  - 2017-06-15 wer
 * - v1.0.0-alpha.4 - added new method save                             - 2016-04-23 wer
 * - v1.0.0-alpha.3 - Changed sql, removed redundant methods            - 2016-04-18 wer
 * - v1.0.0-alpha.2 - Added new method getNavListAll                    - 2016-04-15 wer
 * - v1.0.0-alpha.0 - Initial version                                   - 2016-02-25 wer
 */
class NavComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /** @var \Ritc\Library\Services\Di */
    private $o_di;
    /** @var \Ritc\Library\Models\NavgroupsModel */
    private $o_ng;
    /** @var string */
    private $select_sql;
    /** @var string */
    private $select_order_sql;

    /**
     * NavAllModel constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di = $o_di;
        $this->setupElog($o_di);
        /** @var DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->o_db = $o_db;
        $this->setupProperties($o_db, 'navigation');
        $this->o_ng = new NavgroupsModel($this->o_db);
        $this->o_ng->setupElog($o_di);
        $this->setSelectSql();
        $this->setSelectOrderSql();
    }

    /**
     * Attempt to get all the sub navigation for the parent, recursively.
     * @param int $parent_id Required. Parent id of the navigation record.
     * @param int $ng_id     Required. Id of the navigation group it belongs in.
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getChildrenRecursive($parent_id = -1, $ng_id = -1)
    {
        if ($parent_id == -1 || $ng_id == -1) {
            throw new ModelException('Missing required value.', 220);
        }
        $a_new_list = [];
        try {
            $a_results = $this->getNavListByParent($parent_id, $ng_id);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to get the nav list.', 210, $e);
        }

        if (count($a_results) > 0) {
            foreach ($a_results as $a_nav) {
                try {
                    $a_results = $this->getChildrenRecursive($a_nav['nav_id'], $ng_id);
                }
                catch (ModelException $e) {
                    throw new ModelException('A problem getting the children.', 210, $e);
                }
                $a_nav['submenu'] = $a_results;
                $a_new_list[] = $a_nav;
            }
            return $a_new_list;
        }
        else {
            return [];
        }
    }

    /**
     * Returns the search results for all nav records for a navgroup.
     * @param int $ng_id Optional, will use the default navgroup if not provided.
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getNavList($ng_id = -1)
    {
        if ($ng_id == -1) {
            try {
                $ng_id = $this->o_ng->retrieveDefaultId();
            }
            catch (ModelException $e) {
                throw new ModelException('Missing required navgroup id.', 220, $e);
            }
        }
        $where = "AND ng.ng_id = :ng_id\n";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':ng_id' => $ng_id];
        try {
            return $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            throw new ModelException($this->error_message, 210, $e);
        }
    }

    /**
     * Gets all the navigation records with related values.
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getNavListAll()
    {
        $select_sql = trim(str_replace("WHERE n.nav_active = 'true'", '', $this->select_sql));
        $order_by_sql = "
            ORDER BYi ng.ng_id ASC,
                n.nav_parent_id ASC,
                n.nav_level ASC,
                n.nav_order ASC,
                n.nav_name ASC
        ";
        $sql = $select_sql . $order_by_sql;
        try {
            return $this->o_db->search($sql);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            throw new ModelException($this->error_message, 210, $e);
        }
    }

    /**
     * Returns the Navigation list by navgroup name.
     * @param string $navgroup_name Optional, will use default navgroup if not supplied.
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getNavListByName($navgroup_name = '')
    {
        if ($navgroup_name == '') {
            try {
                $navgroup_name = $this->o_ng->retrieveDefaultName();
            }
            catch (ModelException $e) {

            }
        }
        $where = "AND ng.ng_name = :ng_name\n";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':ng_name' => $navgroup_name];
        try {
            $results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            $this->error_message = 'Could not find the record: ' . $this->o_db->getSqlErrorMessage();
            throw new ModelException($this->error_message, 210, $e);
        }
        return $results;
    }

    /**
     * Returns an array of nav items based on parent nav id.
     * @param int $parent_id Required. Parent id of the navigation record.
     * @param int $ng_id     Required. Id of the navigation group it belongs in.
     * @return bool|array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getNavListByParent($parent_id = -1, $ng_id = -1) {
        if ($parent_id == -1 || $ng_id == -1) {
            return false;
        }
        $where = "
            AND n.nav_parent_id = :parent_id
            AND n.nav_id != :parent_nav_id
            AND map.ng_id = :map_ng_id
        ";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [
            ':parent_id'     => $parent_id,
            ':parent_nav_id' => $parent_id,
            ':map_ng_id'     => $ng_id
        ];
        try {
            $results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            $this->error_message = 'Could not get Nav list by parent';
            throw new ModelException($this->error_message, 210, $e);
        }
        return $results;
    }

    /**
     * Returns a single nav record by nav_id with the url_text.
     * @param int $nav_id
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getNavRecord($nav_id = -1)
    {
        if ($nav_id == -1) {
            return false;
        }
        $sql_and = "AND n.nav_id = :nav_id\n";
        $sql = $this->select_sql . $sql_and . $this->select_order_sql;
        $a_search_for = [':nav_id' => $nav_id];
        try {
            $results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to get the nav record.';
            throw new ModelException($this->error_message, 210, $e);
        }
        return $results[0];
    }

    /**
     * Returns an array that maps the site links available for the auth level provided.
     * @param int $auth_level
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getSitemap($ng_name = 'SiteMap', $auth_level = 0)
    {
        $sql = $this->select_sql . "
            AND n.nav_level = :nav_level
            AND ng.ng_name = :ng_name
            ORDER BY n.nav_order";
        $a_search_for = [
            ':nav_level' => 1,
            ':ng_name'   => $ng_name
        ];
        try {
            $results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            $this->error_message = 'Could not get the record: ' . $this->o_db->getSqlErrorMessage();
            throw new ModelException($this->error_message, 210, $e);
        }
        foreach ($results as $key => $record) {
            if ($record['auth_level'] >= $auth_level) {
                $a_children = $this->getChildrenRecursive($record['parent_id'], $record['ng_id']);
                foreach ($a_children as $child_key => $child_record) {
                    if ($child_record['auth_level'] < $auth_level) {
                        unset($a_children[$child_key]);
                    }
                }
                $results[$key]['submenu'] = $a_children;
            }
            else {
                unset($results[$key]);
            }
        }
        return $results;
    }

    /**
     * Creates an array that can be used with the sitemap_xml twig file.
     * @param int $auth_level
     * @return array
     */
    public function getSitemapForXml($auth_level = 0)
    {
        try {
            $a_results = $this->getNavListAll();
        }
        catch (ModelException $e) {
            return [];
        }
        $a_urls = [];
        foreach ($a_results as $key => $a_url) {
            if ($a_url['auth_level'] <= $auth_level) {
                $a_urls[] = [
                    'loc'        => $a_url['url'],
                    'changefreq' => '',
                    'lastmod'    => '',
                    'priority'   => ''
                ];
            }
        }
        return $a_urls;
    }

    /**
     * Returns array of the top level nav items for a navgroup.
     * @param int $ng_id
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getTopLevelNavList($ng_id = -1)
    {
        if ($ng_id == -1) {
            throw new ModelException('Missing required id.', 220);
        }
        $where = "AND ng.ng_id = :ng_id\nAND n.nav_level = :nav_level\n";
        $sql = $this->select_sql . $where . $this->select_order_sql;
        $a_search_for = [':ng_id' => $ng_id, ':nav_level' => 1];
        try {
            $results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            $this->error_message = 'Could not get the record: ' . $this->o_db->getSqlErrorMessage();
            throw new ModelException($this->error_message, 210, $e);
        }
        return $results;
    }

    /**
     * Creates the array used to build a nav
     * @param int $ng_id
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createNavArray($ng_id = -1)
    {
        if ($ng_id == -1) {
            $ng_id = $this->o_ng->retrieveDefaultId();
            if ($ng_id == -1) {
                throw new ModelException('Missing required id.', 220);
            }
        }
        $a_new_list = array();
        try {
            $a_top_list = $this->getTopLevelNavList($ng_id);
        }
        catch (ModelException $e) {
            $this->error_message = 'Could not get the nav list: ' . $this->o_db->getSqlErrorMessage();
            throw new ModelException($this->error_message, 10, $e);
        }
        foreach ($a_top_list as $a_nav) {
            try {
                $a_results = $this->getChildrenRecursive($a_nav['nav_id'], $ng_id);
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to get nav list: ' . $this->o_db->getSqlErrorMessage();
                throw new ModelException($this->error_message, 210, $e);
            }
            $a_nav['submenu'] = $a_results;
            $a_new_list[] = $a_nav;
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
    n.nav_id as nav_id,
    n.nav_parent_id as parent_id,
    n.url_id as url_id,
    u.url_text as url,
    n.nav_name as name,
    n.nav_text as text,
    n.nav_description as description,
    n.nav_css as css,
    n.nav_level as level,
    n.nav_order as order,
    n.nav_immutable,
    ng.ng_id,
    ng.ng_name,
    r.route_id,
    g.group_id,
    g.group_auth_level as auth_level
FROM {$this->lib_prefix}urls as u
JOIN {$this->lib_prefix}navigation as n
    ON n.url_id = u.url_id
JOIN {$this->lib_prefix}nav_ng_map as map
    ON n.nav_id = map.nav_id
JOIN {$this->lib_prefix}navgroups as ng
    ON ng.ng_id = map.ng_id
JOIN {$this->lib_prefix}routes as r
    ON r.url_id = u.url_id
JOIN {$this->lib_prefix}routes_group_map as rgm
    ON rgm.route_id = r.route_id
JOIN {$this->lib_prefix}groups as g
    ON g.group_id = rgm.group_id
WHERE n.nav_active = 'true'

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
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function save(array $a_post = [])
    {
        if ($a_post == []) {
            $this->error_message = "An array with the save values was not supplied.";
            throw new ModelException($this->error_message, 120);
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
                case 'nav_active':
                    if (!isset($a_post[$key_name]) || $a_post[$key_name] == 'false') {
                        $a_post[$key_name] = 'true';
                    }
                    break;
                case 'nav_level':
                case 'nav_order':
                    if (!isset($a_post[$key_name]) || intval($a_post[$key_name]) == 0) {
                        $a_post[$key_name] = 1;
                    }
                    break;
                default:
                // no default action needed
            }
        }
        if ($action == 'error' || $action == '') {
            throw new ModelException($this->error_message, 120);
        }
        $o_nav = new NavigationModel($this->o_db);
        $o_map = new NavNgMapModel($this->o_db);
        $old_ng_id = false;
        if ($action == 'update') {
            try {
                $old_record = $this->getNavRecord($a_post['nav_id']);
            }
            catch (ModelException $e) {
                $this->error_message = 'Not able to get the old nav record: ' . $this->o_db->getSqlErrorMessage();
                throw new ModelException($this->error_message, 210, $e);
            }
            $old_ng_id = $old_record['ng_id'] != $a_post['ng_id']
                ? $old_record['ng_id']
                : false;
        }
        try {
            $this->o_db->startTransaction();
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
        if ($action == 'create') {
            try {
                $results = $o_nav->create($a_post);
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to create the navigation record: ' . $this->o_db->getSqlErrorMessage();
                throw new ModelException($this->error_message, 110, $e);
            }
            if ($results) {
                $a_values = [
                    'ng_id'  => $a_post['ng_id'],
                    'nav_id' => $a_post['nav_id']
                ];
                try {
                    $results = $o_map->create($a_values);
                }
                catch (ModelException $e) {
                    $this->error_message = 'Unable to create the map: ' . $this->o_db->getSqlErrorMessage();
                    throw new ModelException($this->error_message, 110, $e);
                }
            }
        }
        else {
            try {
                $results = $o_nav->update($a_post);
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to update the navigation record', 300, $e);
            }
            if ($results) {
                if ($old_ng_id) {
                    try {
                        $results = $o_map->delete($old_ng_id, $a_post['nav_id']);
                    }
                    catch (ModelException $e) {
                        $this->error_message = 'Unable to delete the old map record: ' . $this->o_db->getSqlErrorMessage();
                        throw new ModelException($this->error_message, 10, $e);
                    }
                    if ($results) {
                        $a_values = [
                            'ng_id'  => $a_post['ng_id'],
                            'nav_id' => $a_post['nav_id']
                        ];
                        try {
                            $results = $o_map->create($a_values);
                        }
                        catch (ModelException $e) {
                            throw new ModelException('Unable to create a new map record.', 110, $e);
                        }
                    }
                }
            }
        }
        if ($results) {
            try {
                $this->o_db->commitTransaction();
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to commit the transaction.';
                throw new ModelException($this->error_message, 13, $e);
            }
            return true;
        }
        else {
            $this->error_message .= $this->o_db->retrieveFormattedSqlErrorMessage();
            try {
                $this->o_db->rollbackTransaction();
                throw new ModelException($this->error_message, 10);
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to do the operation', 17);
            }
        }
    }
}
