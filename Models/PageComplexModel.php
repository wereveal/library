<?php
/**
 * Class PageComplexModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does multi-table queries and applies necessary business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.6
 * @date    2017-06-17 12:12:02
 * @change_log
 * - v1.0.0-alpha.7 - bug fix                           - 2018-04-17 wer
 * - v1.0.0-alpha.6 - variable name clarification       - 2018-04-12 wer
 * - v1.0.0-alpha.5 - refactored to use ModelException  - 2017-06-17 wer
 * - v1.0.0-alpha.2 - refactoring reflected here        - 2017-01-27 wer
 * - v1.0.0-alpha.0 - Initial version                   - 2016-04-08 wer
 * @todo take out of alpha
 */
class PageComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /** @var \Ritc\Library\Models\ContentModel  */
    private $o_content;
    /** @var \Ritc\Library\Services\Di */
    private $o_di;
    /** @var \Ritc\Library\Models\PageModel  */
    private $o_page;
    /** @var \Ritc\Library\Models\TwigComplexModel  */
    private $o_tpls;
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
        $this->a_object_names = ['o_page', 'o_urls', 'o_content'];
        /** @var DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->o_db = $o_db;
        $this->o_page = new PageModel($o_db);
        $this->o_urls = new UrlsModel($o_db);
        $this->o_content = new ContentModel($o_db);
        $this->o_tpls = new TwigComplexModel($o_di);
        $this->setupElog($o_di);
        $this->setSelectSql();
    }

    /**
     * @param array $a_search_for        Optional, defaults to returning all records
     *                                   Allowed search keys ['url_id', 'url_text', 'page_id', 'page_title']
     * @param array $a_search_parameters Optional, defaults to ['order_by' => 'page_id ASC']
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readPageValues(array $a_search_for = [], array $a_search_parameters = [])
    {
        $a_allowed_keys = ['u.url_id', 'u.url_text', 'p.page_id', 'p.page_title'];
        if (!isset($a_search_parameters['order_by'])) {
            $a_search_parameters['order_by'] = 'page_id ASC';
        }
        if (!isset($a_search_parameters['where_exists'])) {
            $a_search_parameters['where_exists'] = true;
        }
        $sql_where = $this->buildSqlWhere($a_search_for, $a_search_parameters, $a_allowed_keys);
        $sql = $this->select_sql . "\n" . $sql_where;
        try {
            return $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to read the page values.', 140, $e);
        }
    }

    /**
     * Returns records from the join of the page and urls tables based on url id.
     * @param int $url_id Required
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readPageValuesByUrlId($url_id = -1)
    {
        if ($url_id == -1) {
            return false;
        }
        $this->setSelectSql();
        $sql = "
            {$this->select_sql}
            WHERE p.url_id = :url_id";
        try {
            $a_values = $this->o_db->search($sql, [':url_id' => $url_id]);
            if (empty($a_values[0])) {
                throw new ModelException('Unable to get the page values', 140);
            }
            else {
                $a_record = $a_values[0];
            }
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to get the page values', 140, $e);
        }
        $tpl_id = $a_record['tpl_id'];
        try {
            $a_twig_stuff = $this->o_tpls->readTplInfo($tpl_id);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to get the template values', 140, $e);
        }
        if (!empty($a_twig_stuff)) {
            $a_record = array_merge($a_record, $a_twig_stuff);
        }
        try {
            $a_content = [];
            $a_content_results = $this->o_content->readAllByPage($a_record['page_id']);
            foreach ($a_content_results as $a_block) {
                $a_content[$a_block['c_block']] = $a_block;
            }
            $a_record['a_content'] = $a_content;
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to get the content values', 140, $e);
        }
        return $a_record;
    }

    /**
     * Returns one or more records based on the url ordered by the url text.
     * @param string $url_text Required
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readPageValuesByUrlText($url_text = '')
    {
        if ($url_text == '') {
            return false;
        }
        $url_text = str_replace(SITE_URL, '', $url_text);
        $sql = $this->select_sql . '
            WHERE u.url_text = :url_text
            AND p.url_id = u.url_id
            ORDER By u.url_text';
        try {
            return $this->o_db->search($sql, [':url_text' => $url_text]);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to read the page values.', 140, $e);
        }
    }

    /**
     * Sets the class property select_sql.
     * @param string $the_string Optional, normally not specified.
     */
    private function setSelectSql($the_string = '')
    {
        if ($the_string != '') {
            $this->select_sql = $the_string;
        }
        else {
            $a_page_fields  = $this->o_page->getDbFields();
            $a_urls_fields  = $this->o_urls->getDbFields();
            $select_fields  = $this->buildSqlSelectFields($a_page_fields, 'p');
            $select_fields .= ', ' . $this->buildSqlSelectFields($a_urls_fields, 'u');
            $this->select_sql = "
                SELECT {$select_fields}
                FROM {$this->lib_prefix}page as p
                JOIN {$this->lib_prefix}urls as u
                  ON p.url_id = u.url_id
            ";
        }
    }
}

