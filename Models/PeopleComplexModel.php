<?php
/**
 * Class PeopleComplexModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\CustomException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the Model expected operations, database CRUD and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.7
 * @date    2017-12-12 10:08:19
 * @change_log
 * - v1.0.0-alpha.7 - ModelException change reflected here                  - 2017-12-12 wer
 * - v1.0.0-alpha.6 - Moved some methods from here to PeopleModel           - 2017-12-05 wer
 * - v1.0.0-alpha.5 - Moved some business logic from controller to here     - 2017-12-02 wer
 * - v1.0.0-alpha.4 - Refactored to use ModelException                      - 2017-06-17 wer
 * - v1.0.0-alpha.2 - DbUtilityTraits change reflected here                 - 2017-05-09 wer
 * - v1.0.0-alpha.0 - Initial version                                       - 2016-12-08 wer
 */
class PeopleComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /** @var PeopleModel */
    private $o_people;
    /** @var GroupsModel */
    private $o_group;
    /** @var PeopleGroupMapModel */
    private $o_pgm;

    /**
     * PeopleComplexModel constructor.
     *
     * @param Di $o_di
     * @throws CustomException
     */
    public function __construct(Di $o_di)
    {
        $o_db = $o_di->get('db');
        if (!$o_db instanceof DbModel) {
            throw new CustomException('Could not get o_db', ExceptionHelper::getCodeNumber('instance'));
        }
        $this->setupProperties($o_db, 'people');
        $this->o_people = new PeopleModel($o_db);
        $this->o_group  = new GroupsModel($o_db);
        $this->o_pgm    = new PeopleGroupMapModel($o_db);
        $this->setObjectNames(['o_people', 'o_group', 'o_pgm']);
        $this->setupElog($o_di);
    }

    /**
     * Deletes the person and related records.
     *
     * @param int $people_id
     * @return bool
     * @throws ModelException
     */
    public function deletePerson($people_id = -1):bool
    {
        $meth = __METHOD__ . '.';
        if ($people_id === -1) {
            throw new ModelException('Missing required people id', 420);
        }
        try {
            $this->o_db->startTransaction();
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to start transaction', $e->getCode(), $e);
        }
        $o_pdo = $this->o_db->getPDO();
          $this->logIt('In Transaction 1: ' . $o_pdo->inTransaction(), LOG_OFF, $meth . __LINE__);
        try {
            $this->o_pgm->deleteByPeopleId($people_id);
              $this->logIt('In Transaction 2: ' . $o_pdo->inTransaction(), LOG_OFF, $meth . __LINE__);
            try {
                $this->o_people->delete($people_id);
                  $this->logIt('In Transaction 3: ' . $o_pdo->inTransaction(), LOG_OFF, $meth . __LINE__);
                try {
                    $this->o_db->commitTransaction();
                }
                catch (ModelException $e) {
                    $this->o_db->rollbackTransaction();
                    $error_message = DEVELOPER_MODE
                        ? $e->errorMessage()
                        : $e->getMessage();
                    throw new ModelException($error_message, $e->getCode(), $e);
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to delete the people record(s):';
                $this->error_message .= DEVELOPER_MODE
                    ? $e->errorMessage()
                    : $e->getMessage();
                $this->logIt($this->error_message, LOG_OFF, $meth . __LINE__);
                $this->o_db->rollbackTransaction();
                throw new ModelException($this->error_message, $e->getCode(), $e);
            }
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to delete the pgm record'.  $this->o_pgm->getErrorMessage();
            $this->o_db->rollbackTransaction();
            throw new ModelException($this->error_message, $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Gets the user values based on login_id or people_id.
     *
     * @param int|string $people_id the people_id or login_id (as defined in the db)
     * @return array the records for the person
     * @throws ModelException
     */
    public function readInfo($people_id = ''):array
    {
        if ($people_id === '') {
            $this->error_message = 'People ID not provided.';
            throw new ModelException($this->error_message, 220);
        }

        $a_people_fields = $this->o_db->selectDbColumns($this->lib_prefix . 'people');
        if (empty($a_people_fields)) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, 220);
        }
        $a_group_fields = $this->o_db->selectDbColumns($this->lib_prefix . 'groups');
        if (empty($a_group_fields)) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, 220);
        }

        $fields = '';
        foreach ($a_people_fields as $field_name) {
            if ($field_name !== 'password') {
                $fields .= $fields === ''
                    ? 'p.' . $field_name
                    : ', p.' . $field_name
                ;
            }
        }
        foreach ($a_group_fields as $field_name) {
            $fields .= ', g.' . $field_name;
        }


        if (is_numeric($people_id)) {
            $where          = 'p.people_id = :people_id';
            $a_where_values = [':people_id' => $people_id];
        }
        else {
            $where          = 'p.login_id = :login_id';
            $a_where_values = [':login_id' => $people_id];
        }
        $sql = "
            SELECT DISTINCT $fields
            FROM {$this->db_table} as p
            JOIN {$this->db_table}_group_map as pgm
                USING (people_id)
            JOIN {$this->lib_prefix}groups as g
                USING (group_id)
            WHERE {$where}
            ORDER BY g.group_auth_level DESC, g.group_name ASC
        ";
        try {
            $a_people = $this->o_db->search($sql, $a_where_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
        $a_person = [];
        if (isset($a_people[0]) && is_array($a_people[0])) {
            if (($a_people[0]['people_id'] === $people_id) || ($a_people[0]['login_id'] === $people_id)) {
                $a_groups = array();
                foreach ($a_people as $key => $person) {
                    $a_groups[] = [
                        'group_id'          => $person['group_id'],
                        'group_name'        => $person['group_name'],
                        'group_description' => $person['group_description'],
                        'group_auth_level'  => $person['group_auth_level']
                    ];
                }
                foreach ($a_groups as $key => $row) {
                    $a_group_id[$key] = $row['group_id'];
                }
                array_multisort($a_group_id, SORT_ASC, $a_groups);
                $previous_group = '';
                foreach ($a_groups as $key => $a_group) {
                    if ($a_group['group_id'] === $previous_group) {
                        unset($a_groups[$key]);
                    }
                    else {
                        $previous_group = $a_group['group_id'];
                    }
                }
                $a_person = $a_people[0];
                unset(
                    $a_person['group_id'],
                    $a_person['group_name'],
                    $a_person['group_description'],
                    $a_person['group_auth_level']
                );
                $a_person['groups'] = $a_groups;
            }
        }
        else {
            throw new ModelException('Unable to find the person specified.', 230);
        }
        return $a_person;
    }

    /**
     * Reads the people in the group provided.
     *
     * @param int $group_id
     * @return bool|mixed
     * @throws ModelException
     */
    public function readByGroup($group_id = -1)
    {
        try {
            $a_people_fields = $this->o_db->selectDbColumns($this->lib_prefix . 'people');
            try {
                $a_group_fields = $this->o_db->selectDbColumns($this->lib_prefix . 'groups');
            }
            catch (ModelException $e) {
                $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
                throw new ModelException($this->error_message, 220);
            }
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, 220);
        }
        if (is_numeric($group_id)) {
            $group_and = 'g.group_id = :group_id';
            $a_values = ['group_id' => $group_id, 'is_active' => 'true'];
        }
        else {
            $group_and = 'g.group_name = :group_name';
            $a_values = ['group_name' => $group_id, 'is_active' => 'true'];
        }
        $fields = '';
        foreach ($a_people_fields as $field_name) {
            if ($field_name !== 'password') {
                $fields .= $fields === ''
                    ? 'p.' . $field_name
                    : ', p.' . $field_name
                ;
            }
        }
        foreach ($a_group_fields as $field_name) {
            $fields .= ', g.' . $field_name;
        }
        $sql = "
            SELECT $fields
            FROM {$this->lib_prefix}people as p
            JOIN {$this->lib_prefix}people_group_map pgm
              ON p.people_id = pgm.people_id
            JOIN {$this->lib_prefix}groups as g
              ON (
                    g.group_id = pgm.group_id
                AND $group_and
                 )
            WHERE p.is_active = :is_active
        ";
        try {
            $a_results = $this->o_db->search($sql, $a_values);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            $this->error_message .= "\n" . $sql . "\n" . var_export($a_values, true);
            throw new ModelException($this->error_message, 200, $e);
        }
        return $a_results;
    }

    /**
     * Saves the person.
     * If the values contain a value of people_id, person is updated
     * Else it is a new person.
     *
     * @param array $a_person values to save
     * @return int
     * @throws ModelException
     */
    public function savePerson(array $a_person = []):int
    {
          $log_message = 'person to save ' . var_export($a_person, TRUE);
          $this->logIt($log_message, LOG_OFF, __METHOD__);
        if (!isset($a_person['people_id']) || $a_person['people_id'] === '') { // New User
            $a_required_keys = array(
                'login_id',
                'real_name',
                'password'
            );
            foreach($a_required_keys as $key_name) {
                if ($a_person[$key_name] === '') {
                    switch ($key_name) {
                        case 'login_id':
                            throw new ModelException('Missing login_id', 20);
                        case 'password':
                            throw new ModelException('Missing password', 20);
                        case 'real_name':
                            $a_person['real_name'] = $a_person['login_id'];
                            break;
                        default:
                            break;
                    }
                }
            }
            $a_groups = $this->makeGroupIdArray($a_person['groups']);
            unset($a_person['groups']);
            try {
                $this->o_db->startTransaction();
            }
            catch (ModelException $e) {
                throw new ModelException('Could not start the transaction', 12);
            }
            try {
                $a_ids = $this->o_people->create($a_person);
                if (empty($a_ids[0])) {
                    throw new ModelException('Unable to save the new person, unknown error.');
                }
                $new_people_id = $a_ids[0];
                $a_pgm_values = $this->makePgmArray($new_people_id, $a_groups);
                if (empty($a_pgm_values)) {
                    $this->error_message = 'Unable to create the required values.';
                    $err_code = ExceptionHelper::getCodeNumberModel('create unknown');
                    $this->o_db->rollbackTransaction();
                    throw new ModelException($this->error_message, $err_code);
                }
                try {
                    $this->o_pgm->create($a_pgm_values);
                    try {
                        $this->o_db->commitTransaction();
                        return $new_people_id;
                    }
                    catch (ModelException $e) {
                        $this->error_message = 'Unable to commit the transaction: ' . $e->errorMessage();
                        $error_code = ExceptionHelper::getCodeNumberModel('transaction commit');
                    }
                }
                catch (ModelException $e) {
                    $this->error_message = 'Unable to create the people group map record:' . $e->errorMessage();
                    $error_code = $e->getCode();
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to create the person record: ' . $e->errorMessage();
                $error_code = $e->getCode();
            }
            $this->o_db->rollbackTransaction();
            throw new ModelException($this->error_message, $error_code);
        }

        // Existing User
        if (isset($a_person['groups'])) {
            $a_groups = $this->makeGroupIdArray($a_person['groups']);
            $a_pg_values = $this->makePgmArray($a_person['people_id'], $a_groups);
            unset($a_person['groups']);
        }
        $a_possible_keys = $this->o_people->getDbFields();
        if (isset($a_person['password'])) {
            $a_person['password'] = $this->o_people->hashPass($a_person['password']);
        }
        foreach($a_possible_keys as $key_name) {
            if (empty($a_person[$key_name])) {
                switch ($key_name) {
                    case 'people_id':
                        $this->error_message = 'Missing people_id.';
                        $err_code = ExceptionHelper::getCodeNumberModel('create missing primary');
                        throw new ModelException($this->error_message, $err_code);
                    case 'login_id':
                    case 'password':
                    case 'real_name':
                        unset($a_person[$key_name]);
                        break;
                    case 'is_logged_in':
                    case 'is_active':
                    case 'is_immutable':
                        $a_person[$key_name] = 'false';
                        break;
                    default:
                        break;
                }
            }
        }
        if (!empty($a_pg_values)) {
            try {
                $this->o_db->startTransaction();
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to start the transaction: ' . $e->errorMessage();
                $err_code = ExceptionHelper::getCodeNumberModel('transaction start');
                throw new ModelException($this->error_message, $err_code);
            }
            try {
                $this->o_people->update($a_person, ['login_id']);
                try {
                    $this->o_pgm->deleteByPeopleId($a_person['people_id']);
                    try {
                        $this->o_pgm->create($a_pg_values);
                        try {
                            $this->o_db->commitTransaction();
                            return $a_person['people_id'];
                        }
                        catch (ModelException $e) {
                            $this->error_message = 'Unable to commit the transaction';
                            if (DEVELOPER_MODE) {
                                $this->error_message .= ' ' . $e->errorMessage();
                            }
                            $error_code = 40;
                        }
                    }
                    catch (ModelException $e) {
                        $this->error_message = 'Unable to create the people group map record.';
                        if (DEVELOPER_MODE) {
                            $this->error_message .= ' ' . $e->errorMessage();
                        }
                        $error_code = $e->getCode();
                    }
                }
                catch (ModelException $e) {
                    $this->error_message = 'Unable to delete old people group map record.';
                    if (DEVELOPER_MODE) {
                        $this->error_message .= ' ' . $e->errorMessage();
                    }
                    $error_code = $e->getCode();
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Unable to update the person: '. $e->errorMessage();
                $error_code = $e->getCode();
            }
            $this->o_db->rollbackTransaction();
            throw new ModelException($this->error_message, $error_code);
        }
        throw new ModelException('Unable to save the person. ' . $this->error_message, 110);
    }

    ### Business Logic & Utilities ###
    /**
     * Creates the array used to save a new person to the database.
     * Values passed in are normally from a POSTed form that have been sanitized.
     *
     * @param array $a_values
     * @return array|bool|mixed|string
     */
    public function createNewPersonArray(array $a_values = [])
    {
        $meth = __METHOD__ . '.';
        $a_person = $a_values['person'];
        $a_person = $this->o_people->setPersonValues($a_person);
        if (!is_array($a_person)) { // then it should be a string describing the error from setPersonValues.
            return $a_person;
        }
        if (!isset($a_values['groups']) || count($a_values['groups']) < 1) {
            return 'group-missing';
        }
        $a_person['groups'] = $a_values['groups'];
        $this->logIt('Person values: ' . var_export($a_person, TRUE), LOG_OFF, $meth . __LINE__);
        return $a_person;
    }

    /**
     * Returns an array used in the creation of people group map records.
     *
     * @param string|array $group_id   Optional, if $group_name is empty then Registered group is assigned.
     * @param string       $group_name Optional, used if $group_id is empty
     * @return array
     */
    public function makeGroupIdArray($group_id = '', $group_name = ''):array
    {
        if (is_array($group_id)) {
            $a_group_ids = $group_id;
        }
        elseif ($group_id !== '') {
           $results = $this->o_group->isValidGroupId($group_id);
           $a_group_ids = $results
               ? [$group_id]
               : [];
        }
        else {
            $a_group_ids = [];
        }
        if (empty($a_group_ids) && !empty($group_name)) {
            try {
                $a_group = $this->o_group->readByName($group_name);
                if (!empty($a_group)) {
                    $a_group_ids = array($a_group[0]['group_id']);
                }
            }
            catch (ModelException $e) {
                $a_group_ids = [];
            }
        }
        if (empty($a_group_ids)) {
            try {
                $a_found_groups = $this->o_group->readByName('Registered');
                $use_id = $a_found_groups !== false && count($a_found_groups) > 0
                    ? $a_found_groups[0]['group_id']
                    : 5;
                $a_group_ids = [$use_id];
            }
            catch (ModelException $e) {
                $a_group_ids = ['5'];
            }
        }
        return $a_group_ids;
    }

    /**
     * Returns an array mapping a person to the group(s) specified.
     *
     * @param string $people_id
     * @param array  $a_groups
     * @return array
     */
    public function makePgmArray($people_id = '', array $a_groups = array()):array
    {
        if ($people_id === '' || $a_groups === array()) {
            return array();
        }
        $a_return_map = array();
        foreach ($a_groups as $group_id) {
            $a_return_map[] = ['people_id' => $people_id, 'group_id' => $group_id];
        }
        return $a_return_map;
    }

}
