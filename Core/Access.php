<?php
/**
 *  @brief Manages User Access to the site.
 *  @details It is expected that this will be used within a controller and
 *  more finely grained access with be handled there or in a sub-controller.
 *  @file Access.php
 *  @ingroup ritc_library core library
 *  @namespace Ritc/Library/Core
 *  @class Access
 *  @author William E Reveal  <bill@revealitconsulting.com>
 *  @version 4.0.0
 *  @date 2014-09-12 14:46:46
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v4.0.0 - Changed to use the user/group/role model classes - 09/12/2014 wer
 *      v3.6.1 - Changed to use DbModel defined table prefix, - 02/24/2014 wer
 *               bug fix, added anti-spambot code to login
 *               and some code clean up
 *      v3.6.0 - Database changes, added new user role connector table - 11/12/2013
 *               New and revised methods to match database changes.
 *               General Clean up of the code.
 *      v3.5.5 - refactor for change in the database class - 2013-11-06
 *      v3.5.4 - changed namespace and library reorg - 07/30/2013
 *      v3.5.3 - changed namespace to match my framework namespace, - 04/22/2013
 *               refactored to match Elog method name change
 *      v3.5.2 - database methods were renamed, changed to match
 *      v3.5.1 - changed namespace to match Symfony structure
 *      v3.5.0 - new methods to handle user groups, lots of minor changes
 *      v3.4.5 - modified the code to be closer to FIG standards and removed controller code (this is Model)
 *      v3.4.0 - added short_name to Access, changing Real Name back to a real name
 *      v3.3.0 - Refactored to extend the Base class
 *      v3.2.0 - changed real name field to being just short_name, a temporary fix for a particular customer, wasn't intended to be permanent
 *  </pre>
 *  @TODO Decide if the selectUser method needs renamed to something else to indicate
 *        it is selecting more than the data from the user table and create a new method
 *        that selects a single user data from the user table only. Leaning this way.
 *  @TODO Move all the model methods to the model classes.
 *  @TODO Determine how the selectUser (or selectSingleUser - readSingle?) method works for use in verifiers
**/
namespace Ritc\Library\Core;

use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\UsersModel;

class Access extends Base
{
    protected $current_page;
    protected $db_prefix;
    protected $o_elog;
    protected $o_db;
    private   $o_groups;
    private   $o_users;
    protected $private_properties;

    public function __construct(DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->o_elog = Elog::start();
        $this->o_db = $o_db;
        $this->o_users  = new UsersModel($o_db);
        $this->o_groups = new GroupsModel($o_db);
        $this->o_roles  = new RolesModel($o_db);
        $this->db_prefix = $o_db->getDbPrefix();
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
     *          'username'=>'something',
     *          'password'=>'something',
     *          'tolken'=>'something',
     *          'form_ts'=>'000000',
     *          'hobbit'=>'' <-- hobbit should always be blank for valid submission (optional element)
     *      ).
     *
     *  @return mixed, (int) user_id or (bool) false
    **/
    public function login($a_login = '')
    {
        if ($a_login == '') { return false; }
        $a_required = array('username', 'password', 'tolken', 'form_ts');
        foreach ($a_required as $required) {
            if (isset($a_login[$required]) === false) {
                return false;
            }
        }
        $a_user_values = $this->o_users->readInfo($a_login['username']);
        $this->o_elog->write("Posted Values: " . var_export($a_login, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->o_elog->write("User Values: " . var_export($a_user_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($a_user_values !== false && $a_user_values !== null) {
            if ($a_user_values['is_active'] < 1) {
                $this->o_users->incrementBadLoginTimestamp($a_user_values['user_id']);
                $this->o_users->incrementBadLoginCount($a_user_values['user_id']);
                $a_login['password'] = '';
                $a_login['is_active'] = false;
                return $a_login;
            }
            if ($a_user_values['bad_login_count'] > 5 && $a_user_values['bad_login_ts'] >= (time() - (60*5))) {
                $this->o_users->incrementBadLoginTimestamp($a_user_values['user_id']);
                $a_login['password'] = '';
                $a_login['locked'] = 'yes';
                return $a_login;
            }
            // simple anti-spambot thing... if the form has it.
            if (isset($a_login['hobbit']) && $a_login['hobbit'] != '') {
                $this->o_users->setBadLoginTimestamp($a_user_values['user_id']);
                $this->o_users->incrementBadLoginCount($a_user_values['user_id']);
                return false;
            }
            $a_login['created_on'] = $a_user_values['created_on'];
            $a_login['user_id'] = $a_user_values['user_id'];
            $hashed_password = password_hash($a_login['password'],PASSWORD_DEFAULT);
            if ($a_user_values['password'] == $hashed_password) {
                $this->o_users->resetBadLoginCount($a_user_values['user_id']);
                $this->o_users->resetBadLoginTimestamp($a_user_values['user_id']);
                $a_user_values['tolken'] = $a_login['tolken'];
                $a_user_values['form_ts'] = $a_login['form_ts'];
                $a_user_values['locked'] = 'no';
                unset($a_user_values['password']);
                unset($a_user_values['bad_login_count']);
                unset($a_user_values['bad_login_ts']);
                $this->o_elog->write("After password check: " . var_export($a_user_values, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
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
        $user_id = (int) $user_id;
        $results = $this->o_users->readInfo($user_id);
        if ($results !== false) {
            return true;
        }
        return false;
    }
    /**
     *  Checks to see if the username exists.
     *  @param string $username
     *  @return bool true false
    **/
    public function usernameExists($username = '')
    {
        if ($username == '') { return false; }
        $results = $this->o_users->readInfo($username);
        if ($results !== false) {
            return true;
        }
        return false;
    }
    /**
     *  Checks to see if the password provided is valid for user.
     *  @param array $a_user required $a_user['password'] and either $a_user['user_id'] or $a_user['user_name']
     *  @return bool
     */
    public function validPassword(array $a_user = array())
    {
        if (isset($a_user['user_id']) === false && isset($a_user['user_name']) === false ) {
            return false;
        }
        if ($a_user['user_id'] == '' && $a_user['user_name'] == '') {
            return false;
        }
        if (isset($a_user['password']) === false || $a_user['password'] == '') {
            return false;
        }
        $hashed_password = password_hash($a_user['password'], PASSWORD_DEFAULT);
        $a_results = $this->o_users->readInfo($a_user['user_id']);
        if (isset($a_results[0])) {
            $this_user = $a_results[0];
            if ($hashed_password == $this_user['password']) {
                return true;
            }
        }
        return false;
    }

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
