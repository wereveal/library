<?php
/**
 * Class PageComplexModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
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
    /** @var \Ritc\Library\Models\PageModel  */
    private $o_page;
    /** @var PageBlocksMapModel  */
    private $o_pbm;
    /** @var \Ritc\Library\Models\TwigComplexModel  */
    private $o_tpls;
    /** @var \Ritc\Library\Models\UrlsModel  */
    private $o_urls;
    /** @var string  */
    private $select_sql = '';

    /**
     * PageComplexModel constructor.
     *
     * @param \Ritc\Library\Services\Di $o_di
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function __construct(Di $o_di)
    {
        $this->a_object_names = ['o_page', 'o_urls', 'o_pbm'];
        /** @var DbModel $o_db */
        $o_db         = $o_di->get('db');
        $this->o_db   = $o_db;
        $this->o_page = new PageModel($o_db);
        $this->o_urls = new UrlsModel($o_db);
        $this->o_pbm  = new PageBlocksMapModel($o_db);
        try {
            $this->o_content = new ContentComplexModel($o_di);
        }
        catch (ModelException $e) {
            $message    = 'Unable to create instanceof ContentComplexModel.';
            $error_code = ExceptionHelper::getCodeNumber('instance');
            throw new ModelException($message, $error_code, $e);
        }
        $this->o_tpls = new TwigComplexModel($o_di);
        $this->setupElog($o_di);
        $this->setSelectSql();
    }

    /**
     * Deletes all records associated with the page id passed in.
     * This includes the content records, the pbm records, and finally page record.
     *
     * @param int $page_id
     * @return bool
     * @throws ModelException
     */
    public function deletePageValues($page_id = -1):bool
    {
        if ($page_id === -1) {
            $message = 'Missing Required Values';
            $err_code = ExceptionHelper::getCodeNumberModel('missing_values');
            throw new ModelException($message, $err_code);
        }
        try {
            $this->o_db->startTransaction();
            $this->o_content->deleteAllByPage($page_id);
            $this->o_pbm->deleteAllByPageId($page_id);
            $this->o_page->delete($page_id);
            $this->o_db->commitTransaction();
        }
        catch (ModelException $e) {
            $this->o_db->rollbackTransaction();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * @param array $a_search_for        Optional, defaults to returning all records
     *                                   Allowed search keys ['url_id', 'url_text', 'page_id', 'page_title']
     * @param array $a_search_parameters Optional, defaults to ['order_by' => 'page_id ASC', 'search_type' => 'AND']
     *                                   Note that other values are ignored.
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readPageValues(array $a_search_for = [], array $a_search_parameters = [])
    {
        $a_allowed_keys = ['url_id', 'url_text', 'page_id', 'page_title'];
        $order_by = 'p.page_id ASC';
        $search_type = 'AND';
        if (isset($a_search_parameters['order_by'])) {
            $order_by = $a_search_parameters['order_by'];
        }
        if (isset($a_search_parameters['search_type'])) {
            $search_type = $a_search_parameters['search_type'];
        }
        $sql_where = '';
        foreach ($a_search_for as $key => $value) {
            if (\in_array($key, $a_allowed_keys)) {
                $search_for = $key[0] . '.' . $key;
                $placeholder = ':' . $key;
                $sql_where .= $sql_where === ''
                    ? " WHERE {$search_for} = {$placeholder}"
                    : " {$search_type} {$search_for} = {$placeholder}";
            }
        }
        $sql = trim($this->select_sql . $sql_where . ' ORDER BY ' . $order_by);
        try {
            return $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to read the page values.', 140, $e);
        }
    }

    /**
     * Returns records from the join of the page and urls tables based on url id.
     *
     * @param int $url_id Required
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readPageValuesByUrlId($url_id = -1)
    {
        if ($url_id === -1) {
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

            $a_record = $a_values[0];
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
            $a_content_results = $this->o_content->readCurrent($a_record['page_id']);
            $log_message = 'Content ' . var_export($a_content_results, true);
            $this->logIt($log_message, LOG_OFF, __METHOD__);

            foreach ($a_content_results as $a_row) {
                $a_content[$a_row['b_name']] = $a_row;
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
     *
     * @param string $url_text Required
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readPageValuesByUrlText($url_text = '')
    {
        if ($url_text === '') {
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
     * Save the page values as well as the page to block records.
     * This is for a new page. \see self::updatePageValues() for
     * updating existing records.
     *
     * @param array $a_values Required, needs both $a_values['a_blocks'] and $a_values['a_page']
     * @return bool
     * @throws ModelException
     */
    public function savePageValues(array $a_values = []):bool
    {
        /*
        save page getting new id
        save pbm records specifying blocks assigned to page
        */
        $a_blocks = $a_values['a_blocks'];
        $a_page   = $a_values['a_page'];
        if (empty($a_page) || empty($a_blocks)) {
            $message = 'Missing Required Values';
            $err_code = ExceptionHelper::getCodeNumberModel('missing_values');
            throw new ModelException($message, $err_code);
        }
        $this->o_db->startTransaction();
        try {
            $a_ids  = $this->o_page->create($a_page);
            $new_id = $a_ids[0];
        }
        catch (ModelException $e) {
            $this->o_db->rollbackTransaction();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        try {
            $this->o_pbm->createByPageId($new_id, $a_blocks);
        }
        catch (ModelException $e) {
            $this->o_db->rollbackTransaction();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        $this->o_db->commitTransaction();
        return true;
    }

    /**
     * Updates the page record and resets the pbm records with new values.
     *
     * @param array $a_values
     * @return bool
     * @throws ModelException
     */
    public function updatePageValues(array $a_values = []):bool
    {
        $a_page   = $a_values['a_page'];
        $a_blocks = $a_values['a_blocks'];
        if (empty($a_page) || empty($a_blocks)) {
            $message = 'Missing Required Values';
            $err_code = ExceptionHelper::getCodeNumberModel('missing_values');
            throw new ModelException($message, $err_code);
        }
        $this->o_db->startTransaction();
        try {
            $this->o_page->update($a_page);
        }
        catch (ModelException $e) {
            $this->o_db->rollbackTransaction();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        try {
            $this->o_pbm->deleteAllByPageId($a_page['page_id']);
            $this->o_pbm->createByPageId($a_page['page_id'], $a_blocks);
        }
        catch (ModelException $e) {
            $this->o_db->rollbackTransaction();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        $this->o_db->commitTransaction();
        return true;
    }

    ### Utilities ###
    /**
     * Sets the class property select_sql.
     *
     * @param string $the_string Optional, normally not specified.
     */
    private function setSelectSql($the_string = ''):void
    {
        if ($the_string !== '') {
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
                  ON p.url_id = u.url_id ";
        }
    }
}

