<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file GroupsModel.php
 *  @ingroup ritc_library models
 *  @namespace Ritc/Library/Models
 *  @class GroupsModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.2β
 *  @date 2014-11-15 13:10:18
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.2ß - changed to use IOC (Inversion of Control)                              - 11/15/2014 wer
 *      v1.0.1β - extends the Base class, injects the DbModel, clean up                  - 09/23/2014 wer
 *      v1.0.0β - First live version                                                     - 09/15/2014 wer
 *      v0.1.0β - Initial version                                                        - 01/18/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;

class GroupsModel extends Base implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->o_db      = $o_db;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }
    /**
     * Generic create function to create a single record.
     * @param array $a_values required
     * @return bool
     */
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'group_name',
            'group_description'
        );
        if (!Arrays::hasRequiredKeys($a_required_keys, $a_values)) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}groups
                (group_name, group_description)
            VALUES
                (:group_name, :group_description)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}groups")) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        }
        else {
            return false;
        }
    }
    /**
     * @param array $a_search_values
     * @param array $a_search_params
     * @return mixed
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? array('order_by' => 'group_name')
                : $a_search_params;
            $a_allowed_keys = array(
                'group_id',
                'group_name'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY 'group_name'";
        }
        $sql = "
            SELECT group_id, group_name, group_description
            FROM {$this->db_prefix}groups
            {$where}
        ";
        return $this->o_db->search($sql, $a_search_values);
    }
    public function update(array $a_values = array())
    {
        if (   !isset($a_values['group_id'])
            || $a_values['group_id'] == ''
            || !ctype_digit($a_values['group_id'])
        ) {
            return false;
        }
        $a_allowed_keys = ['group_id', 'group_name', 'group_description'];
        $a_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_values);

        $set_sql = $this->o_db->buildSqlSet($a_values, ['group_id']);
        $sql = "
            UPDATE {$this->db_prefix}groups
            {$set_sql}
            WHERE group_id = :group_id
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     * Deletes the specific record.
     * NOTE: this could leave orphaned records in the user_group_map table and group_role_map table
     * if the database isn't set up for relations. If not sure, or want more control, use the
     * deleteWithRelated method.
     * @param int $group_id
     * @return bool
     */
    public function delete($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}groups
            WHERE group_id = :group_id
        ";
        return $this->o_db->delete($sql, array(':group_id' => $group_id), true);
    }
    /**
     * Deletes related records as well as main group record.
     * @param int $group_id
     * @return bool
     */
    public function deleteWithRelated($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        $o_grm = new GroupRoleMapModel($this->o_db);
        $o_ugm = new PeopleGroupMapModel($this->o_db);
        if ($this->o_db->startTransaction()) {
            if ($o_grm->deleteByGroupId($group_id)) {
                if ($o_ugm->deleteByGroupId($group_id)) {
                   if ($this->delete($group_id)) {
                        if ($this->o_db->commitTransaction() === false) {
                            $this->o_db->rollbackTransaction();
                            return false;
                        }
                        else {
                            return true;
                        }
                    }
                }
            }
        }
        else {
            $this->o_db->rollbackTransaction();
            return false;
        }
    }

    ### Shortcuts ###
    /**
     *  Returns a record of the group specified by id.
     *  @param int $group_id
     *  @return array|bool
     */
    public function readById($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        if (!ctype_digit($group_id)) { return false; }
        $results = $this->read(array('group_id' => $group_id));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }
    /**
     *  Returns a record of the group specified by name.
     *  @param string $group_name
     *  @return array()
     */
    public function readyByName($group_name = '')
    {
        if ($group_name == '') { return false; }
        $results = $this->read(array('group_name' => $group_name));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }
    /**
     *  Checks to see if the id is a valid group id.
     *  @param int $group_id
     *  @return bool true or false
     **/
    public function isValidGroupId($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        if (!ctype_digit($group_id)) { return false; }
        if (is_array($this->read(array('group_id' => $group_id)))) {
            return true;
        }
        return false;
    }
}
