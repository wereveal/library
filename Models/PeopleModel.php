<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/PeopleModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.4.0
 * @date      2017-12-05 11:24:02
 * @note <b>Change Log</b>
 * - v1.4.0    - moved some methods from PeopleComplex to here               - 2017-12-05 wer
 *               bug fixes too.
 * - v1.3.3    - DbUtilityTraits change reflected here                       - 2017-05-09 wer
 * - v1.3.2    - Bug fix                                                     - 2017-02-07 wer
 * - v1.3.1    - Bug fix caused by change elsewhere                          - 2017-01-27 wer
 * - v1.3.0    - Moved the multi-table queries to own class                  - 2016-12-08 wer
 * - v1.2.2    - Bug fix                                                     - 2016-08-29 wer
 * - v1.2.1    - Bug Fix, seriously, how did that get past testing?          - 2016-03-19 wer
 * - v1.2.0    - Refactoring of DbModel reflected here                       - 2016-03-18 wer
 * - v1.1.0    - refactoring to make compatible with postgresql              - 11/22/2015 wer
 * - v1.0.0    - initial working version                                     - 11/12/2015 wer
 * - v1.0.0β13 - removed roles from code                                     - 11/06/2015 wer
 * - v1.0.0β12 - Bug fix in sql, incompatible with postgresql                - 11/05/2015 wer
 * - v1.0.0β11 - Added missing method isId - causing bug elsewhere           - 09/25/2015 wer
 * - v1.0.0β10 - Added db error message retrieval                            - 09/23/2015 wer
 * - v1.0.0β9  - Added 'description' to database and added it here           - 09/22/2015 wer
 * - v1.0.0β8  - more changes to the readInfo method                         - 09/03/2015 wer
 * - v1.0.0β7  - had to rewrite the sql for the readInfo method              - 08/04/2015 wer
 * - v1.0.0β6  - refactoring elsewhere caused changes here                   - 07/31/2015 wer
 * - v1.0.0β5  - refactoring method name to reflect what is happening better - 01/06/2015 wer
 * - v1.0.0β4  - reverted to injecting DbModel                               - 11/17/2014 wer
 * - v1.0.0β3  - changed to use DI/IOC                                       - 11/15/2014 wer
 * - v1.0.0β2  - extends the Base class, injects the DbModel, clean up       - 09/23/2014 wer
 * - v1.0.0β1  - First Live version                                          - 09/15/2014 wer
 * - v0.1.0β1  - Initial version                                             - 09/11/2014 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class PeopleModel.
 * @class   PeopleModel
 * @package Ritc\Library\Models
 */
class PeopleModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * PeopleModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'people');
    }

    ### Basic CRUD commands, required by interface, deals only with the {$this->lib_prefix}people table ###

    /**
     * Creates new people record(s) in the people table.
     * @param array $a_values                      required Can be a simple assoc array or array of assoc arrays
     *                                             e.g. ['login_id' => 'fred', 'real_name' => 'Fred', 'password' => 'letmein']
     *                                             or
     *                                             [
     *                                             ['login_id' => 'fred',   'real_name' => 'Fred',   'password' => 'letmein'],
     *                                             ['login_id' => 'barney', 'real_name' => 'Barney', 'password' => 'lethimin']
     *                                             ].
     *                                             Optional key=>values 'short_name',
     *                                             'description', 'is_logged_in',
     *                                             'is_active' & 'is_immutable'
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = [])
    {
        $a_required_keys = [
            'login_id',
            'real_name',
            'short_name',
            'password',
            'description',
            'is_logged_in',
            'is_active',
            'is_immutable'
        ];
        $a_psql = [
            'table_name'  => $this->db_table,
            'column_name' => $this->primary_index_name
        ];
        $a_params = [
            'a_required_keys' => $a_required_keys,
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => $a_psql
        ];
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Returns the record for
     * @param array $a_search_values
     * @param array $a_search_params
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_values,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'login_id ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Updates the people record(s).
     * Enforces immutable app rule.
     * @param array $a_values required $a_values['people_id'] || $a_values['login_id']
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values = [])
    {
        $pin = $this->primary_index_name;
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            $people_ids = [];
            foreach ($a_values as $key => $a_record) {
                $people_ids[] = $a_record[$pin];
            }
            try {
                $results = $this->readById($people_ids);
                foreach ($results as $key => $record) {
                    if ($record['is_immutable']) {
                        unset($a_values[$key]['login_id']);
                    }
                }
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to delete the record.', 445, $e);
            }
        }
        else {
            try {
                $results = $this->readById($a_values[$pin]);
                if ($results[0]['is_immutable']) {
                    unset($a_values['login_id']);
                }
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to delete the record.', 445, $e);
            }
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new ModelException($message, $code);
        }
    }

    /**
     * Deletes a {$this->db_table} record based on id.
     * @param int|array $people_id required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($people_id = -1)
    {
        $pin = $this->primary_index_name;
        if (Arrays::isArrayOfAssocArrays($people_id)) {
            $people_ids = [];
            foreach ($people_id as $key => $a_record) {
                $people_ids[] = $a_record[$pin];
            }
            try {
                $results = $this->readById($people_ids);
                if (empty($results)) {
                    throw new ModelException('Unable to determine if the record is immutable.', 445);
                }
                foreach ($results as $key => $record) {
                    if ($record['is_immutable']) {
                        throw new ModelException('Unable to delete the record: record is immutable.', 440);
                    }
                }
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to determine if the record is immutable.', 445, $e);
            }
        }
        else {
            try {
                $results = $this->readById($people_id);
                if (empty($results)) {
                    throw new ModelException('Unable to determine if the record is immutable.', 445);
                }
                if ($results[0]['is_immutable']) {
                    throw new ModelException('Unable to delete the record: record is immutable.', 440);
                }
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to determine if the record is immutable.', 445, $e);
            }
        }
        try {
            return $this->genericDelete($people_id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    ### Potentially multiple user methods ###
    /**
     * Read the people record(s) by people_id.
     * @param int|array $people_id required may be a single id or a list of ids (array).
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readById($people_id = -1)
    {
        if (is_array($people_id)) {
            $a_search_for = [];
            foreach ($people_id as $id) {
                if ($id < 1) {
                    throw new ModelException("Missing people_id value.", 220);
                }
                $a_search_for[] = ['people_id' => $people_id];
            }
        }
        elseif ($people_id < 1) {
            throw new ModelException("Missing people_id value.", 220);
        }
        else {
            $a_search_for = ['people_id' => $people_id];
        }
        try {
            $results = $this->read($a_search_for);
            return $results;
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Reads the people record(s) by login_id.
     * @param string $login_id
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readByLoginId($login_id = '')
    {
        if (is_array($login_id)) {
            $a_search_for = [];
            foreach ($login_id as $id) {
                if ($id == '') {
                    throw new ModelException('Missing login id value.', 220);
                }
                $a_search_for[] = ['login_id' => $id];
            }
        }
        elseif ($login_id == '') {
            throw new ModelException("Missing login_id value.", 220);
        }
        else {
            $a_search_for = ['login_id' => $login_id];
        }
        try {
            $results = $this->read($a_search_for);
            return $results;
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    ### Single User Methods ###

    /**
     * Gets the people_id (primary record key) for a specific login_id.
     * @param string $login_id required
     * @return bool|int $people_id
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function getPeopleId($login_id = '')
    {
        if ($login_id == '') {
            throw new ModelException('Missing required login_id.');
        }
        try {
            $a_results = $this->read(array('login_id' => $login_id));
            if (!empty($a_results[0])) {
                return $a_results[0]['people_id'];
            }
            else {
                throw new ModelException('No records were returned.', 210);
            }
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to get the records.', 200);
        }
    }

    /**
     * Updates the bad_login_count field for the user by one
     * @param int $people_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function incrementBadLoginCount($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
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
            else {
                throw new ModelException('Unable to update bad_login_count in the people record', 300);
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Increments the bad_login_ts record by one minute
     * @param int $people_id required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function incrementBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) {
            throw new ModelException('Missing required people id.', 320);
        }
        $a_values = [
            'people_id'    => $people_id,
            'bad_login_ts' => 'bad_login_ts + 60'
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
     * @param int $people_id
     * @return string
     */
    public function makeBadLoginAttempt($people_id = -1)
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
                    $message .= 'Model Exception for setLoggedOut: ' . $e->errorMessage();
                }
            }
            catch (ModelException $e) {
                $message .= 'Model exception for incrementBadLoginCount: ' . $e->errorMessage();
            }
        }
        catch (ModelException $e) {
            $message .= 'Model exception for incrementBadLoginTimestamp: ' . $e->errorMessage();
        }
        return $message;
    }

    /**
     * Does the normal things to indicate the person is logged in.
     * @param $people_id
     * @return bool
     */
    public function makeGoodLoginAttempt($people_id)
    {
        $is_good = true;
        try {
            $this->resetBadLoginCount($people_id);
        }
        catch (ModelException $e) {
            $is_good = false;
        }
        try {
            $this->resetBadLoginTimestamp($people_id);
        }
        catch (ModelException $e) {
            $is_good = false;
        }
        try {
            $this->setLoggedIn($people_id);
        }
        catch (ModelException $e) {
            $is_good = false;
        }
        return $is_good;
    }

    /**
     * Returns the user record.
     * @param int|string $user either user id or user name
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readPeopleRecord($user = '')
    {
        if ($user == '') {
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
            else {
                return [];
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Resets the bad_login_count to 0
     * @param int $people_id required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function resetBadLoginCount($people_id = -1)
    {
        if ($people_id == -1) {
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
     * Resets the timestamp to 0
     * @param int $people_id required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function resetBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) {
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
     * @param int $people_id required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function setBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) {
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
     * @param int $people_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function setLoggedIn($people_id = -1)
    {
        if ($people_id == -1) {
            throw new ModelException('Missing required value: people_id', 320);
        }
        $a_values = [
            'people_id'    => $people_id,
            'is_logged_in' => 1
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
     * @param int $people_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function setLoggedOut($people_id = -1)
    {
        if ($people_id == -1) {
            throw new ModelException('Missing required value: people_id', 320);
        }
        $a_values = [
            'people_id'    => $people_id,
            'is_logged_in' => 0
        ];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Updates the user record with a new password
     * @param int    $people_id required
     * @param string $password  required
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function updatePassword($people_id = -1, $password = '')
    {
        if ($people_id == -1 || $password == '') {
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
     * @param int $people_id required id of a user
     * @param int $is_active optional defaults to inactive (0)
     * @return bool success or failure
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function updateActive($people_id = -1, $is_active = 0)
    {
        if ($people_id == -1) {
            throw new ModelException('Missing required value.', 320);
        }
        $is_active = (int) $is_active;
        if ($is_active > 1) {
            $is_active = 1;
        }
        if ($is_active == '') {
            $is_active = 0;
        }
        $a_values = [
            'people_id' => $people_id,
            'is_active' => $is_active];
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    ### Utility methods ###

    /**
     * Creates a short name/alias if none is provided
     * @param  string $long_name
     * @return string the short name.
     */
    public function createShortName($long_name = '')
    {
        if (strpos($long_name, ' ') !== false) {
            $a_real_name = explode(' ', $long_name);
            $short_name = '';
            foreach($a_real_name as $name) {
                $short_name .= strtoupper(substr($name, 0, 1));
            }
        }
        else {
            $short_name = strtoupper(substr($long_name, 0, 8));
        }
        if ($this->isExistingShortName($short_name)) {
            $short_name = $this->createShortName(substr($short_name, 0, 6) . rand(0,99));
        }
        return $short_name;
    }

    /**
     * @param int $people_id
     * @return bool
     */
    public function isId($people_id = -1)
    {
        if (ctype_digit($people_id) && $people_id != -1) {
            $a_where_values = ['people_id' => $people_id];
            try {
                $results = $this->read($a_where_values);
                if (isset($results[0]['people_id']) && $results[0]['people_id'] == $people_id) {
                    return true;
                }
                return false;
            }
            catch (ModelException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @param string $login_id
     * @return bool
     */
    public function isExistingLoginId($login_id = '')
    {
        try {
            $results = $this->readByLoginId($login_id);
            if (!empty($results[0]['people_id'])) {
                return true;
            }
            return false;
        }
        catch (ModelException $e) {
            return false;
        }
    }

    /**
     * @param string $short_name
     * @return bool
     */
    public function isExistingShortName($short_name = '')
    {
        try {
            $a_results = $this->read(['short_name' => $short_name]);
            if (isset($a_results[0]['short_name']) && $a_results[0]['short_name'] == $short_name) {
                return true;
            }
            return false;
        }
        catch (ModelException $e) {
            return false;
        }
    }

    /**
     * Returns an array to be used to create or update a people record.
     * Values in are normally from a POSTed form.
     * @param array $a_person
     * @return array|string
     */
    public function setPersonValues(array $a_person = array())
    {
        $meth = __METHOD__ . '.';
        $new_person = empty($a_person['people_id'])
            ? true
            : false;
        $a_person['password'] = $this->hashPass($a_person['password']);
        $a_fix_these = ['login_id', 'real_name', 'short_name', 'description'];
        foreach ($a_fix_these as $key) {
            if (isset($a_person[$key])) {
                $a_person[$key] = Strings::removeTagsWithDecode($a_person[$key], ENT_QUOTES);
                if ($key == 'short_name') {
                    $a_person[$key] = Strings::makeAlphanumeric($a_person[$key]);
                }
                if ($key == 'login_id') {
                    $a_person['login_id'] = Strings::makeAlphanumericPlus($a_person['login_id']);
                }
            }
        }
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
            if ($a_person['real_name'] == '') {
                $a_person['real_name'] = $a_person['login_id'];
            }
            if ($a_person['short_name'] == '') {
                $a_person['short_name'] = $this->createShortName($a_person['real_name']);
            }
            else {
                $a_person['short_name'] = $this->createShortName($a_person['short_name']);
            }
            $a_person['description'] = empty($a_person['description'])
                ? $a_person['real_name']
                : $a_person['description'];
            $a_person['is_logged_in'] = 0;
            $a_person['is_active'] = isset($a_person['is_active']) && $a_person['is_active'] == 'true'
                ? 1
                : 0;
            $a_person['is_immutable'] = isset($a_person['is_immutable']) && $a_person['is_immutable'] == 'true'
                ? 1
                : 0;
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
            catch (ModelException $e) {
                return 'people_id-invalid';
            }
              $log_message = 'New Person ' . var_export($a_person, TRUE);
              $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
              $log_message = 'Old Person ' . var_export($a_old_person, TRUE);
              $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
            foreach ($a_allowed_keys as $key) {
                if ($key !== 'people_id') {
                    if (isset($a_person[$key])) {
                        $old_value = $a_old_person[$key];
                        $new_value = $a_person[$key];
                        if ($key === 'is_logged_in' || $key === 'is_active' || $key === 'is_immutable') {
                            $new_value = $new_value === 'true' ? 1 : 0;
                            $a_person[$key] = $new_value;
                        }
                        if ($key === 'login_id' && ($new_value !== $old_value)) {
                            if ($this->isExistingLoginId($a_person['login_id'])) {
                                return 'login-exists';
                            }
                        }
                        if ($key === 'short_name' && ($new_value !== $old_value)) {
                            if ($this->isExistingShortName($a_person['short_name'])) {
                                return 'short_name-exists';
                            }
                        }
                        if ($new_value === $old_value) {
                            unset($a_person[$key]);
                        }
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
     * Hashes the password if it isn't already hashed.
     * Also verifies that it isn't a starred out password (value hidden).
     * @param string $password
     * @return bool|string
     */
    public function hashPass($password = '')
    {
        if (empty($password)) {
            return '';
        }
        if (substr($password, 0, 3) == '***') {
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
