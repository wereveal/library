<?php
/**
 * @brief     Does multi-table queries.
 * @ingroup   lib_models
 * @file      PageComplexModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.3
 * @date      2017-02-11 12:29:54
 * @note Change Log
 * - v1.0.0-alpha.3 - bug fixes                     - 2017-02-11 wer
 * - v1.0.0-alpha.2 - refactoring reflected here    - 2017-01-27 wer
 * - v1.0.0-alpha.1 - bug fix                       - 2016-04-28 wer
 * - v1.0.0-alpha.0 - Initial version               - 2016-04-08 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
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

    /** @var \Ritc\Library\Services\Di */
    private $o_di;
    /** @var \Ritc\Library\Models\PageModel  */
    private $o_page;
    /** @var \Ritc\Library\Models\UrlsModel  */
    private $o_urls;
    /** @var string  */
    private $select_sql = '';

    /**
     * PageComplexModel constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di = $o_di;
        /** @var DbModel $o_db */
        $o_db = $o_di->get('db');
        /** @var Elog $o_elog */
        $o_elog = $o_di->get('elog');
        $this->o_db = $o_db;
        $this->o_elog = $o_elog;
        $this->o_page = new PageModel($o_db);
        $this->o_urls = new UrlsModel($o_db);
        if (DEVELOPER_MODE) {
            $this->o_page->setElog($o_elog);
            $this->o_urls->setElog($o_elog);
        }
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
        $a_allowed_keys = ['u.url_id', 'u.url_text', 'p.page_id', 'p.page_title'];

        if (!isset($a_search_parameters['order_by'])) {
            $a_search_parameters['order_by'] = 'page_id ASC';
        }
        if (!isset($a_search_parameters['where_exists'])) {
            $a_search_parameters['where_exists'] = true;
        }
        $sql_where = $this->buildSqlWhere($a_search_for, $a_search_parameters, $a_allowed_keys);
        $sql = $this->select_sql . "\n" . $sql_where;
        $this->logIt("SQL: {$sql}", LOG_ON, $meth . __LINE__);
        $log_message = 'Search Parameters ' . var_export($a_search_for, TRUE);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);

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
WHERE p.url_id = u.url_id
AND p.url_id = :url_id
SQL;
        $this->logIt("sql: $sql", LOG_OFF, $meth . __LINE__);
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

    /**
     * Sets the class property select_sql.
     * @param string $the_string Optional, normally not specified.
     */
    private function setSelectSql($the_string = '')
    {
        $meth = __METHOD__ . '.';
        $this->logIt("db prefix: " . $this->db_prefix, LOG_OFF, $meth . __LINE__);
        if ($the_string != '') {
            $this->select_sql = $the_string;
        }
        else {
            $a_page_fields = $this->o_page->getDbFields();
            $a_urls_fields = $this->o_urls->getDbFields();
            $page_prefix = $this->o_page->getDbPrefix();
            $url_prefix = $this->o_urls->getDbPrefix();
            $select_fields = $this->buildSqlSelectFields($a_page_fields, 'p');
            $select_fields .= ', ' . $this->buildSqlSelectFields($a_urls_fields, 'u');

            $this->select_sql = <<<EOT
SELECT {$select_fields}
FROM {$page_prefix}page as p,
     {$url_prefix}urls as u
EOT;
        }
    }
}

