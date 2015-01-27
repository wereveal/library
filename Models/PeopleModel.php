<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file PeopleModel.php
 *  @ingroup ritc_library models
 *  @namespace Ritc/Library/Models
 *  @class PeopleModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.4ß
 *  @date 2015-01-06 10:40:12
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.4ß - refactoring method name to reflect what is happening better   - 01/06/2015 wer
 *      v1.0.3ß - reverted to injecting DbModel                                 - 11/17/2014 wer
 *      v1.0.2ß - changed to use DI/IOC                                         - 11/15/2014 wer
 *      v1.0.1ß - extends the Base class, injects the DbModel, clean up         - 09/23/2014 wer
 *      v1.0.0ß - First Live version                                            - 09/15/2014 wer
 *      v0.1.0ß - Initial version                                               - 09/11/2014 wer
 *  </pre>
 *  @todo add the methods needed to crud a user with all the correct group and role information
**/
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;

class PeopleModel extends Base implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_db;

    public function __construct(DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->o_db      = $o_db;
        $this->db_type   = $o_db->getDbType();
        $this->db_prefix = $o_db->getDbPrefix();
    }

    ### Basic CRUD commands, required by interface, deals only with the {$this->db_prefix}user table ###
    /**
     *  Creates a new user record in the user table.
     *  @param array $a_values required array('login_id', 'real_name', 'short_name', 'password'), optional key=>values 'is_active' and 'is_default'
     *  @return int|bool
    **/
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'login_id',
            'real_name',
            'short_name',
            'password'
        );
        if (!Arrays::hasRequiredKeys($a_required_keys, $a_values)) {
            return false;
        }
        if ((isset($a_values['is_active']) && $a_values['is_active'] == '') || !isset($a_values['is_active'])) {
            $a_values['is_active'] = 1;
        }
        if ((isset($a_values['is_default']) && $a_values['is_default'] == '') || !isset($a_values['is_default'])) {
            $a_values['is_default'] = 0;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}people
                (login_id, real_name, short_name, password, is_active, is_default)
            VALUES
                (:login_id, :real_name, :short_name, :password, :is_active, :is_default)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}people")) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        }
        else {
            return false;
        }
    }
    /**
     * Returns the record for
     * @param array $a_search_values
     * @param array $a_search_params
     * @return array|bool
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = count($a_search_params) == 0
                ? array('order_by' => 'login_id')
                : $a_search_params;
            $a_allowed_keys = array(
                'people_id',
                'login_id',
                'real_name',
                'short_name',
                'is_default',
                'is_active'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY 'login_id'";
        }
        $sql = "
            SELECT people_id,
                login_id,
                real_name,
                short_name,
                password,
                is_logged_in,
                is_active,
                is_default,
                created_on,
                bad_login_count,
                bad_login_ts
            FROM {$this->db_prefix}people
            {$where}
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     * Updates a {$this->db_prefix}people record.
     * @param array $a_values required $a_values['people_id'] || $a_values['login_id']
     * @return bool
     */
    public function update(array $a_values = array())
    {
        $people_id = '';
        $login_id = '';
        if (isset($a_values['people_id'])) {
            if ($a_values['people_id'] != '') {
                $people_id = $a_values['people_id'];
            }
            unset($a_values['people_id']);
        }
        if (isset($a_values['login_id'])) {
            if ($a_values['login_id'] != '') {
                $login_id = $a_values['login_id'];
            }
            unset($a_values['login_id']);
        }
        if ($people_id == '' && $login_id == '') { return false; }
        /* the following keys in $a_values must have a value other than ''.
         * As such, they get removed from the sql
         * if they are trying to update the values to ''
         */
        $a_possible_keys = array(
            'real_name',
            'short_name',
            'password',
            'is_logged_in',
            'is_active',
            'is_default'
        );
        foreach ($a_possible_keys as $key_name) {
            if (array_key_exists($key_name, $a_values)) {
                if ($a_values[$key_name] == '') {
                    unset($a_values[$key_name]);
                }
            }
            else {
                unset($a_values[$key_name]);
            }
        }
        if ($a_values == array()) {
            return false;
        }
        $sql_set = $this->o_db->buildSqlSet($a_values, array('people_id', 'login_id'));
        $sql_where = isset($a_values['login_id'])
            ? 'WHERE login_id = :login_id'
            : isset($a_values['people_id'])
                ? 'WHERE people_id = :people_id'
                : '';
        if ($sql_where == '' || $sql_set == '') { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            {$sql_set}
            {$sql_where}
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Deletes a {$this->db_prefix}people record based on id.
     *  @param int $people_id required
     *  @return bool
    **/
    public function delete($people_id = -1)
    {
        if ($people_id == -1 || !ctype_digit($people_id)) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}people
            WHERE people_id = :people_id
        ";
        return $this->o_db->delete($sql, array(':people_id' => $people_id), true);
    }

    ### Single User Methods ###
    /**
     *  Gets the people_id (primary record key) for a specific login_id.
     *  @param string $login_id required
     *  @return int|bool $people_id
     */
    public function getPeopleId($login_id = '')
    {
        if ($login_id == '') { return false; }
        $a_results = $this->read(array('login_id' => $login_id));
        if ($a_results !== false) {
            if (isset($a_results[0]) && $a_results[0] != array()) {
                return $a_results[0]['people_id'];
            }
        }
        return false;
    }
    /**
     *  Updates the bad_login_count field for the user by one
     *  @param int $people_id
     *  @return bool
     **/
    public function incrementBadLoginCount($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_count = bad_login_count + 1
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Increments the bad_login_ts record by one minute
     *  @param int $people_id required
     *  @return bool
     */
    public function incrementBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_ts = bad_login_ts + 60
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Returns the user record.
     *  @param int|string $user either user id or user name
     *  @return array
     */
    public function readPeopleRecord($user = '')
    {
        if ($user == '') { return array(); }
        if (ctype_digit($user)) {
            $a_search_by = ['$people_id' => $user];
        }
        else {
            $a_search_by = ['login_id' => $user];
        }
        $a_records = $this->read($a_search_by);
        if (is_array($a_records[0])) {
            return $a_records[0];
        } else {
            return array();
        }
    }
    /**
     *  Resets the bad_login_count to 0
     *  @param int $people_id required
     *  @return bool
     **/
    public function resetBadLoginCount($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_count = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Resets the timestamp to 0
     *  @param int $people_id required
     *  @return bool
    **/
    public function resetBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $update_sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_ts = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        $results = $this->o_db->update($update_sql, $a_values, true);
        return $results;
    }
    /**
     *  Sets the bad login timestamp for the user.
     *  @param int $people_id required
     *  @return bool
    **/
    public function setBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_ts = :timestamp
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id, ':timestamp' => time());
        $results = $this->o_db->update($sql, $a_values, true);
        return $results;
    }
    /**
     * Sets the user record to be logged in.
     * @param int $people_id
     * @return bool
     */
    public function setLoggedIn($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET is_logged_in = 1
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     * Sets the user record to be logged out.
     * @param int $people_id
     * @return bool
     */
    public function setLoggedOut($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET is_logged_in = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Updates the user record with a new password
     *  @param int    $people_id required
     *  @param string $password required
     *  @return bool success or failure
     */
    public function updatePassword($people_id = -1, $password = '')
    {
        if ($people_id == -1 || $password == '') { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET password = :password
            WHERE id = :people_id
        ";
        $a_values = [':people_id' => $people_id, ':password' => $password];
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Updates the user record to be make the user active or inactive, normally inactive.
     *  @param int $people_id   required id of a user
     *  @param int $is_active optional defaults to inactive (0)
     *  @return bool success or failure
     */
    public function updateActive($people_id = -1, $is_active = 0)
    {
        if ($people_id == -1) { return false; }
        $is_active = (int) $is_active;
        if ($is_active > 1) {
            $is_active = 1;
        }
        if ($is_active == '') {
            $is_active = 0;
        }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET is_active = :is_active
            WHERE people_id = :people_id
        ";
        $a_values = [':people_id' => $people_id, ':is_active' => $is_active];
        return $this->o_db->update($sql, $a_values, true);
    }

    ### More complex methods using multiple tables ###
    /**
     *  Gets the user values based on login_id or people_id.
     *  @param mixed $people_id the user id or login_id (as defined in the db)
     *  @return array, the records for the user
    **/
    public function readInfo($people_id = '')
    {
        if ($people_id == '') { return array(); }
        if (ctype_digit($people_id)) {
            $where = "u.people_id = {$people_id} ";
        }
        else {
            $where = "u.login_id = '{$people_id}' ";
        }
        $sql = "
            SELECT DISTINCT u.people_id, u.login_id, u.real_name, u.short_name, u.password, u.is_logged_in,
                u.bad_login_count, u.bad_login_ts, u.is_active, u.is_default, u.created_on,
                g.group_id, g.group_name, g.group_description,
                r.role_id, r.role_level, r.role_name
            FROM {$this->db_prefix}roles as r,
                 {$this->db_prefix}people as u,
                 {$this->db_prefix}groups as g,
                 {$this->db_prefix}people_group_map as ugm,
                 {$this->db_prefix}group_role_map as grm
            WHERE {$where}
            AND ugm.people_id = u.people_id
            AND g.group_id  = ugm.group_id
            AND r.role_id   = grm.role_id
            ORDER BY r.role_level
        ";
        $this->logIt("Select User: {$sql}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->search($sql);
        if (isset($results[0]) && is_array($results[0])) {
            return $results;
        }
        else {
            return array();
        }
    }
    /**
     *  Saves the user.
     *  If the values contain a value of people_id, user is updated
     *  Else it is a new user.
     *  @param array $a_user values to save
     *  @return mixed, people_id or false
     **/
    public function saveUser(array $a_user = array())
    {
        $method = __METHOD__ . '.';
        if (count($a_user) == 0) {
            return false;
        }
        $this->logIt("a_user before changes: " . var_export($a_user, true), LOG_OFF, $method  . __LINE__);

        if (!isset($a_user['people_id']) || $a_user['people_id'] == '') { // New User
            $o_group = new GroupsModel($this->o_db);
            $o_ugm   = new PeopleGroupMapModel($this->o_db);
            $a_required_keys = array(
                'login_id',
                'real_name',
                'short_name',
                'password'
            );
            $a_user_values = array();
            foreach($a_required_keys as $key_name) {
                $a_user_values[$key_name] = isset($a_user[$key_name]) ? $a_user[$key_name] : '' ;
            }
            $this->logIt("" . var_export($a_user_values , true), LOG_OFF, $method  . __LINE__);
            if ($a_user_values['password'] == '') {
                return false;
            }
            else {
                $a_user_values['password'] = password_hash($a_user_values['password'], PASSWORD_DEFAULT);
            }
            if ($this->o_db->startTransaction()) {
                $new_people_id = $this->create($a_user_values);
                if ($new_people_id !== false) {
                    $a_group_id = array();
                    if (isset($a_user['group_id']) && is_array($a_user['group_id'])) {
                        $a_group_id = $a_user['group_id'];
                    }
                    elseif (isset($a_user['group_id']) && $a_user['group_id'] != '') {
                        $a_group_id = $o_group->isValidGroupId($a_user['group_id'])
                            ? array($a_user['group_id'])
                            : array();
                    }
                    if ($a_group_id == array() && isset($a_user['group_name']) && $a_user['group_name'] != '') {
                        $a_group = $o_group->read(['group_name' => $a_user['group_name']]);
                        if ($a_group !== false) {
                            $a_group_id = array($a_group['group_id']);
                        }
                    }
                    if ($a_group_id == array()) {
                        $a_group_id = array('3');
                    }
                    foreach ($a_group_id as $group_id) {
                        $a_ug_values = array('people_id' => $new_people_id, 'group_id' => $group_id);
                        $ug_results = $o_ugm->create($a_ug_values);
                        if ($ug_results === false) {
                            $this->o_db->rollbackTransaction();
                            return false;
                        }
                    }
                    if ($this->o_db->commitTransaction()) {
                        return $new_people_id;
                    }
                    else {
                        $this->o_db->rollbackTransaction();
                        return false;
                    }
                } // new user created
                else {
                    $this->o_db->rollbackTransaction();
                    return false;
                }
            }
            else {
                return false;
            } // this->o_db->startTransaction
        }
        else { // Existing User
            if (isset($a_user['people_id']) && $a_user['people_id'] != '') {
                $people_id = $a_user['people_id'];
            }
            elseif (isset($a_user['login_id']) && $a_user['login_id'] != '') {
                $people_id = $this->getPeopleId($a_user['login_id']);
            }
            else {
                $people_id = false;
            }
            $results = $this->update($a_user);
            if ($results !== false) {
                return $people_id;
            }
        }
        return false;
    }

}
