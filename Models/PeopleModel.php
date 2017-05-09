<?php
/**
 * @brief     Does all the database CRUD stuff.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/PeopleModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.3.3
 * @date      2017-05-09 17:38:21
 * @note <b>Change Log</b>
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

use Ritc\Library\Helper\Arrays;
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

    ### Basic CRUD commands, required by interface, deals only with the {$this->db_prefix}people table ###
    /**
     * Creates new people record(s) in the people table.
     * @param array $a_values required Can be a simple assoc array or array of assoc arrays
     *                        e.g. ['login_id' => 'fred', 'real_name' => 'Fred', 'password' => 'letmein']
     *                        or
     *                        [
     *                            ['login_id' => 'fred',   'real_name' => 'Fred',   'password' => 'letmein'],
     *                            ['login_id' => 'barney', 'real_name' => 'Barney', 'password' => 'lethimin']
     *                        ].
     *                        Optional key=>values 'short_name',
     *                                             'description', 'is_logged_in',
     *                                             'is_active' & 'is_immutable'
     * @return array|bool
     */
    public function create(array $a_values = [])
    {
        $meth = __METHOD__ . '.';
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
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                $a_record = Arrays::createRequiredPairs($a_record, $a_required_keys, true);
                if (Arrays::hasBlankValues($a_record, ['login_id', 'password'])) {
                    $this->error_message = "The array was missing required login_id and/or password";
                    return false;
                }
                $a_values[$key] = $a_record;
            }
        }
        else {
            $a_values = Arrays::createRequiredPairs($a_values, $a_required_keys, true);
            if (Arrays::hasBlankValues($a_values, ['login_id', 'password'])) {
                $this->error_message = "The array was missing required login_id and/or password";
                return false;
            }
        }
        $log_message = 'Values:  ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $sql = "
            INSERT INTO {$this->db_table}
                (login_id, real_name, short_name, password, description, is_logged_in, is_active, is_immutable)
            VALUES
                (:login_id, :real_name, :short_name, :password, :description, :is_logged_in, :is_active, :is_immutable)
        ";
        $log_message = "SQL: {$sql}";
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_table_info = [
            'table_name'  => "{$this->db_table}",
            'column_name' => 'people_id'
        ];
        if ($this->o_db->insert($sql, $a_values, $a_table_info)) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, $meth . __LINE__);
            return $ids;
        }
        else {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            return false;
        }
    }

    /**
     * Returns the record for
     * @param array $a_search_values
     * @param array $a_search_params
     * @return array|bool
     */
    public function read(array $a_search_values = [], array $a_search_params = [])
    {
        if (count($a_search_values) > 0) {
            $a_search_params = count($a_search_params) == 0
                ? array('order_by' => 'login_id')
                : $a_search_params;
            $a_allowed_keys = array(
                'people_id',
                'login_id',
                'real_name',
                'short_name',
                'is_immutable',
                'is_active'
            );
            $a_search_values = $this->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->buildSqlWhere([], $a_search_params);
        }
        else {
            $where = " ORDER BY login_id";
        }
        $sql = "
            SELECT people_id,
                login_id,
                real_name,
                short_name,
                password,
                description,
                is_logged_in,
                bad_login_count,
                bad_login_ts,
                is_active,
                is_immutable,
                created_on
            FROM {$this->db_table}
            {$where}
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->search($sql, $a_search_values);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
        }
        return $results;
    }

    /**
     * Updates a single {$this->db_table} record.
     * @param array $a_values required $a_values['people_id'] || $a_values['login_id']
     * @return bool
     */
    public function update(array $a_values = [])
    {
        /* the following keys in $a_values must have a value other than ''.
         * As such, they get removed from the sql
         * if they are trying to update the values to ''
         */
        $a_possible_keys = array(
            'people_id',
            'login_id',
            'password'
        );
        if (Arrays::hasBlankValues($a_values, $a_possible_keys)) {
            foreach ($a_possible_keys as $key_name) {
                if (array_key_exists($key_name, $a_values)) {
                    if ($a_values[$key_name] == '') {
                        unset($a_values[$key_name]);
                    }
                }
            }
        }
        if (!isset($a_values['people_id']) && !isset($a_values['login_id'])) {
            return false;
        }
        $a_allowed_keys = [
            'people_id',
            'login_id',
            'real_name',
            'short_name',
            'password',
            'description',
            'is_logged_in',
            'bad_login_count',
            'bad_login_ts',
            'is_active',
            'is_immutable'
        ];
        $a_values = Arrays::removeUndesiredPairs($a_values, $a_allowed_keys);
        if ($a_values['is_immutable'] == 1 && isset($a_values['login_id'])) {
            unset($a_values['login_id']);
        }
        $sql_set = $this->buildSqlSet($a_values, ['people_id']);
        if ($sql_set == '') {
            return false;
        }
        $sql = "
            UPDATE {$this->db_table}
            {$sql_set}
            WHERE people_id = :people_id
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->update($sql, $a_values, true);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
        }
        return $results;
    }

    /**
     * Deletes a {$this->db_table} record based on id.
     * @param int $people_id required
     * @return bool
     */
    public function delete($people_id = -1)
    {
        if ($people_id == -1 || !ctype_digit($people_id)) { return false; }
        $sql = "
            DELETE FROM {$this->db_table}
            WHERE people_id = :people_id
        ";
        $results = $this->o_db->delete($sql, array(':people_id' => $people_id), true);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
        }
        return $results;
    }

    ### Single User Methods ###
    /**
     * Gets the people_id (primary record key) for a specific login_id.
     * @param string $login_id required
     * @return int|bool $people_id
     */
    public function getPeopleId($login_id = '')
    {
        if ($login_id == '') { return false; }
        $a_results = $this->read(array('login_id' => $login_id));
        if ($a_results !== false) {
            if (isset($a_results[0]) && $a_results[0] != []) {
                return $a_results[0]['people_id'];
            }
        }
        $this->error_message = $this->o_db->getSqlErrorMessage();
        return false;
    }

    /**
     * Updates the bad_login_count field for the user by one
     * @param int $people_id
     * @return bool
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
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     * Increments the bad_login_ts record by one minute
     * @param int $people_id required
     * @return bool
     */
    public function incrementBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_table}
            SET bad_login_ts = bad_login_ts + 60
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     * Returns the user record.
     * @param int|string $user either user id or user name
     * @return array
     */
    public function readPeopleRecord($user = '')
    {
        if ($user == '') { return []; }
        if (is_numeric($user)) {
            $a_search_by = ['people_id' => $user];
        }
        else {
            $a_search_by = ['login_id' => $user];
        }
        $a_records = $this->read($a_search_by);
        if (isset($a_records[0]) && is_array($a_records[0])) {
            return $a_records[0];
        } else {
            return [];
        }
    }

    /**
     * Resets the bad_login_count to 0
     * @param int $people_id required
     * @return bool
      */
    public function resetBadLoginCount($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_table}
            SET bad_login_count = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     * Resets the timestamp to 0
     * @param int $people_id required
     * @return bool
     */
    public function resetBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $update_sql = "
            UPDATE {$this->db_table}
            SET bad_login_ts = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        $results = $this->o_db->update($update_sql, $a_values, true);
        return $results;
    }

    /**
     * Sets the bad login timestamp for the user.
     * @param int $people_id required
     * @return bool
     */
    public function setBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_table}
            SET bad_login_ts = :timestamp
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id, ':timestamp' => time());
        $results = $this->o_db->update($sql, $a_values, true);
        return $results;
    }

    /**
     * Sets the user record to be logged in.
     * @param int $people_id
     * @return bool
     */
    public function setLoggedIn($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_table}
            SET is_logged_in = 1
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     * Sets the user record to be logged out.
     * @param int $people_id
     * @return bool
     */
    public function setLoggedOut($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_table}
            SET is_logged_in = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     * Updates the user record with a new password
     * @param int    $people_id required
     * @param string $password required
     * @return bool success or failure
     */
    public function updatePassword($people_id = -1, $password = '')
    {
        if ($people_id == -1 || $password == '') { return false; }
        $sql = "
            UPDATE {$this->db_table}
            SET password = :password
            WHERE id = :people_id
        ";
        $a_values = [':people_id' => $people_id, ':password' => $password];
        return $this->o_db->update($sql, $a_values, true);
    }

    /**
     * Updates the user record to be make the user active or inactive, normally inactive.
     * @param int $people_id   required id of a user
     * @param int $is_active optional defaults to inactive (0)
     * @return bool success or failure
     */
    public function updateActive($people_id = -1, $is_active = 0)
    {
        if ($people_id == -1) { return false; }
        $is_active = (int) $is_active;
        if ($is_active > 1) {
            $is_active = 1;
        }
        if ($is_active == '') {
            $is_active = 0;
        }
        $sql = "
            UPDATE {$this->db_table}
            SET is_active = :is_active
            WHERE people_id = :people_id
        ";
        $a_values = [':people_id' => $people_id, ':is_active' => $is_active];
        $results = $this->o_db->update($sql, $a_values, true);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
        }
        return $results;
    }

    ### Utility methods ###
    /**
     * @param int $people_id
     * @return bool
     */
    public function isId($people_id = -1)
    {
        if (ctype_digit($people_id) && $people_id != -1) {
            $a_where_values = ['people_id' => $people_id];
            $results = $this->read($a_where_values);
            if (isset($results[0]['people_id']) && $results[0]['people_id'] == $people_id) {
                return true;
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
        if ($this->readPeopleRecord($login_id) == []) {
            return false;
        }
        return true;
    }

    /**
     * @param string $short_name
     * @return bool
     */
    public function isExistingShortName($short_name = '')
    {
        $a_results = $this->read(['short_name' => $short_name]);
        if (isset($a_results[0]['short_name']) && $a_results[0]['short_name'] == $short_name) {
            return true;
        }
        return false;
    }
}
