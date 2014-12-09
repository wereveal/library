<?php
/**
 *  @brief Manages User Access to the site.
 *  @details It is expected that this will be used within a controller and
 *  more finely grained access with be handled there or in a sub-controller.
 *  @file Access.php
 *  @ingroup ritc_library services library
 *  @namespace Ritc/Library/Services
 *  @class Access
 *  @author William E Reveal  <bill@revealitconsulting.com>
 *  @version 4.0.4
 *  @date 2014-11-17 14:35:29
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v4.0.4 - switched to using IOC/DI                              - 11/17/2014 wer
 *      v4.0.3 - moved to the Services namespace                       - 11/15/2014 wer
 *      v4.0.2 - part of the refactoring of the user model             - 11/11/2014 wer
 *      v4.0.1 - updated to implement the changes to the Base class    - 09/23/2014 wer
 *               Bug fixes.
 *      v4.0.0 - Changed to use the user/group/role model classes      - 09/12/2014 wer
 *      v3.6.1 - Changed to use DbModel defined table prefix,          - 02/24/2014 wer
 *               bug fix, added anti-spambot code to login
 *               and some code clean up
 *      v3.6.0 - Database changes, added new user role connector table - 11/12/2013 wer
 *               New and revised methods to match database changes.
 *               General Clean up of the code.
 *      v3.5.5 - refactor for change in the database class             - 2013-11-06 wer
 *      v3.5.4 - changed namespace and library reorg                   - 07/30/2013 wer
 *      v3.5.3 - changed namespace to match my framework namespace,    - 04/22/2013 wer
 *               refactored to match Elog method name change
 *      v3.5.2 - database methods were renamed, changed to match
 *      v3.5.1 - changed namespace to match Symfony structure
 *      v3.5.0 - new methods to handle user groups, lots of minor changes
 *      v3.4.5 - modified the code to be closer to FIG standards and removed controller code (this is Model)
 *      v3.4.0 - added short_name to Access, changing Real Name back to a real name
 *      v3.3.0 - Refactored to extend the Base class
 *      v3.2.0 - changed real name field to being just short_name, a temporary fix for a particular customer, wasn't intended to be permanent
 *  </pre>
 * @TODO move all database calls to the individual Model classes.
**/
namespace Ritc\Library\Services;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\UsersModel;

class Access extends Base
{
    private $db_prefix;
    private $o_db;
    private $o_groups;
    private $o_roles;
    private $o_session;
    private $o_users;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_db      = $o_di->get('db');
        $this->o_session = $o_di->get('session');
        $this->o_users   = new UsersModel($this->o_db);
        $this->o_groups  = new GroupsModel($this->o_db);
        $this->o_roles   = new RolesModel($this->o_db);
        $this->db_prefix = $this->o_db->getDbPrefix();
    }

    #### Actions ####
    /**
     *  Does the actual login, verifies valid user.
     *
     *  @pre a form has been submitted from the site with all the needed
     *      variables which have been put through a data cleaner.
     *
     *  @param $a_login (array), required with the following keys
     *      array(
     *          'login_id'=>'something',
     *          'password'=>'something',
     *          'tolken'=>'something',
     *          'form_ts'=>'000000',
     *          'hobbit'=>'' <-- hobbit should always be blank for valid submission (optional element)
     *      ).
     *
     *  @return mixed, (int) user_id or (bool) false
    **/
    public function login(array $a_user_post = array())
    {
        $meth = __METHOD__ . '.';
        if ($a_user_post == array()) { return false; }
        $a_required = array('login_id', 'password', 'tolken', 'form_ts');
        foreach ($a_required as $required) {
            if (isset($a_user_post[$required]) === false) {
                return false;
            }
        }
        if ($this->o_session->isValidSession)
        $a_user_values = $this->o_users->readUserInfo($a_user_post['login_id']);
        $this->logIt("Posted Values: " . var_export($a_user_post, true), LOG_OFF, $meth . __LINE__);
        $this->logIt("User Values: " . var_export($a_user_values, true), LOG_OFF, $meth . __LINE__);
        if ($a_user_values !== false && $a_user_values !== null) {
            if ($a_user_values['is_active'] < 1) {
                $this->o_users->incrementBadLoginTimestamp($a_user_values['user_id']);
                $this->o_users->incrementBadLoginCount($a_user_values['user_id']);
                $a_user_post['password'] = '';
                $a_user_post['is_active'] = false;
                return $a_user_post;
            }
            if ($a_user_values['bad_login_count'] > 5 && $a_user_values['bad_login_ts'] >= (time() - (60*5))) {
                $this->o_users->incrementBadLoginTimestamp($a_user_values['user_id']);
                $a_user_post['password'] = '';
                $a_user_post['locked'] = 'yes';
                return $a_user_post;
            }
            // simple anti-spambot thing... if the form has it.
            if (isset($a_user_post['hobbit']) && $a_user_post['hobbit'] != '') {
                $this->o_users->setBadLoginTimestamp($a_user_values['user_id']);
                $this->o_users->incrementBadLoginCount($a_user_values['user_id']);
                return false;
            }
            $a_user_post['created_on'] = $a_user_values['created_on'];
            $a_user_post['user_id'] = $a_user_values['user_id'];
            $hashed_password = password_hash($a_user_post['password'],PASSWORD_DEFAULT);
            if ($a_user_values['password'] == $hashed_password) {
                $this->o_users->resetBadLoginCount($a_user_values['user_id']);
                $this->o_users->resetBadLoginTimestamp($a_user_values['user_id']);
                $a_user_values['tolken'] = $a_user_post['tolken'];
                $a_user_values['form_ts'] = $a_user_post['form_ts'];
                $a_user_values['locked'] = 'no';
                unset($a_user_values['password']);
                unset($a_user_values['bad_login_count']);
                unset($a_user_values['bad_login_ts']);
                $this->logIt("After password check: " . var_export($a_user_values, true), LOG_OFF, $meth . __LINE__);
                return $a_user_values;
            } else {
                $this->o_users->setBadLoginTimestamp($a_user_values['user_id']);
                $this->o_users->incrementBadLoginCount($a_user_values['user_id']);
                return false;
            }
        }
        return false;
    }

    #### Verifiers ####
    /**
     *  Verifies user has the role of super administrator.
     *  @param int $user_id required
     *  @return bool - true = is a super admin, false = not a super admin
    **/
    public function isSuperAdmin($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        $a_user = $this->o_users->readInfo($user_id);
        if ($a_user === false) { return false; }
        if ($a_user['access_level'] === 1) {
            return true;
        }
        return false;
    }
    /**
     *  Checks to see if the value is a valid group id or name.
     *  @param int|string $group
     *  @return bool true or false
     **/
    public function isValidGroup($group = -1)
    {
        if ($group == -1) { return false; }
        if (ctype_digit($group)) {
            $a_search_by = ['group_id' => $group];
        }
        else {
            $a_search_by = ['group_name' => $group];
        }
        if (is_array($this->o_groups->read($a_search_by))) {
            return true;
        }
        return false;
    }
    /**
     * Verifies the group id provided is a valid id
     * @param int $group
     * @return bool
     */
    public function isValidGroupId($group = -1)
    {
        if ($group == -1) { return false; }
        if (ctype_digit($group)) {
            return $this->isValidGroup($group);
        }
        return false;
    }
    /**
     *  Checks to see if the value is a valid role id or name.
     *  @param int|string $role
     *  @return bool true or false
     **/
    public function isValidRole($role = -1)
    {
        if ($role == -1) { return false; }
        if (ctype_digit($role)) {
            $a_search_by = ['role_id' => $role];
        }
        else {
            $a_search_by = ['role_name' => $role];
        }
        if (is_array($this->o_roles->read($a_search_by))) {
            return true;
        }
        return false;
    }
    /**
     * Verifies the role id provided is a valid id.
     * Uses the isValidRole method
     * @param int $role_id
     * @return bool
     */
    public function isValidRoleId($role_id = -1)
    {
        if ($role_id == -1) { return false; }
        if (ctype_digit($role_id)) {
            return $this->isValidRole($role_id);
        }
        return false;
    }
    /**
     *  Checks to see if user exists.
     *  @param int|string $user user id or user name
     *  @return bool
    **/
    public function isValidUser($user = '')
    {
        if ($user == '') { return false; }
        if (is_array($this->o_users->readUserRecord($user))) {
            return true;
        }
        return false;
    }
    /**
     *  Checks to see if the user by id exists.
     *  Uses the isValidUser method.
     *  @param int $user_id required
     *  @return bool
     */
    public function isValidUserId($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        if (ctype_digit($user_id)) {
            return $this->isValidUser($user_id);
        }
        return false;
    }
    /**
     *  Figures out if the user is specified as a default user.
     *  @param string|int $user
     *  @return bool true false
     */
    public function isDefaultUser($user)
    {
        $a_results = $this->o_users->readInfo($user);
        if (isset($a_results['is_default'])) {
            if ($a_results['is_default'] == 1) {
                return true;
            }
        }
        return false;
    }
    /**
     *  Checks to see if the user id exists.
     *  @param int $user_id
     *  @return bool true false
     **/
    public function userIdExists($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        if ($this->o_users->isID($user_id)) {
            $results = $this->o_users->read(array('user_id' => $user_id));
            if (isset($results['user_id']) && $results['user_id'] == $user_id) {
                return true;
            }
        }
        return false;
    }
    /**
     * Checks to see if the user is in the group.
     * @param int|string $user
     * @return bool
     */
    public function userInGroup($user = -1)
    {
        if ($user == -1) { return false; }

        return false;
    }
    /**
     *  Checks to see if the login_id exists.
     *  @param string $login_id
     *  @return bool
     **/
    public function loginIdExists($login_id = '')
    {
        if ($login_id == '') { return false; }
        $results = $this->o_users->read(array('login_id' => $login_id));
        if (isset($results['login_id']) && $results['login_id'] == $login_id) {
            return true;
        }
        return false;
    }
    /**
     *  Checks to see if the password provided is valid for user.
     *  @param array $a_user required $a_user['password'] and either $a_user['user_id'] or $a_user['login_id']
     *  @return bool
     */
    public function validPassword(array $a_user = array())
    {
        if (isset($a_user['user_id']) === false && isset($a_user['login_id']) === false ) {
            return false;
        }
        if ($a_user['user_id'] == '' && $a_user['login_id'] == '') {
            return false;
        }
        if (isset($a_user['password']) === false || $a_user['password'] == '') {
            return false;
        }
        $hashed_password = password_hash($a_user['password'], PASSWORD_DEFAULT);
        if (isset($a_user['user_id']) && $a_user['user_id'] != '') {
            $a_find_this = ['user_id' => $a_user['user_id']];
        }
        else {
            $a_find_this = ['login_id' => $a_user['login_id']];
        }
        $a_results = $this->o_users->read($a_find_this);
        if (isset($a_results[0])) {
            $this_user = $a_results[0];
            if ($hashed_password == $this_user['password']) {
                return true;
            }
        }
        return false;
    }

    #### Database Operations ####
    /**
     *  Deletes Specified User by id
     *  @param int $user_id required, id of user
     *  @return bool, success or failure
    **/
    public function deleteUser($user_id = -1)
    {
        if ($user_id == -1) { return false; }
        $sql = "DELETE FROM {$this->db_prefix}users WHERE id = :user_id";
        $a_user = array(':user_id' => $user_id);
        return $this->o_db->delete($sql, $a_user, true);
    }
    /**
     *  Deletes the user group record for the user
     *  @param int $user_id required
     *  @param int $group_id required
     *  @return bool
    **/
    public function deleteUserGroup($user_id = -1, $group_id = -1)
    {
        if ($user_id == -1 || $group_id == -1) { return false; }
        $sql = "DELETE FROM {$this->db_prefix}user_groups WHERE user_id = :user_id AND group_id = :group_id";
        $a_values = array(':user_id' => $user_id, ':group_id' => $group_id);
        return $this->o_db->delete($sql, $a_values, true);
    }
    /**
     *  Deletes the user role record for the user
     *  @param int $user_id required
     *  @param int $role_id required
     *  @return bool
    **/
    public function deleteUserRole($user_id = -1, $role_id = -1)
    {
        if ($user_id == -1 || $role_id == -1) { return false; }
        $sql = "DELETE FROM {$this->db_prefix}user_roles WHERE user_id = :user_id AND role_id = :role_id";
        $a_values = array(':user_id' => $user_id, ':role_id' => $role_id);
        return $this->o_db->delete($sql, $a_values, true);
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
            WHERE id = :user_id
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
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Creates a new user record.
     *  @param array $a_values, required, values for user record, needs to
     *      be in format for prepared queries.
     *  @return mixed, user_id or false if failure.
    **/
    public function insertUser(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $sql = "
            INSERT INTO {$this->db_prefix}users (login_id, real_name, short_name, password, is_default)
            VALUES (:login_id, :real_name, :short_name, :password, :is_default)";
        if ($this->o_db->insert($sql, $a_values, '{$this->db_prefix}users')) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        } else {
            return false;
        }
    }
    /**
     *  Inserts a new record connecting the user to the group
     *  @param array $a_values array that uses valid prepared sql format
     *  @return bool success or failure
    **/
    public function insertUserGroup(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $sql = "INSERT INTO {$this->db_prefix}user_groups (user_id, group_id) VALUES (:user_id, :group_id)";
        if ($this->o_db->insert($sql, $a_values, '{$this->db_prefix}user_groups')) {
            $ids = $this->o_db->getNewIds();
            return $ids[0];
        } else {
            return false;
        }
    }
    /**
     *  Inserts a new record connecting the user to the role
     *  @param array $a_values array that uses valid prepared sql format
     *  @return bool success or failure
    **/
    public function insertUserRole(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $sql = "INSERT INTO {$this->db_prefix}user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
        if ($this->o_db->insert($sql, $a_values, '{$this->db_prefix}user_roles')) {
            $ids = $this->o_db->getNewIds();
            return $ids[0];
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
            WHERE id = :user_id
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
        $sql = "
            UPDATE {$this->db_prefix}users
            SET bad_login_ts = 0
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Selects a role by the id.
     *  @param int $role_id
     *  @return array
    **/
    public function selectRoleById($role_id = -1)
    {
        if ($role_id == '') { return false; }
        $sql = "
            SELECT id, name, description, access_level
            FROM {$this->db_prefix}roles
            WHERE id = {$role_id}
        ";
        $results = $this->o_db->search($sql);
        if (is_array($results[0])) {
            return $results[0];
        } else {
            return false;
        }
    }
    /**
     *  Returns values for a role by role name.
     *  @param string $role_name
     *  @return array values for role
    **/
    public function selectRoleByName($role_name = '')
    {
        $sql = "
            SELECT id, name, description, access_level
            FROM {$this->db_prefix}roles
            WHERE name LIKE '{$role_name}'
        ";
        $results = $this->o_db->search($sql);
        if (is_array($results[0])) {
            return $results[0];
        } else {
            return false;
        }
    }
    /**
     *  Selects the role information from db.
     *  @param int $access_level
     *  @return array, role data
    **/
    public function selectRoles($access_level = 3)
    {
        $sql = "
            SELECT id, name, description, access_level
            FROM {$this->db_prefix}roles
            WHERe access_level >= {$access_level}
            ORDER BY access_level ASC";
        $this->logIt("sql: {$sql}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $this->o_db->search($sql);
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
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id, ':timestamp' => time());
        $results = $this->o_db->update($sql, $a_values, true);
        return $results;
    }
    /**
     *  Updates an existing user.
     *  @param array $a_values required, values for user record in prepared format
     *  @return mixed, user_id or false if failure
    **/
    public function updateUser(array $a_values = array())
    {
        if ($a_values == array() || $a_values[':user_id'] == '') {
            return false;
        }
        if ($a_values[':password'] == '') {
            $sql = "
                UPDATE {$this->db_prefix}users
                SET login_id   = :login_id,
                    real_name  = :real_name,
                    short_name = :short_name,
                    is_default = :is_default
                WHERE id = :user_id";
            unset($a_values[':password']);
        } else {
            $sql = "
                UPDATE {$this->db_prefix}users
                SET login_id   = :login_id,
                    real_name  = :real_name,
                    short_name = :short_name,
                    password   = :password,
                    is_default = :is_default
                WHERE id = :user_id";
        }
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Updates the user record with a new password
     *  @param array $a_values in prepared format
     *   e.g., array(':password'=>'password', ':user_id'=>'userID')
     *  @return bool success or failure
    **/
    public function updateUserPassword(array $a_values = array())
    {
        if ($a_values == array()) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}users
            SET password = :password
            WHERE id = :user_id
        ";
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     *  Updates the user record to be make the user active or inactive, normally inactive.
     *
     *  @param string   $user_id   required id of a user
     *  @param bool|int $is_active optional defaults to inactive (0)
     *
     *  @return bool success or failure
     */
    public function updateUserToInactive($user_id = '', $is_active = 0) {
        $sql = "
            UPDATE {$this->db_prefix}users
            SET is_active = :is_active
            WHERE id = :user_id
        ";
        $a_values = array(':user_id' => $user_id, ':is_active' => $is_active);
        return $this->o_db->update($sql, $a_values, true);
    }

    ### Archive ###
    /**
     *  Hashes a password using variables in a user's record to create the hash salt.
     *  After a lot of thought, this method isn't any more secure really than
     *  using php5.5 password_hash() function (or equivalent crypt() based code) and is a lot more work.
     *  Leaving this here just in case.
     *  @param array $a_user required array('created_on', 'user_id', 'password')
     *  @return string the hashed password
    **/
    protected function hashPassword(array $a_user = array())
    {
        if ($a_user == array()) { return false; }
        $this->logIt("a_user: " . var_export($a_user, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $salt = substr(hash('sha512', $a_user['created_on'] . ' ' . $a_user['user_id']), 0, 32);
        $hashed_password = hash('sha512', $salt . $a_user['password'], false);
        $this->logIt("salt: {$salt} hash: {$hashed_password}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $hashed_password;
    }

    ### Magic Method fix to keep this class a singleton ####
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

}
