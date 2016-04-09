<?php
/**
 * @brief     Does multi-table queries.
 * @ingroup   ritc_models
 * @file      PageComplexModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-04-08 10:35:17
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2016-04-08 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class PageComplexModel.
 * @class   PageComplexModel
 * @package Ritc\Library\Models
 */
class PageComplexModel
{
    use LogitTraits, DbUtilityTraits;

    private $select_sql = '';

    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'page');
        $this->setSelectSql();
    }

    /**
     * @param array $a_search_for        Optional, defaults to returning all records
     *                                   Allowed search keys ['url_id', 'url_text', 'page_id', 'page_title']
     * @param array $a_search_parameters Optional, defaults to ['order_by' => 'page_id ASC']
     * @return bool|array
     */
    public function readPageValues(array $a_search_for = [], array $a_search_parameters = [])
    {
        $meth = __METHOD__ . '.';
        $a_allowed_keys = ['url_id', 'url_text', 'page_id', 'page_title'];
        $a_search_for = Arrays::removeUndesiredPairs($a_search_for, $a_allowed_keys);
        if (!isset($a_search_parameters['order_by'])) {
            $a_search_parameters['order_by'] = 'page_id ASC';
        }
        $sql_where = $this->buildSqlWhere($a_search_for, $a_search_parameters);
        $sql = $this->select_sql . $sql_where;
        $this->logIt("SQL: {$sql}", LOG_OFF, $meth . __LINE__);
        return $this->o_db->search($sql, $a_search_for);
    }

    /**
     * Returns records from the join of the page and urls tables based on url id.
     * @param int $url_id Required
     * @return array|bool
     */
    public function readPageValuesByUrlId($url_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($url_id == -1) {
            return false;
        }
        $this->setSelectSql();
        $sql =<<<SQL
{$this->select_sql}
WHERE p.url_id = :url_id
AND p.url_id = u.url_id
SQL;
        $this->logIt("sql: $sql", LOG_ON, $meth . __LINE__);
        return $this->o_db->search($sql, [':url_id' => $url_id]);

    }

    /**
     * Returns one or more records based on the url ordered by the url text.
     * @param string $url_text Required
     * @return bool|array
     */
    public function readPageValuesByUrlText($url_text = '')
    {
        if ($url_text == '') {
            return false;
        }
        $url_text = str_replace(SITE_URL, '', $url_text);
        $sql =<<<SQL
{$this->select_sql}
WHERE u.url_text = :url_text
AND p.url_id = u.url_id
ORDER By u.url_text
SQL;
        return $this->o_db->search($sql, [':url_text' => $url_text]);
    }

    private function setSelectSql($the_string = '')
    {
        $meth = __METHOD__ . '.';
        $this->logIt("db prefix: " . $this->db_prefix, LOG_ON, $meth . __LINE__);
        if ($the_string != '') {
            $this->select_sql = $the_string;
        }
        else {
            $this->select_sql =<<<SQL
SELECT p.page_id, p.page_type, p.page_title, p.page_description, 
       p.page_base_url, p.page_lang, p.page_charset, 
       u.url_id, u.url_text, u.url_type
FROM {$this->db_prefix}page as p, {$this->db_prefix}urls as u
SQL;
        }

    }
}