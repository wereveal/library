<?php
/**
 *  Manages User Access to sections of the site.
 *  @file Access.php
 *  @class Access
 *  @author William Reveal  <wer@wereveal.com>
 *  @version 3.5.2
 *  @par Change Log
 *      v3.5.2 - database methods were renamed, changed to match
 *      v3.5.1 - changed namespace to match Symfony structure
 *      v3.5.0 - new methods to handle user groups, lots of minor changes
 *      v3.4.5 - modified the code to be closer to FIG standards and removed controller code (this is Model)
 *      v3.4.0 - added short_name to Access, changing Real Name back to a real name
 *      v3.3.0 - Refactored to extend the Base class
 *      v3.2.0 - changed real name field to being just short_name, a temporary fix for a particular customer, wasn't intended to be permanent
 *  @par Wer Framework version 4.0
 *  @date 2013-03-30 10:58:56
 *  @ingroup wer_framework classes
**/
namespace Wer\FrameworkBundle\Library;

class Access extends Base
{
    protected $current_page;
    protected $o_elog;
    protected $o_db;
    protected $private_properties;
    private static $instance;
    private function __construct()
    {
        $this->o_elog = Elog::start();
        $this->setPrivateProperties();
        $this->o_db = Database::start();
        $this->o_elog->setFromFile(__FILE__);
    }
    /** Access class is a singleton and this gets it started.
        @return obj - instance of Access
    **/
    public static function start()
    {
            if (!isset(self::$instance)) {
                    $c = __CLASS__;
                    self::$instance = new $c;
            }
            return self::$instance;
    }

    #### Actions ####
    /** Does the actual login, verifies valid user.
        @pre a form has been submitted from the site with all the needed
            variables which have been put through a data cleaner.
        @param $a_login (array), required with the following keys
            array(
                'username'=>'something',
                'password'=>'something',
                'token'=>'something',
                'form_ts').
        @return mixed, (int) user_id or (bool) false
    **/
    public function login($a_login = '')
    {
        if ($a_login == '') { return false; }
        $a_required = array('username', 'password', 'token', 'form_ts');
        foreach ($a_required as $required) {
            if (isset($a_login[$required]) === false) {
                return false;
            }
        }
        $a_user_values = $this->selectUser($a_login['username']);
        $this->o_elog->write("Posted Values: " . var_export($a_login, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->o_elog->write("User Values: " . var_export($a_user_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($a_user_values !== false && $a_user_values !== null) {
            if ($a_user_values['is_active'] < 1) {
                $this->incrementBadLoginTimestame($a_user_values['user_id']);
                $this->incrementBadLoginCount($a_user_values['user_id']);
                $a_login['password'] = '';
                $a_login['is_active'] = false;
                return $a_login;
            }
            if ($a_user_values['bad_login_count'] > 5 && $a_user_values['bad_login_ts'] >= (time() - (60*5))) {
                $this->incrementBadLoginTimestamp($a_user_values['user_id']);
                $a_login['password'] = '';
                $a_login['locked'] = 'yes';
                return $a_login;
            } elseif ($a_user_values['bad_login_count'] > 0 && $a_user_values['bad_login_ts'] < (time() - (60*5))) {
                $this->resetBadLoginCount($a_user_values['user_id']);
                $this->resetBadLoginTimestamp($a_user_values['user_id']);
            }
            $a_login['created_on'] = $a_user_values['created_on'];
            $a_login['user_id'] = $a_user_values['user_id'];
            $hashed_password = $this->hashPassword($a_login);
            if ($a_user_values['password'] == $hashed_password) {
                $this->resetBadLoginCount($a_user_values['user_id']);
                $this->resetBadLoginTimestamp($a_user_values['user_id']);
                $a_user_values['token'] = $a_login['token'];
                $a_user_values['form_ts'] = $a_login['form_ts'];
                $a_user_values['locked'] = 'no';
                unset($a_user_values['password']);
                unset($a_user_values['bad_login_count']);
                unset($a_user_values['bad_login_ts']);
                $this->o_elog->write("After password check: " . var_export($a_user_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return $a_user_values;
            } else {
                $this->setBadLoginTimestamp($a_user_values['user_id']);
                $this->incrementBadLoginCount($a_user_values['user_id']);
                return false;
            }
        }
        return false;
    }
    /**
     * Saves the user
     * @param array $a_user values to save
     * @param str $new optional defaults to update existing user, if set, unless = 'update' treat as a new user
     * @return mixed, user_id or false
    **/
    public function saveUser($a_user = array(), $new = '')
    {
        if (is_array($a_user) === false || count($a_user) == 0) {
            return false;
        }
        $this->o_elog->write("a_user before changes: " . var_export($a_user, true), LOG_OFF, __METHOD__ . '.' . __LINE__);

        if ($new != '' && $new != 'update') { // New User
            $a_required_keys = array(
                'role_id',
                'username',
                'real_name',
                'short_name',
                'password',
                'is_default'
            );
            $a_user_values = array();
            foreach($a_required_keys as $key_name) {
                $a_user_values[$key_name] = isset($a_user[$key_name]) ? $a_user[$key_name] : '' ;
            }
            $this->o_elog->write("" . var_export($a_user_values , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            if ($a_user_values['password'] == '') {
                return false;
            }
            if ($this->o_db->startTransaction()) {
                $a_prepared_user = $this->o_db->prepareKeys($a_user_values);
                $this->o_elog->write("prepared user: " . var_export($a_prepared_user, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                $new_user_id = $this->insertUser($a_prepared_user);
                if ($new_user_id !== false) {
                    $group_id = '';
                    if (isset($a_user['group_id']) && $a_user['group_id'] != '') {
                        $group_id = $this->isValidGroupId($a_user['group_id']) ? $a_user['group_id'] : '';
                    }
                    if ($group_id == '' && isset($a_user['group_name']) && $a_user['group_name'] != '') {
                        $a_group = $this->selectGroupByName($a_user['group_name']);
                        if ($a_group !== false) {
                            $group_id = $a_group['group_id'];
                        }
                    }
                    if ($group_id != '') {
                        $a_prepared_values = array(':user_id'=>$new_user_id, ':group_id'=>$group_id);
                        $results = $this->insertUserGroup($a_prepared_values);
                        if ($results) {
                            if ($this->o_db->commitTransaction()) {
                                $a_new_user = $this->selectUser($new_user_id);
                                $this->o_elog->write("Inserted user before password hash: " . var_export($a_new_user, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                                $hashed_password = $this->hashPassword($a_new_user);
                                $a_values = array(
                                    ':user_id'  => $new_user_id,
                                    ':password' => $hashed_password
                                );
                                $this->o_elog->write("hashed password: " . var_export($a_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                                $results = $this->updateUserPassword($a_values);
                                if ($results) {
                                    return $new_user_id;
                                } else {
                                    $this->deleteUserGroup($new_user_id, $group_id);
                                    $this->deleteUser($new_user_id);
                                    return false;
                                }
                            }
                        }
                    }
                }
                $this->o_db->rollbackTransaction();
                return false;
            } else {
                return false;
            }
        } else { // Existing User
            $a_required_keys = array(
                'user_id',
                'role_id',
                'username',
                'real_name',
                'short_name',
                'password',
                'is_default'
            );
            $a_user_values = array();
            foreach($a_required_keys as $key_name) {
                $a_user_values[$key_name] = isset($a_user[$key_name]) ? $a_user[$key_name] : '' ;
            }
            if ($a_user_values['password'] != '') {
                $a_pre_update = $this->selectUser($a_user_values['user_id']);
                $a_pre_update['password'] = $a_user_values['password'];
                $a_user_values['password'] = $this->hashPassword($a_pre_update);
            }
            $a_prepared_user = $this->o_db->prepareKeys($a_user_values);
            $this->o_elog->write("Prepared Array: " . var_export($a_prepared_user , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            $results = $this->updateUser($a_prepared_user);
            if ($results !== false) {
                return $a_user_values['user_id'];
            }
        }
        return false;
    }

    #### Verifiers ####
    /** Checks to see if the param is an id or a name.
        A name can not start with a numeric character so if the param starts
        with a number, it is assumed to be an id (I know, assume = ...)
        @param $value (mixed), required
        @return bool
    **/
    public function isID($value = '')
    {
            $first_char = substr($value, 0, 1);
            if (preg_match('/[0-9]/', $first_char) === 1) {
                    return true;
            }
            return false;
    }
    /** Verifies user has the role of super administrator
        @param none
        @return bool - true = is a super admin, false = not a super admin
    **/
    public function isSuperAdmin($user_id = '')
    {
        $a_user = $this->selectUser($user_id);
        if ($a_user === false) { return false; }
        if ($a_user['access_level'] === 1) {
            return true;
        }
        return false;
    }
    /**
     * Checks to see if the id is a valid group id
     * @param int $group_id
     * @return bool true or false
     */
    private function isValidGroupId($group_id = '')
    {
        if ($group_id == '') { return false; }
        if (is_array($this->selectGroupById($group_id))) {
            return true;
        }
        return false;
    }
    /** Checks to see if user exists.
        @param $a_user (array), values which should include the user id and real name
        @return bool, True or False
    **/
    public function isValidUser($a_user = array())
    {
        if (isset($a_user['user_id']) && isset($a_user['username'])) {
            $a_user_info = $this->selectUser($a_user['user_id']);
            if ($a_user_info['username'] == $a_user['username']) {
                return true;
            }
        }
        return false;
    }
    /**
     * Figures out if the user is specified as a default user
     * @param $user_id required
     * @return bool true false
     */
    public function isDefaultUser($user_id = '')
    {
        if ($a_user == '') { return false; }
        $a_results = $this->selectUser($user_id);
        if ($a_results['is_default'] == 1) {
            return true;
        }
        return false;
    }
    /**
     * Checks to see if the user id exists
     * @param int $user_id
     * @return bool true false
     */
    public function userIdExists($user_id = '')
    {
        if ($user_id == '') { return false; }
        $results = $this->selectUser($user_id);
        if ($results !== false) {
            return true;
        }
        return false;
    }
    /**
     * Checks to see if the username exists
     * @param str $username
     * @return bool true false
     */
    public function usernameExists($username = '')
    {
        if ($username == '') { return false; }
        $results = $this->selectUser($username);
        if ($results !== false) {
            return true;
        }
        return false;
    }

    #### Database Operations ####
    /** Deletes Specified User by id
        @param $user_id (int), required, id of user
        @return bool, success or failure
    **/
    public function deleteUser($user_id = '')
    {
        if ($user_id == '') { return false; }
        $sql = "DELETE FROM sm_users WHERE id = :user_id";
        $a_user = array(':user_id'=>$user_id);
        return $this->o_db->deleteTransaction($sql, $a_user, true);
    }
    /**
     * Deletes the user group record for the user
     * @param int $user_id required
     * @param int $group_id required
     * @return bool
     */
    public function deleteUserGroup($user_id = '', $group_id = '')
    {
        if ($user_id == '' || $group_id == '') { return false; }
        $sql = "DELETE FROM sm_user_groups WHERE user_id = :user_id AND group_id = :group_id";
        $a_values = array(':user_id' => $user_id, ':group_id' => $group_id);
        return $this->o_db->deleteTransaction($sql, $a_values, true);
    }
    /**
     * Updates the bad_login_count field for the user by one
     * @param int $user_id
     * @return bool
    **/
    private function incrementBadLoginCount($user_id = '')
    {
        if ($user_id == '') { return false; }
        $sql = "
            UPDATE sm_users
            SET bad_login_count = bad_login_count + 1
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     * Increments the bad_login_ts record by one minute
     * @param int $user_id required
     * @return bool
    */
    private function incrementBadLoginTimestamp($user_id = '')
    {
        $sql = "
            UPDATE sm_users
            SET bad_login_ts = bad_login_ts + 60
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /** Creates a new user record.
        @param $a_values (array), required, values for user record, needs to
            be in format for prepared queries.
        @return mixed, user_id or false if failure.
    **/
    public function insertUser($a_values = '')
    {
        if ($a_values == '') { return false; }
        $sql = "
            INSERT INTO sm_users (role_id, username, real_name, short_name, password, is_default)
            VALUES (:role_id, :username, :real_name, :short_name, :password, :is_default)";
        if ($this->o_db->insert($sql, $a_values, 'sm_users')) {
            $ids = $this->o_db->getNewIds();
            $this->o_elog->write("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        } else {
            return false;
        }
    }
    /**
     * Inserts a new record connecting the user to the group
     * @param array $a_prepared_values array that uses valid prepared sql format
     * @return bool success or failure
     */
    public function insertUserGroup($a_prepared_values = '')
    {
        if ($a_prepared_values == '') { return false; }
        $sql = "INSERT INTO sm_user_groups (user_id, group_id) VALUES (:user_id, :group_id)";
        if ($this->o_db->insert($sql, $a_prepared_values, 'sm_user_groups')) {
            $ids = $this->o_db->getNewIds();
            return $ids[0];
        } else {
            return false;
        }
    }
    /**
     * Resets the bad_login_count to 0
     * @param int $user_id required
     * return bool
    **/
    private function resetBadLoginCount($user_id)
    {
        if ($user_id == '') { return false; }
        $sql = "
            UPDATE sm_users
            SET bad_login_count = 0
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     * Resets the timestamp to 0
     * @param int $user_id required
     * return bool
    **/
    private function resetBadLoginTimestamp($user_id)
    {
        if ($user_id == '') { return false; }
        $sql = "
            UPDATE sm_users
            SET bad_login_ts = 0
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     * selects a group by the id
     * @param int $group_id
     * @return array
     */
    public function selectGroupById($group_id = '')
    {
        if ($group_id == '') { return false; }
        $sql = "SELECT group_id, group_name, group_description FROM sm_groups WHERE group_id = {$group_id}";
        $results = $this->o_db->search($sql);
        if (is_array($results[0])) {
            return $results[0];
        } else {
            return false;
        }
    }
    /**
     * Returns values for a group by group name
     * @param str $group_name
     * @return array values for group
     */
    public function selectGroupByName($group_name = '')
    {
        $sql = "SELECT group_id, group_name, group_description FROM sm_groups WHERE group_name LIKE '{$group_name}'";
        $results = $this->o_db->search($sql);
        if (is_array($results[0])) {
            return $results[0];
        } else {
            return false;
        }
    }
    /** Selects the role information from db.
        @param none
        @return array, role data
    **/
    public function selectRoles($access_level = 3)
    {
        $sql = "
            SELECT id, name, access_level
            FROM sm_roles
            WHERe access_level >= {$access_level}
            ORDER BY access_level ASC";
        $this->o_elog->write("sql: {$sql}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql);
    }
    /** Gets the user values based on username.
        @param $username (str), the username (as defined in the db)
        @return array, the values for the user
    **/
    public function selectUser($user_id = '')
    {
        if ($user_id == '') { return false; }
        if ($this->isID($user_id)) {
            $where = "u.id = {$user_id} ";
        } else {
            $where = "u.username = '{$user_id}' ";
        }
        $sql = "
            SELECT r.id as role_id, r.access_level, r.name as role_name,
                u.id as user_id, u.username, u.real_name, u.short_name, u.password, u.is_default, u.created_on, u.bad_login_count, u.bad_login_ts, u.is_active,
                g.group_id, g.group_name
            FROM sm_roles as r, sm_users as u, sm_groups as g, sm_user_groups as ug
            WHERE r.id = u.role_id
            AND ug.user_id = u.id
            AND ug.group_id = g.group_id
            AND {$where}
        ";
        $this->o_elog->write("Select User: {$sql}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->search($sql);
        if (isset($results[0]) && is_array($results[0])) {
            return $results[0];
        } else {
            return false;
        }
    }
    /** Selects the users and returns the data.
        Can return all the users or just the users for the specified role.
        @param str $group_name optional Returns uses only in this group
        @param str $role optional. Returns users only in this role if provided.
        @param bool $only_active optional. By default only returns active users. False returns all users.
        @return array, array of users
    **/
    public function selectUsers($group_name = '', $role = '', $only_active = true )
    {

        $sql = "
            SELECT u.id, u.role_id, u.username, u.real_name, u.short_name, u.password, u.is_default,
                r.name as role,
                g.group_id, g.group_name
            FROM sm_users as u, sm_roles as r, sm_groups as g, sm_user_groups as ug
            WHERE u.role_id = r.id
            AND ug.user_id = u.id
            AND ug.group_id = g.group_id
        ";
        if ($group_name != '') {
            $sql .= "
                AND g.group_name LIKE '{$group_name}'
            ";
        }
        if ($role != '') {
            if ($this->isID($role)) {
                $sql .= "
                    AND r.id = {$role} ";
            } else {
                $sql .= "
                    AND r.name = {$role} ";
            }
        }
        if ($only_active !== false) {
            $sql .= "
                AND u.is_active >= 1";
        }
        $sql .= " ORDER BY g.group_name ASC, u.real_name ASC";
        $this->o_elog->write("SQL: {$sql}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql);
    }
    /**
     * Sets the bad login timestamp for the user.
     * @param int $user_id required
     * @return bool
    **/
    private function setBadLoginTimestamp($user_id = '')
    {
        if ($user_id == '') { return false; }
        $sql = "
            UPDATE sm_users
            SET bad_login_ts = :timestamp
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id, ':timestamp' => time());
        $results = $this->o_db->update($sql, $a_values, true);
        return $results;
    }
    /** Updates an existing user.
        @param $a_values (array), required, values for user record
        @return mixed, user_id or false if failure
    **/
    public function updateUser($a_values = '')
    {
        if ($a_values == '' || $a_values[':user_id'] == '') {
            return false;
        }
        if ($a_values[':password'] == '') {
            $sql = "
                UPDATE sm_users
                SET role_id    = :role_id,
                    username   = :username,
                    real_name  = :real_name,
                    short_name = :short_name,
                    is_default = :is_default
                WHERE id = :user_id";
            unset($a_values[':password']);
        } else {
            $sql = "
                UPDATE sm_users
                SET role_id    = :role_id,
                    username   = :username,
                    real_name  = :real_name,
                    short_name = :short_name,
                    password   = :password,
                    is_default = :is_default
                WHERE id = :user_id";
        }
        return $this->o_db->updateTransaction($sql, $a_values, true);
    }
    /**
     * Updates the user record with a new password
     * @param array $a_values in prepared format
     *   e.g., array(':password'=>'password', ':user_id'=>'userID')
     * @return bool success or failure
     */
    private function updateUserPassword($a_values = '')
    {
        if ($a_values == '') { return false; }
        $sql = "
            UPDATE sm_users
            SET password = :password
            WHERE id = :user_id
        ";
        return $this->o_db->updateTransaction($sql, $a_values, true);
    }
    /**
     * Updates the user record with a new password but not in a transaction
     * @param array $a_values in prepared format
     *   e.g., array(':password'=>'password', ':user_id'=>'userID')
     * @return bool success or failure
     */
    private function updateUserPasswordNT($a_values = '')
    {
        if ($a_values == '') { return false; }
        $sql = "
            UPDATE sm_users
            SET password = :password
            WHERE id = :user_id
        ";
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     * Updates the user record to be make the user active or inactive
     *   normally inactive.
     * @param user_id required id of a user
     * @param bool $is_active, optional defaults to inactive (0)
     * @return bool success or failure
    **/
    public function updateUserToInactive($user_id = '', $is_active = 0) {
        $sql = "
            UDPATE sm_users
            SET is_active = :is_active
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id, ':is_active' => $is_active);
        return $this->o_db->update($sql, $a_values, true);
    }

    ### Utility Private/Protected methods ###
    /**
     * hashes a password using the $salter as a bases for the hash salt
     * @param str $password required
     * @param str $salter required
     * @return str the hashed password
     */
    private function hashPassword($a_user = '')
    {
        if ($a_user == '') { return false; }
        $this->o_elog->write("a_user: " . var_export($a_user, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $salt = substr(hash('sha512', $a_user['created_on'] . ' ' . $a_user['user_id']), 0, 32);
        $hashed_password = hash('sha512', $salt . $a_user['password'], false);
        $this->o_elog->write("salt: {$salt} hash: {$hashed_password}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $hashed_password;
    }

    ### Magic Method fix to keep this class a singleton ####
    public function __clone()
    {
            trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

}
