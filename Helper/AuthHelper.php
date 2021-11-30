<?php
/**
 * Class AuthHelper
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use Ritc\Library\Exceptions\CustomException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\PeopleComplexModel;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;
use Ritc\Library\Traits\LogitTraits;

/**
 * Manages User Authentication and Authorization to the site.
 * It is expected that this will be used within a controller and
 * more finely grained access with be handled there or in a sub-controller.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v6.0.0
 * @date    2021-11-29 15:07:17
 * @change_log
 * - v6.0.0 - updated for php8, fixed a potential bug               - 2021-11-29 wer
 * - v5.3.0 - Refactored due to refactoring of models               - 2017-06-20 wer
 * - v5.2.0 - Adding DbUtilityTraits only pointed out problems.     - 2016-03-19 wer
 *            - Removed DbUtilityTraits
 *            - fixed the problems.
 * - v5.1.0 - Part of a refactoring, added DbUtilityTraits          - 2016-03-18 wer
 * - v5.0.0 - removed roles from code                               - 11/06/2015 wer
 * - v4.4.0 - bunch of changes primarily in access control          - 09/26/2015 wer
 * - v4.3.2 - added getHighestRoleLevel method                      - 09/25/2015 wer
 * - v4.3.1 - added logout method                                   - 09/24/2015 wer
 * - v4.3.0 - two changes. isDefaultPerson is now isImmutablePerson - 09/03/2015 wer
 *            - isRouteAllowed now checks for group to route mapping
 *            - role to route mapping, defaults to group.
 * - v4.2.6 - removed abstract class Base, added LogitTraits        - 09/01/2015 wer
 * - v4.2.5 - bug fixes, a change in PeopleModel->readInfo          - 08/14/2015 wer
 * - v4.2.4 - more references to user to person changes             - 08/04/2015 wer
 * - v4.2.3 - refactored references to user into person             - 01/26/2015 wer
 * - v4.2.2 - modified to work with user model changes              - 01/22/2015 wer
 * - v4.2.0 - change the name of the file.                          - 01/14/2015 wer
 *            - It wasn't doing access
 *            - It was doing authorization and authentication.
 * - v4.1.1 - changed the login method to return an array always    - 01/14/2015 wer
 * - v4.1.0 - moved to the Helper namespace, changed name           - 12/09/2014 wer
 * - v4.0.5 - removed remaining db code, fixed bugs                 - 12/09/2014 wer
 * - v4.0.4 - switched to using IOC/DI                              - 11/17/2014 wer
 * - v4.0.3 - moved to the Services namespace                       - 11/15/2014 wer
 * - v4.0.2 - part of the refactoring of the user model             - 11/11/2014 wer
 * - v4.0.1 - updated to implement the changes to the Base class    - 09/23/2014 wer
 * - v4.0.0 - Changed to use the user/group/role model classes      - 09/12/2014 wer
 * - v3.6.1 - Changed to use DbModel defined table prefix,          - 02/24/2014 wer
 *            added anti-spambot code to login
 * - v3.6.0 - Database changes, added new user role connector table - 11/12/2013 wer
 *            New and revised methods to match database changes.
 *            General Clean up of the code.
 * - v3.5.5 - refactor for change in the database class             - 2013-11-06 wer
 * - v3.5.4 - changed namespace and library reorg                   - 07/30/2013 wer
 * - v3.5.3 - changed namespace to match my framework namespace,    - 04/22/2013 wer
 *            refactored to match Elog method name change
 * - v3.5.2 - database methods were renamed, changed to match
 * - v3.5.1 - changed namespace to match Symfony structure
 * - v3.5.0 - new methods to handle user groups, lots of minor changes
 * - v3.4.5 - modified the code to be closer to FIG standards and removed controller code (this is Model)
 * - v3.4.0 - added short_name to Access, changing Real Name back to a real name
 * - v3.3.0 - Refactored to extend the Base class
 * - v3.2.0 - changed real name field to being just short_name, a temporary fix for a particular customer, wasn't intended to be permanent
 */
class AuthHelper
{
    use LogitTraits;

    /** @var PeopleComplexModel $o_complex */
    private PeopleComplexModel $o_complex;
    /** @var GroupsModel $o_groups */
    private GroupsModel $o_groups;
    /** @var Router $o_router */
    private Router $o_router;
    /** @var Session $o_session */
    private Session $o_session;
    /** @var PeopleModel $o_people */
    private PeopleModel $o_people;

    /**
     * AuthHelper constructor.
     *
     * @param Di $o_di
     * @noinspection PhpFieldAssignmentTypeMismatchInspection*/
    public function __construct(Di $o_di)
    {
        /** @var DbModel $o_db */
        $o_db            = $o_di->get('db');
        $this->o_session = $o_di->get('session');
        $this->o_router  = $o_di->get('router');
        $this->o_people  = new PeopleModel($o_db);
        $this->o_groups  = new GroupsModel($o_db);
        try {
            $this->o_complex = new PeopleComplexModel($o_di);
        }
        catch (CustomException $e) {
            $this->logIt($e->getMessage(), LOG_ALWAYS, __METHOD__);
        }
        $this->a_object_names = ['o_people', 'o_groups'];
        $this->setupElog($o_di);
    }

    #### Actions ####
    /**
     * Does the actual login, verifies valid person.
     *
     * @pre a form has been submitted from the site with all the needed
     * - variables which have been put through a data cleaner.
     *
     * @param array $a_person_post required with the following keys:
     * <pre>
     *     array(
     *         'login_id'=>'something',
     *         'password'=>'something',
     *         'tolken'=>'something',
     *         'form_ts'=>'000000',
     *         'hobbit'=>'' <-- hobbit should always be blank for valid submission (optional element)
     *     ).
     * </pre>
     * @return array person_values or login values with message.
     */
    public function login(array $a_person_post = []):array
    {
        if ($a_person_post === []) {
            return [
                'login_id'     => '',
                'is_logged_in' => 'false',
                'message'      => 'Please try again. No information provided.'
            ];
        }
        $a_required = ['login_id', 'password', 'tolken', 'form_ts'];
        if (!Arrays::hasRequiredKeys($a_person_post, $a_required)) {
            $a_missing_keys = Arrays::findMissingKeys($a_person_post, $a_required);
            $missing_info = '';
            foreach ($a_missing_keys as $key) {
                $missing_info .= match ($key) {
                    'login_id' => $missing_info === '' ? 'Login ID' : ', Login ID',
                    'password' => $missing_info === '' ? 'Password' : ', Password',
                    default    => $missing_info === '' ? 'Unknown Problem' : '',
                };
            }
            return [
                'login_id' => $a_person_post['login_id'] ?? '',
                'is_logged_in' => 'false',
                'message' => 'Please try again. Missing info: ' . $missing_info
            ];

        }
        if ($this->o_session->isValidSession($a_person_post, true)) {
            try {
                $a_person = $this->o_people->readPeopleRecord($a_person_post['login_id']);
            }
            catch (ModelException) {
                $a_person = [];
            }
            if (empty($a_person)) {
                return [
                    'login_id'     => '',
                    'is_logged_in' => 'false',
                    'message'      => 'Please try again: invalid values.'
                ];
            }
            if ($a_person['is_active'] !== 'true') {
                $message = $this->o_people->makeBadLoginAttempt($a_person['people_id']);
                $a_person_post['password']     = '';
                $a_person_post['is_logged_in'] = 'false';
                $a_person_post['message']      = $message === ''
                    ? 'The login id is inactive.'
                    : 'The login id is inactive. ' . $message;
                return $a_person_post;
            }
            if ($a_person['bad_login_count'] > 5 && ($a_person['bad_login_ts'] >= (time() - (60 * 5)))) {
                $message = $this->o_people->makeBadLoginAttempt($a_person['people_id']);
                $a_person_post['password']     = '';
                $a_person_post['is_logged_in'] = 'false';
                $a_person_post['login_id']     = '';
                $a_person_post['message']      = $message === ''
                    ? 'The login id is locked out. Please wait 5 minutes and try again.'
                    : 'The login id is locked out. Please wait 5 minutes and try again. ' . $message;
                return $a_person_post;
            }
            // simple anti-spambot thing... if the form has it.
            if (isset($a_person_post['hobbit']) && $a_person_post['hobbit'] !== '') {
                $message = $this->o_people->makeBadLoginAttempt($a_person['people_id']);
                return [
                    'login_id'     => '',
                    'is_logged_in' => 'false',
                    'message'      => 'A problem has occured. Please try again. ' . $message
                ];
            }
            $a_person_post['created_on'] = $a_person['created_on'];
            $a_person_post['people_id']  = $a_person['people_id'];

            if (password_verify($a_person_post['password'], $a_person['password'])) {
                $this->o_people->makeGoodLoginAttempt($a_person['people_id']);
                $a_person['is_logged_in']    = 'true';
                $a_person['message']         = 'Success!';
                $a_person['password']        = '';
                $a_person['bad_login_count'] = 0;
                $a_person['bad_login_ts']    = 0;
                $a_person['auth_level']      = $this->getHighestAuthLevel($a_person['people_id']);
                return $a_person;
            }

            $message = $this->o_people->makeBadLoginAttempt($a_person['people_id']);
            return [
                'login_id'     => $a_person_post['login_id'],
                'is_logged_in' => 'false',
                'password'     => '',
                'message'      => 'The password was incorrect. Please try again. ' . $message
            ];
        }

        $message = 'Please try again.';
        try {
            $a_person = $this->o_people->readPeopleRecord($a_person_post['login_id']);
            if ($a_person['is_logged_in'] === 'true') {
                try {
                    $this->o_people->setLoggedOut($a_person['people_id']);
                }
                catch (ModelException $e) {
                    $message .= ' ' . $e->errorMessage();
                }
            }
        }
        catch (ModelException $e) {
            $message .= ' ' . $e->errorMessage();
        }
        $this->o_session->resetSession();
        return [
            'login_id'     => '',
            'is_logged_in' => 'false',
            'message'      => $message
        ];
    }

    /**
     * Logs the person out and resets session.
     *
     * @param int|string $people_id either login_id or people_id
     * @return array
     */
    public function logout(int|string $people_id = ''):array
    {
        if ($people_id !== '') {
            if (!ctype_digit($people_id)) {
                try {
                    $people_id = $this->o_people->getPeopleId($people_id);
                }
                catch (ModelException $e) {
                    $this->logIt('ModelException: ' . $e->errorMessage(), LOG_OFF, __METHOD__);
                }
            }
            try {
                $this->o_people->setLoggedOut($people_id);
            }
            catch (ModelException $e) {
                $this->logIt('ModelException: ' . $e->errorMessage(), LOG_OFF, __METHOD__);
            }
        }
        $this->o_session->resetSession();
        return [
            'login_id'     => '',
            'is_logged_in' => 'false',
            'message'      => 'Logged Out.'
        ];
    }

    /**
     * Gets the highest auth level the person has.
     * Note that this is not associated with a route or page, just the highest
     * auth level that the person has based on groups the person is in.
     *
     * @param int|string $people_id and be either db field people_id or login_id
     * @return int
     */
    public function getHighestAuthLevel(int|string $people_id = ''):int
    {
        $meth = __METHOD__ . '.';
        if ($people_id === '') {
            return 0;
        }
        $auth_level = 0;
        try {
            $a_person = $this->o_complex->readInfo($people_id);
            if (!empty($a_person)) {
                foreach($a_person['groups'] as $a_group) {
                    $auth_level = $a_group['group_auth_level'] > $auth_level
                        ? $a_group['group_auth_level']
                        : $auth_level;
                }
            }
        }
        catch (ModelException $e) {
            $this->logIt('DB Error: ' . $e->errorMessage(), LOG_OFF, $meth . __LINE__);
        }
        return $auth_level;
    }

    #### Verifiers ####
    /**
     * Verifies the person has an auth level greater than or equal to specified level.
     *
     * @param string $people_id
     * @param int    $auth_level
     * @return bool
     */
    public function hasMinimumAuthLevel(string $people_id = '', int $auth_level = 0):bool
    {
        $highest_auth_level = $this->getHighestAuthLevel($people_id);
        return $auth_level <= $highest_auth_level;
    }

    /**
     * Determines if a person is allowed access to something.
     * Checks to see if the person is logged in and the route is allowed to the
     * group the person is in.
     *
     * @param string $people_id
     * @param int    $auth_level
     * @param bool   $logged_in
     * @return bool
     */
    public function isAllowedAccess(string $people_id = '', int $auth_level = 0, bool $logged_in = false):bool
    {
        return ($logged_in || $this->isLoggedIn())
            && ($this->hasMinimumAuthLevel($people_id, $auth_level) ||
                $this->isRouteAllowed($people_id)
            );
    }

    /**
     * Checks to see if the person is denied access to something.
     * Uses the isAllowedAccess method, subtracting 1 from denied auth level
     * to see if that level has access.
     *
     * @param string $people_id
     * @param int    $auth_level
     * @return bool
     */
    public function isDeniedAccess(string $people_id = '', int $auth_level = 0):bool
    {
        if ($this->isAllowedAccess($people_id, $auth_level + 1)) {
            return false;
        }
        return true;
    }

    /**
     * Figures out if the person is specified as a default person.
     *
     * @param int|string $person can be the person id or the person name.
     * @return bool true false
     */
    public function isImmutablePerson(int|string $person = -1):bool
    {
        if ($person === -1) {
            return false;
        }
        try {
            $a_person = $this->o_complex->readInfo($person);
            if (isset($a_person['is_immutable']) && $a_person['is_immutable'] === 'true') {
                return true;
            }
        }
        catch (ModelException $e) {
            $meth = __METHOD__ . '.' . __LINE__;
            $this->logIt('Db Error: ' . $e->errorMessage(), LOG_OFF, $meth);
        }
        return false;
    }

    /**
     * Verifies a person is logged in and session is valid for person.
     *
     * @return bool
     */
    public function isLoggedIn():bool
    {
        if ($this->o_session->isNotValidSession()) {
            return false;
        }
        $login_id = $this->o_session->getVar('login_id');
        if ($login_id === '') {
            return false;
        }
        try {
            $a_person = $this->o_complex->readInfo($login_id);
            if (!empty($a_person) && $a_person['is_logged_in'] === 'true') {
                return true;
            }
        }
        catch (ModelException $e) {
            $this->logIt('Db Error: ' . $e->errorMessage(), LOG_OFF, __METHOD__);
        }
        return false;
    }

    /**
     * Checks to see if the person has a valid group for the route.
     *
     * @param int|string $people_id
     * @return bool
     */
    public function isRouteAllowed(int|string $people_id = -1):bool
    {
        if ($people_id === -1 || $people_id === '') { return false; }
        $meth = __METHOD__ . '.';
        try {
            $a_person = $this->o_complex->readInfo($people_id);
            if (!empty($a_person)) {
                $a_allowed_groups = $this->o_router->getAllowedGroups();
                foreach($a_person['groups'] as $a_group) {
                    foreach ($a_allowed_groups as $a_allowed_group) {
                        if ($a_group['group_id'] === $a_allowed_group) {
                            return true;
                        }
                    }
                }
            }
        }
        catch (ModelException $e) {
            $this->logIt('Db Exception: ' . $e->errorMessage(), LOG_OFF, $meth . __LINE__);
        }
        return false;
    }

    /**
     * Verifies person is in the group SuperAdmin (group_id === 1).
     *
     * @param int|string $people_id required
     * @return bool - true = is a super admin, false = not a super admin
     */
    public function isSuperAdmin(int|string $people_id = ''):bool
    {
        if ($people_id === '') {
            return false;
        }
        $sa_level = 10;
        try {
            $a_super_admin = $this->o_groups->readById(1);
            if (!empty($a_super_admin['group_auth_level'])) {
                $sa_level = $a_super_admin['group_auth_level'];
            }
        }
        catch (ModelException $e) {
            $this->logIt('Model Exception: ' . $e->errorMessage(), LOG_OFF, __METHOD__);
        }
        $auth_level = $this->getHighestAuthLevel($people_id);
        return $auth_level === $sa_level;
    }

    /**
     * Checks to see if the value is a valid group id or name.
     *
     * @param int|string $group
     * @return bool true or false
     */
    public function isValidGroup(int|string $group = -1):bool
    {
        if ($group === -1) { return false; }
        if (ctype_digit($group)) {
            $a_search_by = ['group_id' => $group];
        }
        else {
            $a_search_by = ['group_name' => $group];
        }
        try {
            $results = $this->o_groups->read($a_search_by);
            if (!empty($results)) {
                return true;
            }
        }
        catch (ModelException $e) {
            $this->logIt('ModelException: ' . $e->errorMessage(), LOG_OFF, __METHOD__);
        }
        return false;
    }

    /**
     * Verifies the group id provided is a valid id.
     *
     * @param int $group
     * @return bool
     */
    public function isValidGroupId(int $group = -1):bool
    {
        if ($group === -1) { return false; }
        if (ctype_digit($group)) {
            return $this->isValidGroup($group);
        }
        return false;
    }

    /**
     * Checks to see if person exists.
     *
     * @param int|string $person person id or login name
     * @return bool
     */
    public function isValidPerson(int|string $person = ''):bool
    {
        if ($person === '') { return false; }
        try {
            $a_people = $this->o_complex->readInfo($person);
            if (isset($a_people['people_id'])) {
                return true;
            }
        }
        catch (ModelException $e) {
            $this->logIt('Db Exception: ' . $e->errorMessage(), LOG_OFF, __METHOD__);
        }
        return false;
    }

    /**
     * Checks to see if the person by id exists.
     *
     * Uses the isValidPerson method.
     *
     * @param int $people_id required
     * @return bool
     */
    public function isValidPeopleId(int $people_id = -1):bool
    {
        if ($people_id === -1) { return false; }
        if (ctype_digit($people_id)) {
            return $this->isValidPerson($people_id);
        }
        return false;
    }

    /**
     * Checks to see if the person id exists.
     *
     * @param int $people_id
     * @return bool true false
     */
    public function peopleIdExists(int $people_id = -1):bool
    {
        if ($people_id !== -1 && ctype_digit($people_id)) {
            try {
                $results = $this->o_people->read(['people_id' => $people_id]);
            }
            catch (ModelException) {
                return false;
            }
            if (!empty($results[0]['people_id']) && $results[0]['people_id'] === $people_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks to see if the person is in the group.
     *
     * @param int|string $person
     * @param int|string $group
     * @return bool
     */
    public function personInGroup(int|string $person = -1, int|string $group = ''):bool
    {
        if ($person === -1 || $group === '') {
            return false;
        }
        try {
            $a_people = $this->o_complex->readInfo($person);
        }
        catch (ModelException) {
            $a_people = [];
        }
        if (!empty($a_people['groups'])) {
            foreach ($a_people['groups'] as $a_group) {
                if ($a_group['group_id'] === $group || $a_group['group_name'] === $group) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Checks to see if the login_id exists.
     *
     * @param string $login_id
     * @return bool
     */
    public function loginIdExists(string $login_id = ''):bool
    {
        if ($login_id === '') { return false; }
        try {
            $results = $this->o_people->read(array('login_id' => $login_id));
        }
        catch (ModelException) {
            $results = false;
        }
        return !empty($results['login_id']) && ($results['login_id'] === $login_id);
    }

    /**
     * Checks to see if the password provided is valid for person.
     *
     * @param array $a_person required $a_person['password'] and either $a_person['people_id'] or $a_person['login_id']
     * @return bool
     */
    public function validPassword(array $a_person = []):bool
    {
        if (isset($a_person['people_id']) === false && isset($a_person['login_id']) === false ) {
            return false;
        }
        if ($a_person['people_id'] === '' && $a_person['login_id'] === '') {
            return false;
        }
        if (isset($a_person['password']) === false || $a_person['password'] === '') {
            return false;
        }
        if (isset($a_person['people_id']) && $a_person['people_id'] !== '') {
            $a_find_this = ['people_id' => $a_person['people_id']];
        }
        else {
            $a_find_this = ['login_id' => $a_person['login_id']];
        }
        try {
            $a_results = $this->o_people->read($a_find_this);
            if (!empty($a_results[0]['password'])) {
                return password_verify($a_person['password'], $a_results[0]['password']);
            }
        }
        catch (ModelException $e) {
            $this->logIt('ModelException: ' . $e->errorMessage(), LOG_OFF, __METHOD__);
        }
        return false;
    }
}
