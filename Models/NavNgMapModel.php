<?php
/**
 * Class NavNgMapModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Does all the database CRUD stuff for the navigation to navgroups mapping.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.4
 * @date    2018-06-07 16:34:47
 * @change_log
 * - v1.0.0-alpha.4 - Refactored to use ModelAbstract              - 2018-06-07 wer
 * - v1.0.0-alpha.3 - Refactored to use ModelException             - 2017-06-15 wer
 * - v1.0.0-alpha.2 - DbUtilityTraits change reflected here        - 2017-05-09 wer
 * - v1.0.0-alpha.1 - Refactoring reflected here                   - 2017-01-27 wer
 * - v1.0.0-alpha.0 - Initial version                              - 02/25/2016 wer
 */
class NavNgMapModel extends ModelAbstract
{
    /**
     * NavNgMapModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'nav_ng_map');
        $this->setRequiredKeys(['ng_id', 'nav_id']);
    }

    /**
     * "Updates" a record using the values provided.
     * Overrides abstract. Doesn't actually update a record.
     * The two fields in the table create a single primary key so can not be changed.
     * To change, a DELETE/INSERT thing has to be done. This method should be wrapped
     * in a transaction. It doesn't itself so that it can be used in a larger transaction.
     *
     * @param array  $a_new_values Required, not like abstract. ['nav_id', 'ng_id']
     * @param array  $a_old_values Required, not like abstract. ['nnm_id', 'nav_id', 'ng_id']
     * @return bool
     * @throws ModelException
     */
    public function update(array $a_new_values = [], array $a_old_values = []):bool
    {
        if ($a_new_values['nav_id'] === $a_old_values['nav_id'] &&
            $a_new_values['ng_id']  === $a_old_values['ng_id']
        ) {
            return true;
        }
        if (
            empty($a_new_values['nav_id']) ||
            empty($a_new_values['ng_id']) ||
            empty($a_old_values['nav_id']) ||
            empty($a_old_values['ng_id'])
        ) {
            $err_msg = 'Missing required values.';
            $err_code = ExceptionHelper::getCodeNumberModel('update missing values');
            throw new ModelException($err_msg, $err_code);
        }

        try {
            if (empty($a_old_values['nnm_id'])) {
                $a_old_record = $this->read($a_old_values);
                $nnm_id = $a_old_record[0][$this->primary_index_name];
            }
            else {
                $nnm_id = $a_old_values['nnm_id'];
            }
            $this->delete($nnm_id);
            $this->create($a_new_values);
            return true;
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes a record with either/both id(s) provided.
     *
     * @param int $ng_id  semi-optional, either/both ng_id and/or nav_id must be set
     * @param int $nav_id semi-optional, either/both ng_id and/or nav_id must be set
     * @note The obvious needs to be noted. If only one param is provided, all the records
     *       for that id will be deleted. This may be an unwanted consequence.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function deleteWith($ng_id = -1, $nav_id = -1):bool
    {
        if ($ng_id === -1 && $nav_id === -1) {
            return false;
        }

        if ($ng_id === -1) {
            $where = 'nav_id = :nav_id';
            $a_values = ['nav_id' => $nav_id];
        }
        elseif ($nav_id === -1) {
            $where = 'ng_id = :ng_id';
            $a_values = ['ng_id' => $ng_id];
        }
        else {
            $where = 'ng_id = :ng_id AND nav_id = :nav_id';
            $a_values = ['ng_id' => $ng_id, 'nav_id' => $nav_id];
        }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE {$where}";
        try {
            return $this->o_db->delete($sql, $a_values, true);
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to delete the record: ' . $this->o_db->getSqlErrorMessage();
            throw new ModelException($this->error_message, 410, $e);
        }
    }

    /**
     * Checks to see if either/both navigation record and navgroups record exists.
     * Note that this does make a weird assumption: it must be able to verify records do not exist.
     * If it errors on the read it returns true, as if it found something.
     *
     * @param array $a_values
     * @return bool
     */
    public function relatedRecordsExist(array $a_values = []):bool
    {
        if (empty($a_values['nav_id']) && empty($a_values['ng_id'])) {
            return true;
        }
        if (!empty($a_values['nav_id'])) {
            $o_nav = new NavigationModel($this->o_db);
            try {
                $results = $o_nav->read(['nav_id' => $a_values['nav_id']]);
                if (!empty($results)) {
                    return true;
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'The Navigation record does not exist.';
                return true;
            }
        }
        if (!empty($a_values['ng_id'])) {
            $o_ng  = new NavgroupsModel($this->o_db);
            try {
                $results = $o_ng->read(['ng_id' => $a_values['ng_id']]);
                if (!empty($results)) {
                    return true;
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'The Navgroup record does not exist.';
                return true;
            }
        }
        return false;
    }
}
