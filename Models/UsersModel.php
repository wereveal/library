<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file UsersModel.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Models
 *  @class UsersModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-09-15 14:54:11
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - First Live version - 09/15/2014 wer
 *      v0.1.0 - Initial version    - 09/11/2014 wer
 *  </pre>
 *  @todo add the methods needed to crud a user with all the correct group and role information
**/
namespace Ritc\Library\Models;

use Ritc\Library\Core\Arrays;
use Ritc\Library\Core\DbFactory;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\Elog;
use Ritc\Library\Interfaces\ModelInterface;

class UsersModel implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_arrays;
    private $o_db;
    private $o_elog;

    public function __construct($db_config = 'db_config.php')
    {
        $this->o_elog = Elog::start();
        $o_dbf = DbFactory::start($db_config, 'rw');
        $o_pdo = $o_dbf->connect();
        if ($o_pdo !== false) {
            $this->o_db = new DbModel($o_pdo);
        }
        else {
            $this->o_elog->write('Could not connect to the database', LOG_ALWAYS, __METHOD__ . '.' . __LINE__);
        }
        $this->o_arrays = new Arrays;
        $this->db_type = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }

    ### Basic CRUD commands, required by interface, deals only with the {$this->db_prefix}user table ###
    /**
     *  Creates a new user record in the user table.
     *  @param array $a_values required array('user_name', 'real_name', 'short_name', 'password'), optional key=>values 'is_active' and 'is_default'
     *  @return int|bool
    **/
    public function create(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'user_name',
            'real_name',
            'short_name',
            'password'
        );
        if (!$this->o_arrays->hasRequiredKeys($a_required_keys, $a_values)) {
            return false;
        }
        if ((isset($a_values['is_active']) && $a_values['is_active'] == '') || !isset($a_values['is_active'])) {
            $a_values['is_active'] = 1;
        }
        if ((isset($a_values['is_default']) && $a_values['is_default'] == '') || !isset($a_values['is_default'])) {
            $a_values['is_default'] = 0;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}users
                (user_name, real_name, short_name, password, is_active, is_default)
            VALUES
                (:user_name, :real_name, :short_name, :password, :is_active, :is_default)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}users")) {
            $ids = $this->o_db->getNewIds();
            $this->o_elog->write("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
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
                ? array('order_by' => 'user_name')
                : $a_search_params;
            $a_allowed_keys = array(
                'user_id',
                'user_name',
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
            $where = " ORDER BY 'user_name'";
        }
        $sql = "
            SELECT user_id,
                user_name,
                real_name,
                short_name,
                password,
                is_active,
                is_default,
                created_on,
                bad_login_count,
                bad_login_ts
            FROM {$this->db_prefix}users
            {$where}
        ";
        $this->o_elog->write($sql, LOG_ON, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql, $a_search_values);
    }
    /**
     * Updates a {$this->db_prefix}users record.
     * @param array $a_values required $a_values['user_id'] || $a_values['user_name']
     * @return bool
     */
    public function update(array $a_values = array())
    {
        $user_id = '';
        $user_name = '';
        if (isset($a_values['user_id'])) {
            if ($a_values['user_id'] != '') {
                $user_id = $a_values['user_id'];
            }
            unset($a_values['user_id']);
        }
        if (isset($a_values['user_name'])) {
            if ($a_values['user_name'] != '') {
                $user_name = $a_values['user_name'];
            }
            unset($a_values['user_name']);
        }
        if ($user_id == '' && $user_name == '') { return false; }
        /* the following keys in $a_values must have a value other than ''.
         * As such, they get removed from the sql
         * if they are trying to update the values to ''
         */
        $a_possible_keys = array(
            'real_name',
            'short_name',
            'password',
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
        $sql_set = $this->o_db->buildSqlSet($a_values, array('user_id', 'user_name'));
        $sql_where = isset($a_values['user_name'])
            ? 'WHERE user_name = :user_name'
            : isset($a_values['user_id'])
                ? 'WHERE user_id = :user_id'
                : '';
        if ($sql_where == '' || $sql_set == '') { return false; }
        $sql = "
            UPDATE {$this->db_prefix}users
            {$sql_set}
            {$sql_where}
        ";
        $this->o_elog->write($sql, LOG_ON, __METHOD__ . '.' . __LINE__);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Deletes a {$this->db_prefix}users record based on id.
     *  @param int $user_id required
     *  @return bool
    **/
    public function delete($user_id = -1)
    {
        if ($user_id == -1 || !ctype_digit($user_id)) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}users
            WHERE user_id = :user_id
        ";
        return $this->o_db->delete($sql, array(':user_id' => $user_id), true);
    }

    ### Single User Methods ###
    /**
     *  Gets the user id for a specific user_name.
     *  @param string $user_name required
     *  @return int|bool $user_id
     */
    public function getId($user_name = '')
    {
        if ($user_name == '') { return false; }
        $a_results = $this->read(array('user_name' => $user_name));
        if ($a_results !== false) {
            if (isset($a_results[0]) && $a_results[0] != array()) {
                return $a_results[0]['user_id'];
            }
        }
        return false;
    }
    /**
     *  Updates the bad_login_count field for the user by one
     *  @param int $user_id
     *  @return bool
     **/
    public function incrementBadLoginCount($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}users
            SET bad_login_count = bad_login_count + 1
            WHERE user_id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Increments the bad_login_ts record by one minute
     *  @param int $user_id required
     *  @return bool
     */
    public function incrementBadLoginTimestamp($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}users
            SET bad_login_ts = bad_login_ts + 60
            WHERE user_id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Returns the user record.
     *  @param int|string $user either user id or user name
     *  @return array|bool
     */
    public function readUserRecord($user = '')
    {
        if ($user == '') { return false; }
        if (ctype_digit($user)) {
            $a_search_by = ['$user_id' => $user];
        }
        else {
            $a_search_by = ['user_name' => $user];
        }
        $a_records = $this->read($a_search_by);
        if (is_array($a_records[0])) {
            return $a_records[0];
        } else {
            return false;
        }
    }
    /**
     *  Resets the bad_login_count to 0
     *  @param int $user_id required
     *  @return bool
     **/
    public function resetBadLoginCount($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}users
            SET bad_login_count = 0
            WHERE user_id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Resets the timestamp to 0
     *  @param int $user_id required
     *  @return bool
    **/
    public function resetBadLoginTimestamp($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        $update_sql = "
            UPDATE {$this->db_prefix}users
            SET bad_login_ts = 0
            WHERE user_id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        $results = $this->o_db->update($update_sql, $a_values, true);
        return $results;
    }
    /**
     *  Sets the bad login timestamp for the user.
     *  @param int $user_id required
     *  @return bool
    **/
    public function setBadLoginTimestamp($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}users
            SET bad_login_ts = :timestamp
            WHERE user_id = :user_id
        ";
        $a_values = array(':user_id' => $user_id, ':timestamp' => time());
        $results = $this->o_db->update($sql, $a_values, true);
        return $results;
    }
    /**
     *  Updates the user record with a new password
     *  @param int    $user_id required
     *  @param string $password required
     *  @return bool success or failure
     */
    public function updatePassword($user_id = -1, $password = '')
    {
        if ($user_id == -1 || $password == '') { return false; }
        $sql = "
            UPDATE {$this->db_prefix}users
            SET password = :password
            WHERE id = :user_id
        ";
        $a_values = [':user_id' => $user_id, ':password' => $password];
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Updates the user record to be make the user active or inactive, normally inactive.
     *  @param int $user_id   required id of a user
     *  @param int $is_active optional defaults to inactive (0)
     *  @return bool success or failure
     */
    public function updateActive($user_id = -1, $is_active = 0)
    {
        if ($user_id == -1) { return false; }
        $is_active = (int) $is_active;
        if ($is_active > 1) {
            $is_active = 1;
        }
        if ($is_active == '') {
            $is_active = 0;
        }
        $sql = "
            UPDATE {$this->db_prefix}users
            SET is_active = :is_active
            WHERE user_id = :user_id
        ";
        $a_values = [':user_id' => $user_id, ':is_active' => $is_active];
        return $this->o_db->update($sql, $a_values, true);
    }

    ### More complex methods using multiple tables ###
    /**
     *  Gets the user values based on user_name or user_id.
     *  @param mixed $user_id the user id or user_name (as defined in the db)
     *  @return array, the values for the user
    **/
    public function readInfo($user_id = '')
    {
        if ($user_id == '') { return false; }
        if (ctype_digit($user_id)) {
            $where = "u.user_id = {$user_id} ";
        }
        else {
            $where = "u.user_name = '{$user_id}' ";
        }
        $sql = "
            SELECT r.role_id, r.role_level, r.role_name
                u.user_id, u.user_name, u.real_name, u.short_name,
                u.password, u.is_default, u.created_on, u.bad_login_count,
                u.bad_login_ts, u.is_active, u.is_default,
                g.group_id, g.group_name, g.group_description
            FROM {$this->db_prefix}roles as r,
                 {$this->db_prefix}users as u,
                 {$this->db_prefix}groups as g,
                 {$this->db_prefix}user_group_map as ug,
                 {$this->db_prefix}user_role_map as ur
            WHERE ur.user_id = u.user_id
            AND ur.role_id = r.role_id
            AND ug.user_id = u.user_id
            AND ug.group_id = g.group_id
            AND {$where}
        ";
        $this->o_elog->write("Select User: {$sql}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->search($sql);
        if (isset($results[0]) && is_array($results[0])) {
            return $results[0];
        }
        else {
            return false;
        }
    }
    /**
     *  Selects the users and returns the data.
     *  Can return all the users or just the users for the specified role.
     *  @param string $group_name optional Returns uses only in this group
     *  @param string $role optional. Returns users only in this role if provided.
     *  @param bool $only_active optional. By default only returns active users. False returns all users.
     *  @return array, array of users
     **/
    public function readUsersInfo($group_name = '', $role = '', $only_active = true )
    {

        $sql = "
            SELECT u.user_id, u.user_name, u.real_name, u.short_name, u.password, u.is_default, u.is_active
                r.role_id, r.role_name,
                g.group_id, g.group_name
            FROM {$this->db_prefix}users as u,
                {$this->db_prefix}roles as r,
                {$this->db_prefix}groups as g,
                {$this->db_prefix}user_groups as ug,
                {$this->db_prefix}user_roles as ur
            WHERE ur.role_id = r.role_id
            AND ur.user_id = u.user_id
            AND ug.user_id = u.user_id
            AND ug.group_id = g.group_id
        ";
        if ($group_name != '') {
            $sql .= "
                AND g.group_name LIKE '{$group_name}'
            ";
        }
        if ($role != '') {
            if (ctype_digit($role)) {
                $sql .= "
                    AND r.role_id = {$role} ";
            }
            else {
                $sql .= "
                    AND r.role_name = '{$role}' ";
            }
        }
        if ($only_active) {
            $sql .= "
                AND u.is_active >= 1";
        }
        $sql .= " ORDER BY g.group_name ASC, u.real_name ASC";
        $this->o_elog->write("SQL: {$sql}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql);
    }
    /**
     *  Saves the user.
     *  If the values contain a value of user_id, user is updated
     *  Else it is a new user.
     *  @param array $a_user values to save
     *  @return mixed, user_id or false
     **/
    public function saveUser(array $a_user = array())
    {
        $method = __METHOD__ . '.';
        if (count($a_user) == 0) {
            return false;
        }
        $this->o_elog->write("a_user before changes: " . var_export($a_user, true), LOG_OFF, $method  . __LINE__);

        if (!isset($a_user['user_id']) || $a_user['user_id'] == '') { // New User
            $o_group = new GroupsModel($this->o_db);
            $o_role  = new RolesModel($this->o_db);
            $o_ugm   = new UserGroupMapModel($this->o_db);
            $o_urm   = new UserRoleMapModel($this->o_db);
            $a_required_keys = array(
                'user_name',
                'real_name',
                'short_name',
                'password',
                'is_default'
            );
            $a_user_values = array();
            foreach($a_required_keys as $key_name) {
                $a_user_values[$key_name] = isset($a_user[$key_name]) ? $a_user[$key_name] : '' ;
            }
            $this->o_elog->write("" . var_export($a_user_values , true), LOG_OFF, $method  . __LINE__);
            if ($a_user_values['password'] == '') {
                return false;
            }
            else {
                $a_user_values['password'] = password_hash($a_user_values['password'], PASSWORD_DEFAULT);
            }
            if ($this->o_db->startTransaction()) {
                $new_user_id = $this->create($a_user_values);
                if ($new_user_id !== false) {
                    $group_id = '';
                    $role_id  = '';
                    if (isset($a_user['group_id']) && $a_user['group_id'] != '') {
                        $group_id = $o_group->isValidGroupId($a_user['group_id']) ? $a_user['group_id'] : '';
                    }
                    if ($group_id == '' && isset($a_user['group_name']) && $a_user['group_name'] != '') {
                        $a_group = $o_group->read(array('group_name' => $a_user['group_name']));
                        if ($a_group !== false) {
                            $group_id = $a_group['group_id'];
                        }
                    }
                    if (isset($a_user['role_id']) && $a_user['role_id'] != '') {
                        $role_id = $o_role->isValidId($a_user['role_id']) ? $a_user['role_id'] : '';
                    }
                    if ($role_id == '' && isset($a_user['role_name']) && $a_user['role_name'] != '') {
                        $a_role = $o_role->read(array('role_name' => $a_user['role_name']));
                        if ($a_role !== false) {
                            $role_id = $a_role['id'];
                        }
                    }
                    if ($group_id != '') {
                        $a_ug_values = array('user_id' => $new_user_id, 'group_id' => $group_id);
                        $ug_results = $o_ugm->create($a_ug_values);
                        if ($ug_results) {
                            $a_ur_values = array('user_id' => $new_user_id, 'role_id' => $role_id);
                            $ur_results = $o_urm->create($a_ur_values);
                            if($ur_results) {
                                if ($this->o_db->commitTransaction()) {
                                    return $new_user_id;
                                } // commit transaction
                            } // insertUserRole != false
                        } // insertUserGroup != false
                    } // group_id != ''
                } // new_user_id !== false
                $this->o_db->rollbackTransaction();
                return false;
            }
            else {
                return false;
            } // this->o_db->startTransaction
        }
        else { // Existing User
            if (isset($a_user['user_id']) && $a_user['user_id'] != '') {
                $user_id = $a_user['user_id'];
            }
            elseif (isset($a_user['user_name']) && $a_user['user_name'] != '') {
                $user_id = $this->getId($a_user['user_name']);
            }
            else {
                $user_id = false;
            }
            $results = $this->update($a_user);
            if ($results !== false) {
                return $user_id;
            }
        }
        return false;
    }

}
