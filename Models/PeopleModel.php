<?php /** @noinspection PhpUndefinedConstantInspection */

/**
 * Class PeopleModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Exception;
use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Services\DbModel;

/**
 * Does all the Model expected operations, database CRUD and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.0.0
 * @date    2021-11-30 15:02:40
 * @change_log
 * - v3.0.0    - Updated to use php 8 only                      - 2021-11-30 wer
 * - v2.0.0    - Refactored to extend ModelAbstract             - 2018-06-14 wer
 * - v1.5.0    - New method, isImmutable                        - 2018-06-10 wer
 * - v1.4.0    - moved some methods from PeopleComplex to here  - 2017-12-05 wer
 *               bug fixes too.
 * - v1.3.3    - DbUtilityTraits change reflected here          - 2017-05-09 wer
 * - v1.3.0    - Moved the multi-table queries to own class     - 2016-12-08 wer
 * - v1.2.0    - Refactoring of DbModel reflected here          - 2016-03-18 wer
 * - v1.1.0    - refactoring to make compatible with postgresql - 11/22/2015 wer
 * - v1.0.0    - Finally out of beta                            - 11/12/2015 wer
 * - v1.0.0β1  - First Live version                             - 09/15/2014 wer
 * - v0.1.0β1  - Initial version                                - 09/11/2014 wer
 */
class PeopleModel extends ModelAbstract
{
    /**
     * PeopleModel constructor.
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'people');
        $this->setRequiredKeys(
            [
                'login_id',
                'real_name',
                'short_name',
                'password',
                'description',
                'is_logged_in',
                'is_active',
                'is_immutable'
            ]
        );
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###
    /**
     * Reads the people record(s) by login_id.
     *
     * @param array|int|string $login_id
     * @return array
     * @throws ModelException
     */
    public function readByLoginId(array|int|string $login_id = ''):array
    {
        if (is_array($login_id)) {
            $a_search_for = [];
            foreach ($login_id as $id) {
                if ($id === '') {
                    throw new ModelException('Missing login id value.', 220);
                }
                $a_search_for[] = ['login_id' => $id];
            }
        }
        elseif ($login_id === '') {
            throw new ModelException('Missing login_id value.', 220);
        }
        else {
            $a_search_for = ['login_id' => $login_id];
        }
        try {
            return $this->read($a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Gets the people_id (primary record key) for a specific login_id.
     *
     * @param string $login_id required
     * @return int $people_id
     * @throws ModelException
     */
    public function getPeopleId(string $login_id = ''):int
    {
        if ($login_id === '') {
            throw new ModelException('Missing required login_id.');
        }
        try {
            $a_results = $this->read(array('login_id' => $login_id));
            if (!empty($a_results[0])) {
                return $a_results[0]['people_id'];
            }
            throw new ModelException('No records were returned.', 230);
        }
        catch (ModelException) {
            throw new ModelException('Unable to get the records.', 200);
        }
    }

    /**
     * Updates the bad_login_count field for the user by one.
     *
     * @param int $people_id
     * @return bool
     * @throws ModelException
     */
    public function incrementBadLoginCount(int $people_id = -1): bool
    {
        if ($people_id === -1) { return false; }
        $sql = /** @lang text */
        "
            UPDATE {$this->db_table}
            SET bad_login_count = bad_login_count + 1
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        try {
            $results = $this->o_db->update($sql, $a_values, true);
            if ($results) {
                return true;
            }

            throw new ModelException('Unable to update bad_login_count in the people record', 300);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Increments the bad_login_ts record by one minute
     *
     * @param int $people_id required
     * @return bool
     * @throws ModelException
     */
    public function incrementBadLoginTimestamp(int $people_id = -1): bool
    {
        if ($people_id === -1) {
            throw new ModelException('Missing required people id.', 320);
        }
        $a_values = [
            'people_id'    => $people_id,
            'bad_login_ts' => time() + 60
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Does the normal things to indicate the login attempt was bad.
     *
     * @param int $people_id
     * @return string
     */
    public function makeBadLoginAttempt(int $people_id = -1):string
    {
        $message = '';
        try {
            $this->incrementBadLoginTimestamp($people_id);
            try {
                $this->incrementBadLoginCount($people_id);
                try {
                    $this->setLoggedOut($people_id);
                }
                catch (ModelException $e) {
                    if (DEVELOPER_MODE) {
                        $message .= '<br>Model Exception for setLoggedOut: ' . $e->errorMessage();
                    }
                }
            }
            catch (ModelException $e) {
                if (DEVELOPER_MODE) {
                    $message .= '<br>Model exception for incrementBadLoginCount: ' . $e->errorMessage();
                }
            }
        }
        catch (ModelException $e) {
            if (DEVELOPER_MODE) {
                $message .= '<br>Model exception for incrementBadLoginTimestamp: ' . $e->errorMessage();
            }
        }
        return $message;
    }

    /**
     * Does the normal things to indicate the person is logged in.
     *
     * @param $people_id
     * @return bool
     */
    public function makeGoodLoginAttempt($people_id):bool
    {
        $is_good = true;
        try {
            $this->resetBadLoginCount($people_id);
        }
        catch (ModelException) {
            $is_good = false;
        }
        try {
            $this->resetBadLoginTimestamp($people_id);
        }
        catch (ModelException) {
            $is_good = false;
        }
        try {
            $this->setLoggedIn($people_id);
        }
        catch (ModelException) {
            $is_good = false;
        }
        return $is_good;
    }

    /**
     * Returns the user record.
     *
     * @param int|string $user either user id or user name
     * @return array
     * @throws ModelException
     */
    public function readPeopleRecord(int|string $user = ''): array
    {
        if ($user === '') {
            throw new ModelException('Missing required value.', 220);
        }
        if (is_numeric($user)) {
            $a_search_by = ['people_id' => $user];
        }
        else {
            $a_search_by = ['login_id' => $user];
        }
        try {
            $a_records = $this->read($a_search_by);
            if (isset($a_records[0]) && is_array($a_records[0])) {
                return $a_records[0];
            }
            return [];
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Resets the bad_login_count to 0
     *
     * @param int $people_id required
     * @return bool
     * @throws ModelException
     */
    public function resetBadLoginCount(int $people_id = -1): bool
    {
        if ($people_id === -1) {
            throw new ModelException('Missing required value: people_id', 320);
        }
        $a_values = [
            'people_id'       => $people_id,
            'bad_login_count' => 0
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Resets the timestamp to 0.
     *
     * @param int $people_id required
     * @return bool
     * @throws ModelException
     */
    public function resetBadLoginTimestamp(int $people_id = -1): bool
    {
        if ($people_id === -1) {
            throw new ModelException('Missing required value: people_id', 320);
        }
        $a_values = [
            'people_id'    => $people_id,
            'bad_login_ts' => 0
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Sets the bad login timestamp for the user.
     *
     * @param int $people_id required
     * @return bool
     * @throws ModelException
     */
    public function setBadLoginTimestamp(int $people_id = -1): bool
    {
        if ($people_id === -1) {
            throw new ModelException('Missing required value: people_id', 320);
        }
        $a_values = [
            'people_id'    => $people_id,
            'bad_login_ts' => time()
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Sets the user record to be logged in.
     *
     * @param int $people_id
     * @return bool
     * @throws ModelException
     */
    public function setLoggedIn(int $people_id = -1): bool
    {
        if ($people_id === -1) {
            throw new ModelException('Missing required value: people_id', 320);
        }
        $a_values = [
            'people_id'      => $people_id,
            'is_logged_in'   => 'true',
            'last_logged_in' => date('Y-m-d')
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Sets the user record to be logged out.
     *
     * @param int $people_id
     * @return bool
     * @throws ModelException
     */
    public function setLoggedOut(int $people_id = -1): bool
    {
        if ($people_id === -1) {
            throw new ModelException('Missing required value: people_id', 320);
        }
        $a_values = [
            'people_id'    => $people_id,
            'is_logged_in' => 'false'
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Updates the user record with a new password.
     *
     * @param int    $people_id required
     * @param string $password  required
     * @return bool
     * @throws ModelException
     */
    public function updatePassword(int $people_id = -1, string $password = ''): bool
    {
        if ($people_id === -1 || $password === '') {
            throw new ModelException('Missing required value.', 320);
        }
        $a_values = [
            'people_id' => $people_id,
            'password'  => $password
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Updates the user record to be make the user active or inactive, normally inactive.
     *
     * @param int    $people_id required id of a user
     * @param string $is_active optional defaults to inactive (false)
     * @return bool success or failure
     * @throws ModelException
     */
    public function updateActive(int $people_id = -1, string $is_active = 'false'): bool
    {
        if ($people_id === -1) {
            throw new ModelException('Missing required value.', 320);
        }
        $a_values = [
            'people_id' => $people_id,
            'is_active' => $is_active
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    ### Utility methods ###

    /**
     * Creates a short name/alias if none is provided.
     *
     * @param string $long_name
     * @return string the short name.
     */
    public function createShortName(string $long_name = ''):string
    {
        if (str_contains($long_name, ' ')) {
            $a_real_name = explode(' ', $long_name);
            $short_name = '';
            foreach($a_real_name as $name) {
                $short_name .= strtoupper($name[0]);
            }
        }
        else {
            $short_name = strtoupper(substr($long_name, 0, 8));
        }
        if ($this->isExistingShortName($short_name)) {
            try {
                $rand = random_int(0, 99);
            }
            catch (Exception) {
                $rand = 1;
            }
            $short_name = $this->createShortName(substr($short_name, 0, 6) . $rand);
        }
        return $short_name;
    }

    /**
     * @param string $login_id
     * @return bool
     */
    public function isExistingLoginId(string $login_id = ''): bool
    {
        try {
            $results = $this->readByLoginId($login_id);
            if (!empty($results[0]['people_id'])) {
                return true;
            }
            return false;
        }
        catch (ModelException) {
            return false;
        }
    }

    /**
     * @param string $short_name
     * @return bool
     */
    public function isExistingShortName(string $short_name = ''): bool
    {
        try {
            $a_results = $this->read(['short_name' => $short_name]);
            return isset($a_results[0]['short_name']) && $a_results[0]['short_name'] === $short_name;
        }
        catch (ModelException) {
            return false;
        }
    }

    /**
     * Returns an array to be used to create or update a people record.
     * Values in are normally from a POSTed form.
     * @param array $a_person
     * @return array|string
     */
    public function setPersonValues(array $a_person = array()): array|string
    {
        $meth = __METHOD__ . '.';
        $new_person = empty($a_person['people_id']);
        if (substr($a_person['password'], 1) !== '*') {
            $a_person['password'] = $this->hashPass($a_person['password']);
        }
        else {
            $a_person['password'] = '';
        }
        $a_fix_these = ['login_id', 'real_name', 'short_name', 'description'];
        foreach ($a_fix_these as $key) {
            if (isset($a_person[$key])) {
                $a_person[$key] = Strings::removeTagsWithDecode($a_person[$key], ENT_QUOTES);
                if ($key === 'short_name') {
                    $a_person[$key] = Strings::makeAlphanumeric($a_person[$key]);
                }
                if ($key === 'login_id') {
                    $a_person['login_id'] = Strings::makeAlphanumericPlus($a_person['login_id']);
                }
            }
        }
        $a_person = $this->fixCheckBoxes($a_person);

        if ($new_person) {
            if (empty($a_person['password'])) {
                return 'password-missing';
            }
            if (empty($a_person['login_id'])) {
                return 'login-missing';
            }
            if ($this->isExistingLoginId($a_person['login_id'])) {
                return 'login-exists';
            }
            $a_allowed_keys   = [
                'login_id',
                'password',
                'real_name',
                'short_name',
                'description',
                'is_logged_in',
                'is_active',
                'is_immutable'
            ];
            $a_person = Arrays::createRequiredPairs($a_person, $a_allowed_keys, true);
            if ($a_person['real_name'] === '') {
                $a_person['real_name'] = $a_person['login_id'];
            }
            if ($a_person['short_name'] === '') {
                $a_person['short_name'] = $this->createShortName($a_person['real_name']);
            }
            else {
                $a_person['short_name'] = $this->createShortName($a_person['short_name']);
            }
            $a_person['description'] = empty($a_person['description'])
                ? $a_person['real_name']
                : $a_person['description'];
        }
        else {
            $a_allowed_keys   = [
                'people_id',
                'login_id',
                'real_name',
                'short_name',
                'password',
                'description',
                'is_logged_in',
                'is_active',
                'is_immutable'
            ];
            $a_person = Arrays::removeBlankPairs($a_person, $a_allowed_keys, true);
            if (!Arrays::hasRequiredKeys($a_person, ['people_id'])) {
                return 'people_id-missing';
            }
            try {
                $a_previous_values = $this->read(['people_id' => $a_person['people_id']]);
                if (empty($a_previous_values)) {
                    return 'people_id-invalid';
                }
                $a_old_person = $a_previous_values[0];
            }
            catch (ModelException) {
                return 'people_id-invalid';
            }
              $log_message = 'New Person ' . var_export($a_person, TRUE);
              $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
              $log_message = 'Old Person ' . var_export($a_old_person, TRUE);
              $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
            foreach ($a_allowed_keys as $key) {
                if ($key !== 'people_id' && isset($a_person[$key])) {
                    $old_value = $a_old_person[$key];
                    $new_value = $a_person[$key];
                    if ($key === 'login_id' &&
                        ($new_value !== $old_value) &&
                        $this->isExistingLoginId($a_person['login_id'])
                    ) {
                        return 'login-exists';
                    }
                    if ($key === 'short_name' &&
                        ($new_value !== $old_value) &&
                        $this->isExistingShortName($a_person['short_name'])
                    ) {
                        return 'short_name-exists';
                    }
                    if ($new_value === $old_value) {
                        unset($a_person[$key]);
                    }
                }
            }
            if (count($a_person) < 2) {
                return 'nothing-to-update';
            }
        }
          $log_message = 'Person modified ' . var_export($a_person, TRUE);
          $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        return $a_person;
    }

    /**
     * Sets values from form to valid values for database.
     * @param array $a_person
     * @return array
     */
    private function fixCheckBoxes(array $a_person = []):array
    {
          $this->logIt(var_export($a_person, true), LOG_OFF, __METHOD__);
        $a_person['is_logged_in'] = isset($a_person['is_logged_in']) ? 'true' : 'false';
        $a_person['is_active']    = isset($a_person['is_active'])    ? 'true' : 'false';
        $a_person['is_immutable'] = isset($a_person['is_immutable']) ? 'true' : 'false';
          $this->logIt(var_export($a_person, true), LOG_OFF, __METHOD__);
        return $a_person;
    }

    /**
     * Hashes the password if it isn't already hashed.
     * Also verifies that it isn't a starred out password (value hidden).
     *
     * @param string $password
     * @return string
     */
    public function hashPass(string $password = ''): string
    {
        if (empty($password)) {
            return '';
        }
        if (str_starts_with($password, '***')) {
            return '';
        }
        $pass_info = password_get_info($password);
        if ($pass_info['algo'] === 0) {
            if (defined('PASSWORD_ARGON2I')) {
                $password = password_hash($password, PASSWORD_ARGON2I);
            }
            else {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }
        }
        return $password;
    }
}
