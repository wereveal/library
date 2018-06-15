<?php
/**
 * Class NavNgMapModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
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
     * Generic update for a record using the values provided.
     * Overrides abstract. Required by interface but not used in this instance.
     * The two fields in the table create a single primary key so can not be changed.
     * To change, an INSERT/DELETE thing has to be done.
     *
     * @param array  $a_values
     * @param string $immutable
     * @param array  $a_not_used
     * @return void
     * @throws ModelException
     * @todo change so that it does the delete/insert thing... maybe.
     */
    public function update(array $a_values = [], array $a_not_used = [])
    {
        throw new ModelException('Update not allowed.', 350);
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
    public function deleteWith($ng_id = -1, $nav_id = -1)
    {
        if ($ng_id == -1 && $nav_id == -1) {
            return false;
        }
        elseif ($ng_id == -1) {
            $where = 'nav_id = :nav_id';
            $a_values = ['nav_id' => $nav_id];
        }
        elseif ($nav_id == -1) {
            $where = 'ng_id = :ng_id';
            $a_values = ['ng_id' => $ng_id];
        }
        else {
            $where = "ng_id = :ng_id AND nav_id = :nav_id";
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
     * @param array $a_values
     * @return bool
     */
    public function relatedRecordsExist(array $a_values = [])
    {
        if (empty($a_values['nav_id']) && empty($a_values['ng_id'])) {
            return true;
        }
        if (!empty($a_values['nav_id'])) {
            $o_nav = new NavigationModel($this->o_db);
            try {
                $results = $o_nav->read(['nav_id' => $a_values['nav_id']]);
                if (count($results) > 0) {
                    return true;
                }
            }
            catch (ModelException $e) {
                $this->error_message = "The Navigation record does not exist.";
                return true;
            }
        }
        if (!empty($a_values['ng_id'])) {
            $o_ng  = new NavgroupsModel($this->o_db);
            try {
                $results = $o_ng->read(['ng_id' => $a_values['ng_id']]);
                if (count($results) > 0) {
                    $this->error_message = "The Navgroup record does not exist.";
                    return true;
                }
            }
            catch (ModelException $e) {
                $this->error_message = "The Navgroup record does not exist.";
                return true;
            }
        }
        return false;
    }
}
