<?php
/**
 *  @brief     Does all the complex database CRUD stuff for the navigation.
 *  @ingroup   ritc_library models
 *  @file      NavAllModel.php
 *  @namespace Ritc\Library\Models
 *  @class     NavAllModel
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

class NavAllModel
{
    use LogitTraits;

    /**
     * @var string
     */
    private $error_message;
    /**
     * @var \Ritc\Library\Services\DbModel
     */
    private $o_db;
    /**
     * @var \Ritc\Library\Models\NavigationModel
     */
    private $o_nav;
    /**
     * @var \Ritc\Library\Models\NavgroupsModel
     */
    private $o_ng;
    /**
     * @var \Ritc\Library\Models\NavNgMapModel
     */
    private $o_map;
    /**
     * @var string
     */
    private $read_nav_sql;
    /**
     * @var string
     */
    private $read_nav_order_sql;

    /**
     * NavAllModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
        $this->o_nav     = new NavigationModel($o_db);
        $this->o_ng      = new NavgroupsModel($o_db);
        $this->o_map     = new NavNgMapModel($o_db);
    }

    /**
     * Returns the search results for all nav records.
     * @return array
     */
    public function readNav() {
        $sql = $this->read_nav_sql . $this->read_nav_order_sql;
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
     * Returns the nav items based on level and parent ID.
     * @param int $level           required
     * @param int $nav_parent_id  optional, if not provided, all navigation of that level are returned.
     * @return array|bool
     */
    public function readNavByLevel($level = -1, $nav_parent_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($level < 1) {
            return false;
        }
        elseif ($nav_parent_id == -1) {
            $where = "    AND m.nav_level = :nav_level\n";
            $a_search_for = [':nav_level' => $level];
        }
        else {
            $where = "    AND m.nav_level = :nav_level\n    AND m.nav_parent_id = :nav_parent_id\n";
            $a_search_for = [
                ':nav_level'     => $level,
                ':nav_parent_id' => $nav_parent_id
            ];
        }
        $sql = $this->read_nav_sql . $where . $this->read_nav_order_sql;
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
     * Returns an array of nav records based on page id.
     * Gets the top parent and all the children that are related to that page.
     * @param int $page_id
     * @return array|bool
     */
    public function readNavByPageId($page_id = -1)
    {
        if ($page_id == -1) {
            return false;
        }
        $a_search_for = [':nav_page_id' => $page_id];
        $results = $this->read($a_search_for);
        if ($results !== false && count($results) > 0) {
            $nav_id = $results[0]['nav_id'];
            $parent_id = $results[0]['nav_parent_id'];
            if ($nav_id != $parent_id) {
                $nav_id = $this->findTopNav($nav_id);
            }
            return $this->readNavByParentId($nav_id);
        }
        else {
            return false;
        }
    }

    /**
     * Returns an array of nav records that have the given nav id as nav_parent_id.
     * Note that the nav_id needs to be a parent of nav records.
     * Else it will return no records.
     * @param int $nav_id
     * @return bool|array
     */
    public function readNavByParentId($nav_id = -1)
    {
        if ($nav_id < 1) {
            return false;
        }
        $sql = $this->read_nav_sql;
        $where = "    AND m.nav_parent_id = :nav_parent_id\n";
        $sql .= $where . $this->read_nav_order_sql;
        $a_search_for = [':nav_parent_id' => $nav_id];
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
     * Returns the nav id of the top most nav for the given nav id.
     * @param int $nav_id
     * @return int
     */
    public function findTopNav($nav_id = -1)
    {
        if ($nav_id == -1) {
            return 0;
        }
        $a_search_for = [':nav_id' => $nav_id];
        $results = $this->read($a_search_for);
        if ($results !== false && count($results) > 0) {
            $nav_id = $results[0]['nav_id'];
            $parent_id = $results[0]['nav_parent_id'];
            if ($nav_id != $parent_id) {
                return $this->findTopNav($parent_id);
            }
            else {
                return $nav_id;
            }
        }
        return 0;
    }

    /**
     * Creates the array used to build a nav
     * @param int $page_id   optional, allows a nav to be build on a page id only
     * @param int $parent_id optional, allows only a partial nav to be built
     * @return array
     */
    public function createNavArray($page_id = -1, $parent_id = -1)
    {
        if ($page_id > 0) {
            $results = $this->readNavByPageId($page_id);
        }
        elseif ($parent_id > 0) {
            $results = $this->readNavByParentId($parent_id);
        }
        else {
            $results = $this->readNav();
        }

        // do some sort of loopy loop here

        return $results; // temp
    }

    /**
     * Checks to see the the nav has children.
     * @param int $nav_id
     * @return bool
     */
    public function navHasChildren($nav_id = -1)
    {
        if ($nav_id == -1) {
            return false;
        }
        $sql = "
            SELECT DISTINCT nav_level
            FROM {$this->db_prefix}navigation
            WHERE nav_parent_id = :nav_parent_id
            AND nav_id != nav_parent_id
        ";
        $a_search_values = [':nav_parent_id' => $nav_id, ];
        $results = $this->o_db->search($sql, $a_search_values);
        if ($results !== false && count($results) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getReadNavString()
    {
        return $this->read_nav_sql;
    }

    /**
     * @param string $read_nav_sql normally not set.
     */
    public function setReadNavSql($read_nav_sql = '')
    {
        if ($read_nav_sql == '') {
            $read_nav_sql =<<<EOT
SELECT
    p.page_id as 'page_id',
    p.page_url as 'url',
    p.page_description as 'description',
    m.nav_id as 'nav_id',
    m.nav_parent_id as 'parent_id',
    m.nav_name as 'name',
    m.nav_css as 'css',
    m.nav_level as 'level',
    m.nav_order as 'order'
FROM {$this->db_prefix}page as p, {$this->db_prefix}navigation as m
WHERE p.page_id = m.nav_page_id
    AND m.nav_active = 1

EOT;
        }
        $this->read_nav_sql = $read_nav_sql;
    }

    /**
     * @return string
     */
    public function getReadNavOrderSql()
    {
        return $this->read_nav_order_sql;
    }

    /**
     * Sets the string for the ORDER BY statement used in several ReadNavX methods.
     * @param string $string optional
     */
    public function setReadNavOrderSql($string = '')
    {
        if ($string == '') {
            $string =<<<EOT
ORDER BY m.nav_level ASC, m.nav_order ASC, m.nav_name ASC
EOT;
        }
        $this->read_nav_order_sql = $string;
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