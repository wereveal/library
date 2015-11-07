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
 *  @version 5.0.0
 *  @date 2015-11-06 15:03:52
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v5.0.0 - removed roles from code                               - 11/06/2015 wer
 *      v4.4.1 - bug fix                                               - 10/06/2015 wer
 *      v4.4.0 - bunch of changes primarily in access control          - 09/26/2015 wer
 *      v4.3.2 - added getHighestRoleLevel method                      - 09/25/2015 wer
 *      v4.3.1 - added logout method                                   - 09/24/2015 wer
 *      v4.3.0 - two changes. isDefaultPerson is now isImmutablePerson - 09/03/2015 wer
 *               isRouteAllowed now checks for group to route mapping
 *               as well as role to route mapping, defaults to group.
 *      v4.2.6 - removed abstract class Base, added LogitTraits        - 09/01/2015 wer
 *      v4.2.5 - bug fixes, a change in PeopleModel->readInfo          - 08/14/2015 wer
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

use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

class AuthHelper
{
    use LogitTraits;

    private $db_prefix;
    private $o_db;
    private $o_groups;
    private $o_router;
    private $o_session;
    private $o_people;

    public function __construct(Di $o_di)
    {
        $this->o_db      = $o_di->get('db');
        $this->o_session = $o_di->get('session');
        $this->o_router  = $o_di->get('router');
        $this->o_people  = new PeopleModel($this->o_db);
        $this->o_groups  = new GroupsModel($this->o_db);
        $this->db_prefix = $this->o_db->getDbPrefix();
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_people->setElog($this->o_elog);
            $this->o_groups->setElog($this->o_elog);
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
            $a_person = $this->o_people->readInfo($a_person_post['login_id']);
            $this->logIt("Posted Values: " . var_export($a_person_post, true), LOG_OFF, $meth . __LINE__);
            $this->logIt("User Values: " . var_export($a_person, true), LOG_OFF, $meth . __LINE__);
            if ($a_person == array() || !is_array($a_person)) {
                $this->logIt(var_export($a_person_post, true), LOG_OFF, $meth . __LINE__);
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
            $a_person_post['people_id']  = $a_person['people_id'];

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
            return [
                'login_id'     => '',
                'is_logged_in' => 0,
                'message'      => 'Please try again. Your session has timed out.'
            ];
        }
    }
    /**
     *  Logs the person out and resets session.
     *  @param string|int $people_id either login_id or people_id
     *  @return array
     */
    public function logout($people_id = '')
    {
        if ($people_id != '') {
            if (!ctype_digit($people_id)) {
                $people_id = $this->o_people->getPeopleId($people_id);
            }
            $this->o_people->setLoggedOut($people_id);
        }
        $this->o_session->resetSession();
        return [
            'login_id'     => '',
            'is_logged_in' => 0,
            'message'      => 'Logged Out.'
        ];
    }
    /**
     *  Gets the highest auth level the person has.
     *  Note that this is not associated with a route or page, just the highest
     *  auth level that the person has based on groups the person is in.
     *  @param string|int $people_id and be either db field people_id or login_id
     *  @return int
     */
    public function getHighestAuthLevel($people_id = '')
    {
        if ($people_id == '') {
            return 0;
        }
        $auth_level = 0;
        $a_person = $this->o_people->readInfo($people_id);
        if (is_array($a_person) && count($a_person) > 0) {
            foreach($a_person['groups'] as $a_group) {
                $auth_level = $a_group['group_auth_level'] > $auth_level
                    ? $a_group['group_auth_level']
                    : $auth_level;
            }
        }
        return $auth_level;
    }

    #### Verifiers ####
    public function hasMinimumAuthLevel($people_id = '', $auth_level = 0)
    {
        $highest_auth_level = $this->getHighestAuthLevel($people_id);
        if ($auth_level <= $highest_auth_level) {
            return true;
        }
        return false;
    }
    /**
     * Determines if a person is allowed access to something.
     * Checks to see if the person is logged in and the route is allowed to the
     * group the person is in.
     * @return bool
     */
    public function isAllowedAccess($people_id = '', $auth_level = 0)
    {
        if ($this->isLoggedIn()
            &&
            (
                $this->hasMinimumAuthLevel($people_id, $auth_level)
                ||
                $this->isRouteAllowed($people_id)
            )
        ) {
            return true;
        }
        return false;
    }
    /**
     * Checks to see if the person is denied access to something.
     * Uses the isAllowedAccess method, subtracting 1 from denied auth level
     * to see if that level has access.
     * @param int $role_level level at which access is denied.
     * @return bool
     */
    public function isDeniedAccess($people_id = '', $auth_level = 0)
    {
        if ($this->isAllowedAccess($people_id, $auth_level + 1)) {
            return false;
        }
        return true;
    }
    /**
     *  Figures out if the person is specified as a default person.
     *  @param string|int $person can be the person id or the person name.
     *  @return bool true false
     */
    public function isImmutablePerson($person = -1)
    {
        if ($person == -1) {
            return false;
        }
        $a_person = $this->o_people->readInfo($person);
        if (isset($a_person['is_immutable'])) {
            if ($a_person['is_immutable'] == 1) {
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
        $a_person = $this->o_people->readInfo($login_id);
        if ($a_person !== false && !is_null($a_person)) {
            if ($a_person['is_logged_in'] == 1) {
                return true;
            }
        }
        return false;
    }
    /**
     * Checks to see if the person has a valid group for the route.
     * @param int|string $people_id
     * @return bool
     */
    public function isRouteAllowed($people_id = -1)
    {
        if ($people_id == -1 || $people_id == '') { return false; }
        $meth = __METHOD__ . '.';
        $a_person = $this->o_people->readInfo($people_id);
        $this->logIt('Person: ' . var_export($a_person, true), LOG_OFF, $meth . __LINE__);
        if ($a_person !== false) {
            $a_allowed_groups = $this->o_router->getAllowedGroups();
            $this->logIt(var_export($a_allowed_groups, true), LOG_OFF, $meth . __LINE__);
            foreach($a_person['groups'] as $a_group) {
                foreach ($a_allowed_groups as $a_allowed_group) {
                    if ($a_group['group_id'] == $a_allowed_group) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    /**
     *  Verifies person is in the group SuperAdmin (group_id == 1).
     *  @param int|string $people_id required
     *  @return bool - true = is a super admin, false = not a super admin
    **/
    public function isSuperAdmin($people_id = '')
    {
        if ($people_id == '') {
            return false;
        }
        $a_super_admin = $this->o_groups->readyById(1);
        $auth_level = $this->getHighestAuthLevel($people_id);
        if ($auth_level == $a_super_admin['group_auth_level']) {
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
     *  Checks to see if person exists.
     *  @param int|string $person person id or login name
     *  @return bool
    **/
    public function isValidPerson($person = '')
    {
        if ($person == '') { return false; }
        $a_people = $this->o_people->readInfo($person);
        if (isset($a_people['people_id'])) {
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
        if (ctype_digit($people_id) && $people_id != -1) {
            $results = $this->o_people->read(['people_id' => $people_id]);
            $a_person = $results[0];
            if (isset($a_person['people_id']) && $a_person['people_id'] == $people_id) {
                return true;
            }
        }
        return false;
    }
    /**
     *  Checks to see if the person is in the group.
     *  @param int|string $person
     *  @param int|string $group
     *  @return bool
     */
    public function personInGroup($person = -1, $group = '')
    {
        if ($person == -1 || $group == '') {
            return false;
        }
        $a_people = $this->o_people->readInfo($person);
        if (isset($a_people['groups']) && count($a_people['groups']) > 0) {
            foreach ($a_people['groups'] as $a_group) {
                if ($a_group['group_id'] == $group || $a_group['group_name'] == $group) {
                    return true;
                }
            }
        }
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
