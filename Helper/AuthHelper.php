<?php
/**
 *  @brief Manages User Access to the site.
 *  @details It is expected that this will be used within a controller and
 *  more finely grained access with be handled there or in a sub-controller.
 *  @file AuthHelper.php
 *  @ingroup ritc_library helper library
 *  @namespace Ritc/Library/Helper
 *  @class AuthHelper
 *  @author William E Reveal  <bill@revealitconsulting.com>
 *  @version 4.2.0
 *  @date 2015-01-14 10:03:39
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v4.2.0 - change the name of the file. It wasn't doing access   - 01/14/2015 wer
 *               it was doing authorization.
 *      v4.1.1 - changed the login method to return an array always    - 01/14/2015 wer
 *      v4.1.0 - moved to the Helper namespace, changed name           - 12/09/2014 wer
 *      v4.0.5 - removed remaining db code, fixed bugs                 - 12/09/2014 wer
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
namespace Ritc\Library\Helper;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\UsersModel;
use Ritc\Library\Services\Di;

class AuthHelper extends Base
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
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_users->setElog($this->o_elog);
            $this->o_groups->setElog($this->o_elog);
            $this->o_roles->setElog($this->o_elog);
        }
    }

    #### Actions ####
    /**
     *  Does the actual login, verifies valid user.
     *
     *  @pre a form has been submitted from the site with all the needed
     *      variables which have been put through a data cleaner.
     *
     *  @param array $a_user_post required with the following keys
     *      array(
     *          'login_id'=>'something',
     *          'password'=>'something',
     *          'tolken'=>'something',
     *          'form_ts'=>'000000',
     *          'hobbit'=>'' <-- hobbit should always be blank for valid submission (optional element)
     *      ).
     *
     *  @return array user_values or login values with message.
    **/
    public function login(array $a_user_post = array())
    {
        $meth = __METHOD__ . '.';
        if ($a_user_post == array()) { return false; }
        $a_required = array('login_id', 'password', 'tolken', 'form_ts');
        foreach ($a_required as $required) {
            if (isset($a_user_post[$required]) === false) {
                return [
                    'login_id' => isset($a_user_post['login_id'])
                        ? $a_user_post['login_id']
                        : '',
                    'is_logged_in' => 0,
                    'message' => 'Please try again.'
                ];
            }
        }
        if ($this->o_session->isValidSession($a_user_post, true)) {
            $a_user_values = $this->o_users->readInfo($a_user_post['login_id']);
            $this->logIt("Posted Values: " . var_export($a_user_post, true), LOG_OFF, $meth . __LINE__);
            $this->logIt("User Values: " . var_export($a_user_values, true), LOG_OFF, $meth . __LINE__);
            if ($a_user_values !== false && $a_user_values !== null) {
                if ($a_user_values['is_active'] < 1) {
                    $this->o_users->incrementBadLoginTimestamp($a_user_values['user_id']);
                    $this->o_users->incrementBadLoginCount($a_user_values['user_id']);
                    $this->o_users->setLoggedOut($a_user_values['user_id']);
                    $a_user_post['password']     = '';
                    $a_user_post['is_logged_in'] = 0;
                    $a_user_post['message']      = 'The login id is inactive.';
                    return $a_user_post;
                }
                if ($a_user_values['bad_login_count'] > 5 && $a_user_values['bad_login_ts'] >= (time() - (60 * 5))) {
                    $this->o_users->incrementBadLoginTimestamp($a_user_values['user_id']);
                    $this->o_users->setLoggedOut($a_user_values['user_id']);
                    $a_user_post['password']     = '';
                    $a_user_post['is_logged_in'] = 0;
                    $a_user_post['login_id']     = '';
                    $a_user_post['message']      = 'The login id is locked out. Please wait 5 minutes and try again.';
                    return $a_user_post;
                }
                // simple anti-spambot thing... if the form has it.
                if (isset($a_user_post['hobbit']) && $a_user_post['hobbit'] != '') {
                    $this->o_users->setLoggedOut($a_user_values['user_id']);
                    $this->o_users->setBadLoginTimestamp($a_user_values['user_id']);
                    $this->o_users->incrementBadLoginCount($a_user_values['user_id']);
                    return [
                        'login_id'     => '',
                        'is_logged_in' => 0,
                        'message'      => 'A problem has occured. Please try again.'
                    ];
                }
                $a_user_post['created_on'] = $a_user_values['created_on'];
                $a_user_post['user_id']    = $a_user_values['user_id'];

                $this->logIt("Password Needed: " . $a_user_values['password'], LOG_OFF, __METHOD__ . '.' . __LINE__);
                $this->logIt("Password Given (hashed): " . password_hash($a_user_post['password'], PASSWORD_DEFAULT), LOG_OFF, __METHOD__ . '.' . __LINE__);
                if (password_verify($a_user_post['password'], $a_user_values['password'])) {
                    $this->o_users->resetBadLoginCount($a_user_values['user_id']);
                    $this->o_users->resetBadLoginTimestamp($a_user_values['user_id']);
                    $this->o_users->setLoggedIn($a_user_values['user_id']);
                    $a_user_values['is_logged_in']    = 1;
                    $a_user_values['message']         = 'Success!';
                    $a_user_values['password']        = '';
                    $a_user_values['bad_login_count'] = 0;
                    $a_user_values['bad_login_ts']    = 0;
                    $this->logIt("After password check: " . var_export($a_user_values, true), LOG_OFF, $meth . __LINE__);
                    return $a_user_values;
                } else {
                    $this->o_users->setBadLoginTimestamp($a_user_values['user_id']);
                    $this->o_users->incrementBadLoginCount($a_user_values['user_id']);
                    $this->o_users->setLoggedOut($a_user_values['user_id']);
                    return [
                        'login_id'     => $a_user_post['login_id'],
                        'is_logged_in' => 0,
                        'password'     => '',
                        'message'      => 'The password was incorrect. Please try again.'
                    ];
                }
            }
        }
        return ['login_id' => '', 'is_logged_in' => 0, 'message' => 'Please try again.'];
    }

    #### Verifiers ####
    /**
     *  Figures out if the user is specified as a default user.
     *  @param string|int $user can be the user id or the user name.
     *  @return bool true false
     */
    public function isDefaultUser($user = -1)
    {
        if ($user == -1) {
            return false;
        }
        $a_results = $this->o_users->readInfo($user);
        if (isset($a_results['is_default'])) {
            if ($a_results['is_default'] == 1) {
                return true;
            }
        }
        return false;
    }
    /**
     *  Verifies a user is logged in and session is valid for user.
     *  @return bool
     */
    public function isLoggedIn()
    {
        if ($this->o_session->isNotValidSession()) {
            return false;
        }
        $login_id = $this->o_session->getVar('login_id');
        if (is_null($login_id)) {
            return false;
        }
        $a_user_info = $this->o_users->readInfo($login_id);
        if ($a_user_info !== false) {
            if ($a_user_info['is_logged_in'] == 1) {
                return true;
            }
        }
        return false;
    }
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
     *  @param int|string $user user id or login name
     *  @return bool
    **/
    public function isValidUser($user = '')
    {
        if ($user == '') { return false; }
        if (is_array($this->o_users->readInfo($user))) {
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
        if (isset($a_user['user_id']) && $a_user['user_id'] != '') {
            $a_find_this = ['user_id' => $a_user['user_id']];
        }
        else {
            $a_find_this = ['login_id' => $a_user['login_id']];
        }
        $a_results = $this->o_users->read($a_find_this);
        if (isset($a_results[0])) {
            return password_verify($a_user['password'], $a_results[0]['password']);
        }
        return false;
    }
}
