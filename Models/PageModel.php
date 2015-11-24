<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file PageModel.php
 *  @ingroup ritc_library models
 *  @namespace Ritc/Library/Models
 *  @class PageModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-10-30 09:01:06
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version                              - 10/30/2015 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class PageModel implements ModelInterface
{
    use LogitTraits;

    private $db_prefix;
    private $db_type;
    private $error_message;
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
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
            'page_url'
        ];
        if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
            $this->error_message = 'Did not have a page url.';
            return false;
        }
        $a_needed_keys = [
            'page_url',
            'page_type',
            'page_title',
            'page_description',
            'page_base_url',
            'page_lang',
            'page_charset',
            'page_immutable'
        ];
        $a_values = Arrays::createRequiredPairs($a_values, $a_needed_keys, true);
        if ($a_values['page_immutable'] == 'true') {
            $a_values['page_immutable'] = 1;
        }
        else {
            $a_values['page_immutable'] = 0;
        }
        $a_results = $this->read(['page_url' => $a_values['page_url']]);
        $this->logIt('page results: ' . var_export($a_results, TRUE), LOG_OFF, $meth . __LINE__);
        if (isset($a_results[0])) {
            $a_existing_url = $a_results[0];
            if ($a_values['page_url'] == $a_existing_url['page_url']) {
                $this->error_message = 'The URL for the page already exists.';
                return false;
            }
        }
        $sql = "
            INSERT INTO {$this->db_prefix}page (
                page_url,
                page_type,
                page_title,
                page_description,
                page_base_url,
                page_lang,
                page_charset,
                page_immutable
            ) VALUES (
                :page_url,
                :page_type,
                :page_title,
                :page_description,
                :page_base_url,
                :page_lang,
                :page_charset,
                :page_immutable
            )
        ";
        $a_table_info = [
            'table_name'  => "{$this->db_prefix}page",
            'column_name' => 'page_id'
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
            $this->error_message = 'The page could not be saved.';
            return false;
        }
    }
    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values optional, defaults to returning all records
     * @param array $a_search_params optional, defaults to ['order_by' => 'route_path']
     * @return array
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? ['order_by' => 'page_url']
                : $a_search_params;
            $a_allowed_keys = [
                'page_id',
                'page_url',
                'page_type',
                'page_base_url',
                'page_lang',
                'page_charset',
                'page_immutable'
            ];
            $a_search_values = Arrays::removeUndesiredPairs($a_search_values, $a_allowed_keys);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY page_path";
        }
        $sql = "
            SELECT
                page_id,
                page_url,
                page_type,
                page_title,
                page_description,
                page_base_url,
                page_lang,
                page_charset,
                page_immutable
            FROM {$this->db_prefix}page
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
        if (!isset($a_values['page_id'])
            || $a_values['page_id'] == ''
            || (is_string($a_values['page_id']) && !is_numeric($a_values['page_id']))
        ) {
            $this->error_message = 'The Page Id was not supplied.';
            return false;
        }
        $a_allowed_keys = [
            'page_id',
            'page_url',
            'page_type',
            'page_title',
            'page_description',
            'page_base_url',
            'page_lang',
            'page_charset',
            'page_immutable'
        ];
        $a_values = Arrays::removeUndesiredPairs($a_values, $a_allowed_keys);
        if (isset($a_values['page_immutable']) && $a_values['page_immutable'] == 'true') {
            $a_values['page_immutable'] = 1;
        }
        else {
            $a_values['page_immutable'] = 0;
        }
        if ($a_values['page_immutable'] == 0) {
            if (isset($a_values['page_url'])) {
                $a_results = $this->read(['page_id' => $a_values['page_id']]);
                if (isset($a_results[0])) {
                    $a_old_values = $a_results[0];
                }
                else {
                    $this->error_message = 'The page appears not to be available to update.';
                    return false;
                }
                $a_results = $this->read(['page_url' => $a_values['page_url']]);
                if (isset($a_results[0])) {
                    $a_existing_url = $a_results[0];
                    if ($a_old_values['page_id'] != $a_existing_url['page_id']) {
                        $this->error_message = 'The Page URL already exists.';
                        return false;
                    }
                }
            }
        }
        $set_sql = $this->o_db->buildSqlSet($a_values, ['page_id']);
        $sql = "
            UPDATE {$this->db_prefix}page
            {$set_sql}
            WHERE page_id = :page_id
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
     * @param int $page_id
     * @return array
     */
    public function delete($page_id = -1)
    {
        if ($page_id == -1) { return false; }
        $search_sql = "SELECT page_immutable FROM {$this->db_prefix}page WHERE page_id = :page_id";
        $search_results = $this->o_db->search($search_sql, array(':page_id' => $page_id));
        if ($search_results[0]['page_immutable'] == 1) {
            return ['message' => 'Sorry, that page can not be deleted.', 'type' => 'failure'];
        }
        $sql = "
            DELETE FROM {$this->db_prefix}page
            WHERE page_id = :page_id
        ";
        $results = $this->o_db->delete($sql, array(':page_id' => $page_id), true);
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
     * Implements the ModelInterface method, getErrorMessage.
     * return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }
}
