<?php
/**
 *  @brief     Does all the database CRUD stuff for the PeopleGroupMap table.
 *  @ingroup   ritc_library lib_models
 *  @file      Ritc/Library/Models/PeopleGroupMapModel.php
 *  @namespace Ritc\Library\Models
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0
 *  @date      2015-11-27 14:57:55
 *  @note <pre><b>Change Log</b>
 *      v1.0.0   - take out of beta                                      - 11/27/2015 wer
 *      v1.0.0β7 - refactoring fix for postgres compatibility            - 11/22/2015 wer
 *      v1.0.0β6 - removed abstract Base, implemented LogitTraits        - 09/03/2015 wer
 *      v1.0.0β5 - refactoring elsewhere caused changes here             - 07/31/2015 wer
 *      v1.0.0β4 - refactored user to people                             - 01/26/2015 wer
 *      v1.0.0β3 - extends the Base class, injects the DbModel, clean up - 09/23/2014 wer
 *      v1.0.0β2 - First Live version                                    - 09/15/2014 wer
 *      v1.0.0β1 - Initial version                                       - 01/18/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class PeopleGroupMapModel.
 * @class   PeopleGroupMapModel
 * @package Ritc\Library\Models
 */
class PeopleGroupMapModel implements ModelInterface
{
    use LogitTraits;

    /** @var string  */
    private $db_prefix;
    /** @var string  */
    private $db_type;
    /** @var \Ritc\Library\Services\DbModel  */
    private $o_db;

    /**
     * PeopleGroupMapModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $o_db->getDbType();
        $this->db_prefix = $o_db->getDbPrefix();
    }

    ### Basic CRUD commands, required by interface ###
    /**
     *  Creates a new user group map record in the people_group_map table.
     *  @param array $a_values required can be simple assoc array
     *                         ['people_id' => 1, 'group_id' => 1] or
     *                         array of assoc arrays
     *                         [['people_id' => 1, 'group_id' => 1], ['people_id' => 2, 'group_id' => 1]]
     *  @return array|bool
    **/
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'people_id',
            'group_id'
        );
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_pgm) {
                if (!Arrays::hasRequiredKeys($a_pgm, $a_required_keys)) {
                    return false;
                }
                else {
                    $a_values[$key] = Arrays::removeUndesiredPairs($a_pgm, $a_required_keys);
                }
            }
        }
        else {
            if (!Arrays::hasRequiredKeys($a_values, $a_required_keys)) {
                return false;
            }
            else {
                $a_values = Arrays::removeUndesiredPairs($a_values, $a_required_keys);
            }
        }
        $sql = "
            INSERT INTO {$this->db_prefix}people_group_map (people_id, group_id)
            VALUES (:people_id, :group_id)
        ";
        $a_table_info = [
            'table_name'  => "{$this->db_prefix}people_group_map",
            'column_name' => 'pgm_id'
        ];
        if ($this->o_db->insert($sql, $a_values, $a_table_info)) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids;
        }
        else {
            return false;
        }
    }
    /**
     * Returns record(s) from the library_people_group_map table
     * @param array $a_search_values
     * @return mixed
     */
    public function read(array $a_search_values = array())
    {
        $where = '';
        if ($a_search_values != array()) {
            $a_search_params = array('order_by' => 'people_id');
            $a_allowed_keys = array(
                'group_id',
                'people_id',
                'pgm_id'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        $sql = "
            SELECT *
            FROM {$this->db_prefix}people_group_map
            {$where}
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     *  Updates the record, NOT!
     *  Method is required by interface.
     *  Update is not allowed! Always return false.
     *      Reasoning. The group_id and people_id form a unique index. As such,
     *      they should not be modified. The record should always be deleted and
     *      a new one added.
     *  @param array $a_values
     *  @return bool
     */
    public function update(array $a_values = array())
    {
        return false;
    }
    /**
     * Deletes a single record.
     * @param int $pgm_id required
     * @return bool
     */
    public function delete($pgm_id = -1)
    {
        if ($pgm_id == -1) { return false; }
        if (!ctype_digit($pgm_id)) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}people_group_map
            WHERE pgm_id = :pgm_id
        ";
        return $this->o_db->delete($sql, array(':pgm_id' => $pgm_id), true);
    }
    /**
     * Deletes the record(s) in table based on group id(s).
     * @param int|array $group_id either '1' or ['1', '2', '3']
     * @return bool
     */
    public function deleteByGroupId($group_id = -1)
    {
        $sql = "
            DELETE FROM {$this->db_prefix}people_group_map
            WHERE group_id = :group_id
        ";
        $a_values = array();
        if (is_array($group_id)) {
            foreach ($group_id as $id) {
                $a_values[] = [':group_id' => $id];
            }
        }
        else {
            $a_values = [':group_id' => $group_id];
        }
        return $this->o_db->delete($sql, $a_values, true);
    }
    /**
     * Deletes the records based on people id.
     * @param int $people_id
     * @return bool
     */
    public function deleteByPeopleId($people_id = -1)
    {
        $sql = "
            DELETE FROM {$this->db_prefix}people_group_map
            WHERE people_id = :people_id
        ";
        return $this->o_db->delete($sql, array(':people_id' => $people_id), true);
    }

    ### Required by Interface ###
    /**
     * Returns error message.
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->o_db->getSqlErrorMessage();
    }
}
