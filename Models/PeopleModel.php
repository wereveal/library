<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file PeopleModel.php
 *  @ingroup ritc_library models
 *  @namespace Ritc/Library/Models
 *  @class PeopleModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β11
 *  @date 2015-09-25 15:34:56
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β11 - Added missing method isId - causing bug elsewhere           - 09/25/2015 wer
 *      v1.0.0β10 - Added db error message retrieval                            - 09/23/2015 wer
 *      v1.0.0β9  - Added 'description' to database and added it here           - 09/22/2015 wer
 *      v1.0.0β8  - more changes to the readInfo method                         - 09/03/2015 wer
 *      v1.0.0β7  - had to rewrite the sql for the readInfo method              - 08/04/2015 wer
 *      v1.0.0β6  - refactoring elsewhere caused changes here                   - 07/31/2015 wer
 *      v1.0.0β5  - refactoring method name to reflect what is happening better - 01/06/2015 wer
 *      v1.0.0β4  - reverted to injecting DbModel                               - 11/17/2014 wer
 *      v1.0.0β3  - changed to use DI/IOC                                       - 11/15/2014 wer
 *      v1.0.0β2  - extends the Base class, injects the DbModel, clean up       - 09/23/2014 wer
 *      v1.0.0β1  - First Live version                                          - 09/15/2014 wer
 *      v0.1.0β1  - Initial version                                             - 09/11/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

class PeopleModel implements ModelInterface
{
    use LogitTraits;

    private $db_prefix;
    private $db_type;
    private $error_message;
    private $o_db;
    private $o_group;
    private $o_pgm;

    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->db_type   = $o_db->getDbType();
        $this->db_prefix = $o_db->getDbPrefix();
        $this->o_group   = new GroupsModel($this->o_db);
        $this->o_pgm     = new PeopleGroupMapModel($this->o_db);
    }

    ### Basic CRUD commands, required by interface, deals only with the {$this->db_prefix}user table ###
    /**
     *  Creates new people record(s) in the people table.
     *  @param array $a_values required Can be a simple assoc array or array of assoc arrays
     *                         e.g. ['login_id' => 'fred', 'real_name' => 'Fred', 'password' => 'letmein']
     *                         or
     *                         [
     *                             ['login_id' => 'fred',   'real_name' => 'Fred',   'password' => 'letmein'],
     *                             ['login_id' => 'barney', 'real_name' => 'Barney', 'password' => 'lethimin']
     *                         ].
     *                         Optional key=>values 'short_name',
     *                                              'description', 'is_logged_in',
     *                                              'is_active' & 'is_immutable'
     *  @return array|bool
    **/
    public function create(array $a_values = array())
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
        $a_values = Arrays::createRequiredPairs($a_values, $a_required_keys, true);
        if (Arrays::hasBlankValues($a_values, ['login_id', 'password'])) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}people
                (login_id, real_name, short_name, password, description, is_logged_in, is_active, is_immutable)
            VALUES
                (:login_id, :real_name, :short_name, :password, :description, :is_logged_in, :is_active, :is_immutable)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}people")) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
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
    public function read(array $a_search_values = array(), array $a_search_params = array())
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
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        elseif (count($a_search_params) > 0) {
            $where = $this->o_db->buildSqlWhere(array(), $a_search_params);
        }
        else {
            $where = " ORDER BY 'login_id'";
        }
        $sql = "
            SELECT people_id,
                login_id,
                real_name,
                short_name,
                password,
                description,
                is_logged_in,
                is_active,
                is_immutable,
                created_on,
                bad_login_count,
                bad_login_ts
            FROM {$this->db_prefix}people
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
     * Updates a single {$this->db_prefix}people record.
     * @param array $a_values required $a_values['people_id'] || $a_values['login_id']
     * @return bool
     */
    public function update(array $a_values = array())
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
        $sql_set = $this->o_db->buildSqlSet($a_values, ['people_id', 'login_id']);
        if (isset($a_values['people_id'])) {
            $sql_where = 'WHERE people_id = :people_id';
            if (isset($a_values['login_id'])) {
                unset($a_values['login_id']);
            }
        }
        elseif (isset($a_values['login_id'])) {
            $sql_where = 'WHERE login_id = :login_id';
            if (isset($a_values['people_id'])) {
                unset($a_values['people_id']);
            }
        }
        else { // it should never fall into this.
            $sql_where = '';
        }
        if ($sql_where == '' || $sql_set == '') {
            return false;
        }
        $sql = "
            UPDATE {$this->db_prefix}people
            {$sql_set}
            {$sql_where}
        ";
        $this->logIt($sql, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $results = $this->o_db->update($sql, $a_values, true);
        if ($results === false) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
        }
        return $results;
    }
    /**
     *  Deletes a {$this->db_prefix}people record based on id.
     *  @param int $people_id required
     *  @return bool
    **/
    public function delete($people_id = -1)
    {
        if ($people_id == -1 || !ctype_digit($people_id)) { return false; }
        $sql = "
            DELETE FROM {$this->db_prefix}people
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
     *  Gets the people_id (primary record key) for a specific login_id.
     *  @param string $login_id required
     *  @return int|bool $people_id
     */
    public function getPeopleId($login_id = '')
    {
        if ($login_id == '') { return false; }
        $a_results = $this->read(array('login_id' => $login_id));
        if ($a_results !== false) {
            if (isset($a_results[0]) && $a_results[0] != array()) {
                return $a_results[0]['people_id'];
            }
        }
        $this->error_message = $this->o_db->getSqlErrorMessage();
        return false;
    }
    /**
     *  Updates the bad_login_count field for the user by one
     *  @param int $people_id
     *  @return bool
     **/
    public function incrementBadLoginCount($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_count = bad_login_count + 1
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Increments the bad_login_ts record by one minute
     *  @param int $people_id required
     *  @return bool
     */
    public function incrementBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_ts = bad_login_ts + 60
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Returns the user record.
     *  @param int|string $user either user id or user name
     *  @return array
     */
    public function readPeopleRecord($user = '')
    {
        if ($user == '') { return array(); }
        if (ctype_digit($user)) {
            $a_search_by = ['$people_id' => $user];
        }
        else {
            $a_search_by = ['login_id' => $user];
        }
        $a_records = $this->read($a_search_by);
        if (is_array($a_records[0])) {
            return $a_records[0];
        } else {
            return array();
        }
    }
    /**
     *  Resets the bad_login_count to 0
     *  @param int $people_id required
     *  @return bool
     **/
    public function resetBadLoginCount($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_count = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Resets the timestamp to 0
     *  @param int $people_id required
     *  @return bool
    **/
    public function resetBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $update_sql = "
            UPDATE {$this->db_prefix}people
            SET bad_login_ts = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        $results = $this->o_db->update($update_sql, $a_values, true);
        return $results;
    }
    /**
     *  Sets the bad login timestamp for the user.
     *  @param int $people_id required
     *  @return bool
    **/
    public function setBadLoginTimestamp($people_id = -1)
    {
        if ($people_id == -1) { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
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
            UPDATE {$this->db_prefix}people
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
            UPDATE {$this->db_prefix}people
            SET is_logged_in = 0
            WHERE people_id = :people_id
        ";
        $a_values = array(':people_id' => $people_id);
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Updates the user record with a new password
     *  @param int    $people_id required
     *  @param string $password required
     *  @return bool success or failure
     */
    public function updatePassword($people_id = -1, $password = '')
    {
        if ($people_id == -1 || $password == '') { return false; }
        $sql = "
            UPDATE {$this->db_prefix}people
            SET password = :password
            WHERE id = :people_id
        ";
        $a_values = [':people_id' => $people_id, ':password' => $password];
        return $this->o_db->update($sql, $a_values, true);
    }
    /**
     *  Updates the user record to be make the user active or inactive, normally inactive.
     *  @param int $people_id   required id of a user
     *  @param int $is_active optional defaults to inactive (0)
     *  @return bool success or failure
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
            UPDATE {$this->db_prefix}people
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

    ### More complex methods using multiple tables ###
    /**
     *  Deletes the person and related records.
     *  @param array $a_person
     *  @return bool
     */
    public function deletePerson(array $a_person = array())
    {
        if ($a_person == array()) {
            return false;
        }
        return false;
    }
    /**
     *  Gets the user values based on login_id or people_id.
     *  @param int|string $people_id the people_id or login_id (as defined in the db)
     *  @return array, the records for the person
    **/
    public function readInfo($people_id = '')
    {
        $meth = __METHOD__ . '.';
        if ($people_id == '') {
            return array();
        }
        if (ctype_digit($people_id)) {
            $where          = "p.people_id = :people_id";
            $a_where_values = [':people_id' => $people_id];
        }
        else {
            $where          = "p.login_id = :login_id";
            $a_where_values = [':login_id' => $people_id];
        }
        $sql = "
            SELECT DISTINCT p.people_id, p.login_id, p.real_name, p.short_name,
                p.password, p.description, p.is_logged_in, p.bad_login_count,
                p.bad_login_ts, p.is_active, p.is_immutable, p.created_on,
                g.group_id, g.group_name, g.group_description,
                r.role_id, r.role_level, r.role_name
            FROM {$this->db_prefix}people as p
            JOIN {$this->db_prefix}groups as g
            JOIN {$this->db_prefix}people_group_map as pgm
                ON p.people_id = pgm.people_id
                AND pgm.group_id = g.group_id
            JOIN {$this->db_prefix}roles as r
            JOIN {$this->db_prefix}group_role_map as grm
                ON grm.group_id = g.group_id
                AND grm.role_id = r.role_id
            WHERE {$where}
            ORDER BY r.role_level ASC, g.group_name ASC
        ";
        $this->logIt("Select User: {$sql}", LOG_OFF, $meth . __LINE__);
        $this->logIt("a_where_values: " . var_export($a_where_values, true), LOG_OFF, $meth . __LINE__);
        $a_people = $this->o_db->search($sql, $a_where_values);
        $this->logIt("a_people: " . var_export($a_people, true), LOG_OFF, $meth);
        if (isset($a_people[0]) && is_array($a_people[0])) {
            if (($a_people[0]['people_id'] == $people_id) || ($a_people[0]['login_id'] == $people_id)) {
                $a_roles = array();
                $a_groups = array();
                foreach ($a_people as $key => $person) {
                    $a_roles[] = [
                        'role_id'    => $person['role_id'],
                        'role_level' => $person['role_level'],
                        'role_name'  => $person['role_name']
                    ];
                    $a_groups[] = [
                        'group_id'          => $person['group_id'],
                        'group_name'        => $person['group_name'],
                        'group_description' => $person['group_description']
                    ];
                }
                foreach ($a_roles as $key => $row) {
                    $a_role_id[$key] = $row['role_id'];
                }
                array_multisort($a_role_id, SORT_ASC, $a_roles);
                foreach ($a_groups as $key => $row) {
                    $a_group_id[$key] = $row['group_id'];
                }
                array_multisort($a_group_id, SORT_ASC, $a_groups);

                $previous_role = '';
                foreach ($a_roles as $key => $a_role) {
                    if ($a_role['role_id'] == $previous_role) {
                        unset($a_roles[$key]);
                    }
                    else {
                        $previous_role = $a_role['role_id'];
                    }
                }
                $previous_group = '';
                foreach ($a_groups as $key => $a_group) {
                    if ($a_group['group_id'] == $previous_group) {
                        unset($a_groups[$key]);
                    }
                    else {
                        $previous_group = $a_group['group_id'];
                    }
                }
                $a_person = $a_people[0];
                unset($a_person['role_id']);
                unset($a_person['role_level']);
                unset($a_person['role_name']);
                unset($a_person['group_id']);
                unset($a_person['group_name']);
                unset($a_person['group_description']);
                $a_person['roles'] = $a_roles;
                $a_person['groups'] = $a_groups;
                $this->logIt("Found Person: " . var_export($a_person, true), LOG_OFF, $meth . __LINE__);
                return $a_person;
            }
        }
        else {
            $this->logIt("Did not find person. {$sql}", LOG_OFF, $meth);
        }
        return array();
    }
    /**
     *  Saves the person.
     *  If the values contain a value of people_id, person is updated
     *  Else it is a new person.
     *  @param array $a_person values to save
     *  @return mixed, people_id or false
     **/
    public function savePerson(array $a_person = array())
    {
        if (!Arrays::hasRequiredKeys($a_person, ['login_id', 'password'])) {
            return false;
        }
        if (!isset($a_person['people_id']) || $a_person['people_id'] == '') { // New User
            $a_required_keys = array(
                'login_id',
                'real_name',
                'password',
                'short_name'
            );
            foreach($a_required_keys as $key_name) {
                if ($a_person[$key_name] == '') {
                    switch ($key_name) {
                        case 'login_id':
                        case 'password':
                            return false;
                        case 'real_name':
                            $a_person['real_name'] = $a_person['login_id'];
                            break;
                        case 'short_name':
                            $a_person['short_name'] = $this->createShortName($a_person['real_name']);
                            break;
                        default:
                            break;
                    }
                }
            }
            $a_person['password'] = password_hash($a_person['password'], PASSWORD_DEFAULT);
            $a_groups = $this->makeGroupIdArray($a_person['groups']);
            $a_person = $this->setPersonValues($a_person);
            if ($this->o_db->startTransaction()) {
                $a_ids = $this->create($a_person);
                if ($a_ids !== false) {
                    $new_people_id = $a_ids[0];
                    $a_pgm_values = $this->makePgmArray($new_people_id, $a_groups);
                    if ($a_pgm_values != array()) {
                        if ($this->o_pgm->create($a_pgm_values)) {
                            if ($this->o_db->commitTransaction()) {
                                return $new_people_id;
                            }
                        }
                    }
                } // new user created
                $this->error_message = $this->o_db->getSqlErrorMessage();
                $this->o_db->rollbackTransaction();
            }
        }
        else { // Existing User
            $a_required_keys = array(
                'people_id',
                'login_id',
                'real_name',
                'password',
                'short_name'
            );
            foreach($a_required_keys as $key_name) {
                if ($a_person[$key_name] == '') {
                    switch ($key_name) {
                        case 'people_id':
                        case 'login_id':
                        case 'password':
                            return false;
                        case 'real_name':
                            $a_person['real_name'] = $a_person['login_id'];
                            break;
                        case 'short_name':
                            $a_person['short_name'] = $this->createShortName($a_person['real_name']);
                            break;
                        default:
                            break;
                    }
                }
            }
            $a_person['password'] = password_hash($a_person['password'], PASSWORD_DEFAULT);
            $a_groups = $this->makeGroupIdArray($a_person['groups']);
            $a_person = $this->setPersonValues($a_person);
            $a_pg_values = $this->makePgmArray($a_person['people_id'], $a_groups);
            if ($a_pg_values != array()) {
                if ($this->o_db->startTransaction()) {
                    if ($this->update($a_person)) {
                        if ($this->o_pgm->deleteByPeopleId($a_person['people_id'])) {
                            if ($this->o_pgm->create($a_pg_values)) {
                                if ($this->o_db->commitTransaction()) {
                                    return true;
                                }
                            }
                        }
                    }
                    $this->error_message = $this->o_db->getSqlErrorMessage();
                    $this->o_db->rollbackTransaction();
                }
            }
        }
        return false;
    }

    ### Utility methods ###
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
     *  Returns an array used in the creation of people group map records.
     *  @param array $group_id
     *  @param       $group_name
     *  @return array
     */
    private function makeGroupIdArray($group_id = array(), $group_name = '')
    {
        if (is_array($group_id)) {
            $a_group_ids = $group_id;
        }
        elseif ($group_id != '') {
            $a_group_ids = $this->o_group->isValidGroupId($group_id)
                ? array($group_id)
                : array();
        }
        else {
            $a_group_ids = array();
        }
        if ($a_group_ids == array() && $group_name != '') {
            $a_group = $this->o_group->readByName($group_name);
            if ($a_group !== false && count($a_group) > 0) {
                $a_group_ids = array($a_group[0]['group_id']);
            }
            else {
                $a_found_groups = $this->o_group->readByName('Registered');
                $use_id = $a_found_groups !== false && count($a_found_groups) > 0
                    ? $a_found_groups[0]['group_id']
                    : 5;
                $a_group_ids = array($use_id);
            }
        }
        return $a_group_ids;
    }
    /**
     *  Returns an array mapping a person to the group(s) specified.
     *  @param string $people_id
     *  @param array  $a_groups
     *  @return array
     */
    private function makePgmArray($people_id = '', array $a_groups = array())
    {
        if ($people_id == '' || $a_groups == array()) {
            return array();
        }
        $a_return_map = array();
        foreach ($a_groups as $group_id) {
            $a_return_map[] = ['people_id' => $people_id, 'group_id' => $group_id];
        }
        return $a_return_map;
    }

    ### Required by Interface ###
    public function getErrorMessage()
    {
        return $this->error_message;
    }
}
