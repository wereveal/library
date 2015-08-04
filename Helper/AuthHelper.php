<?php
/**
 *  @brief Manages User Authentication and Authorization to the site.
 *  @details It is expected that this will be used within a controller and
 *  more finely grained access with be handled there or in a sub-controller.
 *  @file AuthHelper.php
 *  @ingroup ritc_library helper
 *  @namespace Ritc/Library/Helper
 *  @class AuthHelper
 *  @author William E Reveal  <bill@revealitconsulting.com>
 *  @version 4.2.4
 *  @date 2015-08-04 11:52:02
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v4.2.4 - more references to user to person changes             - 08/04/2015 wer
 *      v4.2.3 - refactored references to user into person             - 01/26/2015 wer
 *      v4.2.2 - modified to work with user model changes              - 01/22/2015 wer
 *      v4.2.1 - bug fixes                                             - 01/16/2015 wer
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
**/
namespace Ritc\Library\Helper;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Services\Di;

class AuthHelper extends Base
{
    private $db_prefix;
    private $o_db;
    private $o_groups;
    private $o_roles;
    private $o_session;
    private $o_people;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_db      = $o_di->get('db');
        $this->o_session = $o_di->get('session');
        $this->o_people  = new PeopleModel($this->o_db);
        $this->o_groups  = new GroupsModel($this->o_db);
        $this->o_roles   = new RolesModel($this->o_db);
        $this->db_prefix = $this->o_db->getDbPrefix();
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_people->setElog($this->o_elog);
            $this->o_groups->setElog($this->o_elog);
            $this->o_roles->setElog($this->o_elog);
        }
    }

    #### Actions ####
    /**
     *  Does the actual login, verifies valid person.
     *
     *  @pre a form has been submitted from the site with all the needed
     *      variables which have been put through a data cleaner.
     *
     *  @param array $a_person_post required with the following keys
     *      array(
     *          'login_id'=>'something',
     *          'password'=>'something',
     *          'tolken'=>'something',
     *          'form_ts'=>'000000',
     *          'hobbit'=>'' <-- hobbit should always be blank for valid submission (optional element)
     *      ).
     *
     *  @return array person_values or login values with message.
    **/
    public function login(array $a_person_post = array())
    {
        $meth = __METHOD__ . '.';
        if ($a_person_post == array()) {
            return [
                'login_id'     => '',
                'is_logged_in' => 0,
                'message'      => 'Please try again. No information provided.'
            ];
        }
        $a_required = ['login_id', 'password', 'tolken', 'form_ts'];
        foreach ($a_required as $required) {
            if (isset($a_person_post[$required]) === false) {
                return [
                    'login_id' => isset($a_person_post['login_id'])
                        ? $a_person_post['login_id']
                        : '',
                    'is_logged_in' => 0,
                    'message' => 'Please try again. Missing info.'
                ];
            }
        }
        if ($this->o_session->isValidSession($a_person_post, true)) {
            $a_people_records = $this->o_people->readInfo($a_person_post['login_id']);
            $this->logIt("Posted Values: " . var_export($a_person_post, true), LOG_OFF, $meth . __LINE__);
            $this->logIt("User Values: " . var_export($a_people_records, true), LOG_OFF, $meth . __LINE__);
            if ($a_people_records !== false && !is_null($a_people_records) && isset($a_people_records[0])) {
                $a_person = $a_people_records[0]; // the first record should have the highest access level.
            }
            else {
                $this->logIt(var_export($a_person_post, true), LOG_OFF, $meth . __LINE__);
                $this->o_session->resetSession();
                return [
                    'login_id'     => '',
                    'is_logged_in' => 0,
                    'message'      => 'Please try again. The login id was not found.'
                ];
            }
            if ($a_person['is_active'] < 1) {
                $this->o_people->incrementBadLoginTimestamp($a_person['people_id']);
                $this->o_people->incrementBadLoginCount($a_person['people_id']);
                $this->o_people->setLoggedOut($a_person['people_id']);
                $a_person_post['password']     = '';
                $a_person_post['is_logged_in'] = 0;
                $a_person_post['message']      = 'The login id is inactive.';
                return $a_person_post;
            }
            if ($a_person['bad_login_count'] > 5 && $a_person['bad_login_ts'] >= (time() - (60 * 5))) {
                $this->o_people->incrementBadLoginTimestamp($a_person['people_id']);
                $this->o_people->setLoggedOut($a_person['people_id']);
                $a_person_post['password']     = '';
                $a_person_post['is_logged_in'] = 0;
                $a_person_post['login_id']     = '';
                $a_person_post['message']      = 'The login id is locked out. Please wait 5 minutes and try again.';
                return $a_person_post;
            }
            // simple anti-spambot thing... if the form has it.
            if (isset($a_person_post['hobbit']) && $a_person_post['hobbit'] != '') {
                $this->o_people->setLoggedOut($a_person['people_id']);
                $this->o_people->setBadLoginTimestamp($a_person['people_id']);
                $this->o_people->incrementBadLoginCount($a_person['people_id']);
                return [
                    'login_id'     => '',
                    'is_logged_in' => 0,
                    'message'      => 'A problem has occured. Please try again.'
                ];
            }
            $a_person_post['created_on'] = $a_person['created_on'];
            $a_person_post['people_id']    = $a_person['people_id'];

            $this->logIt("Password Needed: " . $a_person['password'], LOG_OFF, $meth . __LINE__);
            $this->logIt("Password Given (hashed): " . password_hash($a_person_post['password'], PASSWORD_DEFAULT), LOG_OFF, $meth . __LINE__);
            if (password_verify($a_person_post['password'], $a_person['password'])) {
                $this->o_people->resetBadLoginCount($a_person['people_id']);
                $this->o_people->resetBadLoginTimestamp($a_person['people_id']);
                $this->o_people->setLoggedIn($a_person['people_id']);
                $a_person['is_logged_in']    = 1;
                $a_person['message']         = 'Success!';
                $a_person['password']        = '';
                $a_person['bad_login_count'] = 0;
                $a_person['bad_login_ts']    = 0;
                $this->logIt("After password check: " . var_export($a_person, true), LOG_OFF, $meth . __LINE__);
                return $a_person;
            } else {
                $this->o_people->setBadLoginTimestamp($a_person['people_id']);
                $this->o_people->incrementBadLoginCount($a_person['people_id']);
                $this->o_people->setLoggedOut($a_person['people_id']);
                return [
                    'login_id'     => $a_person_post['login_id'],
                    'is_logged_in' => 0,
                    'password'     => '',
                    'message'      => 'The password was incorrect. Please try again.'
                ];
            }
        }
        else {
            $this->logIt(var_export($a_person_post, true), LOG_OFF, $meth . __LINE__);
            $this->o_session->resetSession();
            return [
                'login_id'     => '',
                'is_logged_in' => 0,
                'message'      => 'Please try again. Your session has timed out.'
            ];
        }
    }

    #### Verifiers ####
    /**
     * Figure out if the person has a role level at or higher than param.
     * @param int $people_id the id of the person being checked
     * @param int $role_level (has a fallback so could be a role name
     * @return bool
     */
    public function hasMinimumRoleLevel($people_id, $role_level = 9999)
    {
        $a_people_records = $this->o_people->readInfo($people_id);
        $this->logIt("User Values: " . var_export($a_people_records, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        if ($a_people_records !== false && !is_null($a_people_records) && isset($a_people_records[0])) {
            $a_person = $a_people_records[0]; // the first record should have the highest access level.
            if (is_numeric($role_level)) {
                if ($a_person['role_level'] <= $role_level) {
                    return true;
                }
            }
            else {
                $a_roles_results = $this->o_roles->read(['role_name' => $role_level]);
                if ($a_person['role_level'] <= $a_roles_results[0]['role_level']) {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     *  Figures out if the person is specified as a default person.
     *  @param string|int $person can be the person id or the person name.
     *  @return bool true false
     */
    public function isDefaultPerson($person = -1)
    {
        if ($person == -1) {
            return false;
        }
        $a_results = $this->o_people->readInfo($person);
        if (isset($a_results[0]['is_default'])) {
            if ($a_results[0]['is_default'] == 1) {
                return true;
            }
        }
        return false;
    }
    /**
     *  Verifies a person is logged in and session is valid for person.
     *  @return bool
     */
    public function isLoggedIn()
    {
        if ($this->o_session->isNotValidSession()) {
            return false;
        }
        $login_id = $this->o_session->getVar('login_id');
        if ($login_id == '') {
            return false;
        }
        $a_people = $this->o_people->readInfo($login_id);
        if (isset($a_people[0])) {
            if ($a_people[0]['is_logged_in'] == 1) {
                return true;
            }
        }
        return false;
    }
    /**
     *  Verifies person has the role of super administrator.
     *  @param int $people_id required
     *  @return bool - true = is a super admin, false = not a super admin
     * @TODO this may not work as intended due to changes to roles/groups etc.
    **/
    public function isSuperAdmin($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $a_people = $this->o_people->readInfo($people_id);
        if (!isset($a_people[0])) { return false; }
        if ($a_people[0]['access_level'] === 1) {
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
     *  Checks to see if person exists.
     *  @param int|string $person person id or login name
     *  @return bool
    **/
    public function isValidPerson($person = '')
    {
        if ($person == '') { return false; }
        $a_people = $this->o_people->readInfo($person);
        if (isset($a_people[0])) {
            return true;
        }
        return false;
    }
    /**
     *  Checks to see if the person by id exists.
     *  Uses the isValidPerson method.
     *  @param int $people_id required
     *  @return bool
     */
    public function isValidPeopleId($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        if (ctype_digit($people_id)) {
            return $this->isValidPerson($people_id);
        }
        return false;
    }
    /**
     *  Checks to see if the person id exists.
     *  @param int $people_id
     *  @return bool true false
     **/
    public function peopleIdExists($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        if ($this->o_people->isID($people_id)) {
            $results = $this->o_people->read(array('people_id' => $people_id));
            if (isset($results['people_id']) && $results['people_id'] == $people_id) {
                return true;
            }
        }
        return false;
    }
    /**
     * Checks to see if the person is in the group.
     * @param int|string $person
     * @return bool
     */
    public function personInGroup($person = -1)
    {
        if ($person == -1) { return false; }

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
        $results = $this->o_people->read(array('login_id' => $login_id));
        if (isset($results['login_id']) && $results['login_id'] == $login_id) {
            return true;
        }
        return false;
    }
    /**
     *  Checks to see if the password provided is valid for person.
     *  @param array $a_person required $a_person['password'] and either $a_person['people_id'] or $a_person['login_id']
     *  @return bool
     */
    public function validPassword(array $a_person = array())
    {
        if (isset($a_person['people_id']) === false && isset($a_person['login_id']) === false ) {
            return false;
        }
        if ($a_person['people_id'] == '' && $a_person['login_id'] == '') {
            return false;
        }
        if (isset($a_person['password']) === false || $a_person['password'] == '') {
            return false;
        }
        if (isset($a_person['people_id']) && $a_person['people_id'] != '') {
            $a_find_this = ['people_id' => $a_person['people_id']];
        }
        else {
            $a_find_this = ['login_id' => $a_person['login_id']];
        }
        $a_results = $this->o_people->read($a_find_this);
        if (isset($a_results[0])) {
            return password_verify($a_person['password'], $a_results[0]['password']);
        }
        return false;
    }
}
