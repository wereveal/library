<?php
/**
 * Class PageModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\DatesTimes;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Does all the database CRUD stuff for the page table plus other app/business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.0.0
 * @date    2018-06-15 09:16:14
 * @change_log
 * - v3.0.0   - Refactored to use ModelAbstract                         - 2018-06-15 wer
 * - v2.0.0   - Refactored to use ModelException                        - 2017-06-17 wer
 * - v1.2.0   - refactored to utilize the DbUtilityTraits               - 2016-04-01 wer
 * - v1.1.0   - refactoring changes to DbModel reflected here.          - 2016-03-19 wer
 * - v1.0.0   - take out of beta                                        - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                                         - 10/30/2015 wer
 */
class PageModel extends ModelAbstract
{
    /**
     * PageModel constructor.
     *
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'page');
        $this->setRequiredKeys(['url_id', 'page_title']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    public function read(array $a_search_for = [], array $a_search_params = []):array
    {
        $a_fields      = $a_search_params['a_fields']      ?? $this->a_db_fields;
        $return_format = $a_search_params['return_format'] ?? 'assoc';
        $order_by      = 'page_title ASC';
        $page_up       = date('Y-m-d H:i:s');
        $page_down     = $page_up;
        $starting_from = '';
        $limit_to      = '';
        if (!empty($a_search_for['page_up'])) {
            $min_time_allowed = strtotime('1000-01-01 00:00:00');
            if ($pub_up = strtotime($a_search_for['page_up'])) {
                if ($pub_up >= $min_time_allowed) {
                    $page_up = DatesTimes::convertDateTimeWith('Y-m-d H:i:s', $a_search_for['page_up']) ;
                }
            }
            unset($a_search_for['page_up']);
        }
        if (!empty($a_search_for['page_down'])) {
            $max_time_allowed = strtotime('9999-12-31 23:59:59');
            if ($pub_down = strtotime($a_search_for['page_down'])) {
                if ($pub_down <= $max_time_allowed) {
                    $page_down = DatesTimes::convertDateTimeWith('Y-m-d H:i:s', $a_search_for['page_down']);
                }
            }
            unset($a_search_for['page_down']);
        }
        if (!empty($a_search_params['order_by'])) {
            $order_by = $a_search_params['order_by'];
            unset($a_search_params['order_by']);
        }
        if (!empty($a_search_params['limit_to'])) {
            $limit_to = $a_search_params['limit_to'];
            unset($a_search_params['limit_to']);
        }
        if (!empty($a_search_params['starting_from'])) {
            $starting_from = $a_search_params['starting_from'];
            if ($starting_from > 0) {
                --$starting_from; // limit offset starts at 0 so if we want to start at record 6 the LIMIT offset is 5
            }
            unset($a_search_params['starting_from']);
        }
        $where  = $this->buildSqlWhere($a_search_for, $a_search_params);
        $where .= ' 
            AND page_up <= :page_up 
            AND page_down >= :page_down 
            ORDER BY ' . $order_by;
        if ($limit_to !== '') {
            if ($starting_from !== '') {
                $where .= " LIMIT {$starting_from}, {$limit_to}";
            }
            else {
                $where .= " LIMIT {$limit_to}";
            }
        }
        $a_search_for['page_up']   = $page_up;
        $a_search_for['page_down'] = $page_down;
        $distinct = isset($a_search_params['select_distinct'])
                    && $a_search_params['select_distinct'] === true
            ? 'DISTINCT '
            : '';
        $select_me = $this->buildSqlSelectFields($a_fields);
        $sql = "
            SELECT {$distinct}{$select_me}
            FROM $this->db_table
            {$where}
        ";
        try {
            return $this->o_db->search($sql, $a_search_for, $return_format);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Reads the page record that corresponds to the url id.
     *
     * @param  int $url_id Required
     * @return array
     * @throws ModelException
     */
    public function readByUrlId($url_id = -1):array
    {
        if ($url_id < 1) {
            $err_num = ExceptionHelper::getCodeNumberModel('read missing value');
            throw new ModelException('Missing url id', $err_num);
        }
        try {
            return $this->read(['url_id' => $url_id]);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
