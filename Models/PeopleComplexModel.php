<?php
/**
 * @brief     Provides data using more complex sql for the people and groups.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/PeopleComplexModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.2
 * @date      2017-05-09 17:39:39
 * @note Change Log
 * - v1.0.0-alpha.2 - DbUtilityTraits change reflected here                 - 2017-05-09 wer
 * - v1.0.0-alpha.1 - Bug fix                                               - 2017-01-27 wer
 * - v1.0.0-alpha.0 - Initial version                                       - 2016-12-08 wer
 * @todo Ritc/Library/Models/PeopleComplexModel.php - Test
 */
namespace Ritc\Library\Models;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class PeopleComplexModel.
 * @class   PeopleComplexModel
 * @package Ritc\Library\Models
 */
class PeopleComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /**
     * @var \Ritc\Library\Models\PeopleModel
     */
    private $o_people;
    /**
     * @var \Ritc\Library\Models\GroupsModel
     */
    private $o_group;
    /**
     * @var \Ritc\Library\Models\PeopleGroupMapModel
     */
    private $o_pgm;

    /**
     * PeopleComplexModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'people');
        $this->o_people = new PeopleModel($o_db);
        $this->o_group  = new GroupsModel($o_db);
        $this->o_pgm    = new PeopleGroupMapModel($o_db);
        if (defined(DEVELOPER_MODE) && DEVELOPER_MODE) {
            $this->o_people->setElog($this->o_elog);
            $this->o_group->setElog($this->o_elog);
            $this->o_pgm->setElog($this->o_elog);
        }
    }

    /**
     * Deletes the person and related records.
     * @param int $people_id
     * @return bool
     */
    public function deletePerson($people_id = -1)
    {
        if ($people_id == -1) {
            return false;
        }
        if ($this->o_db->startTransaction()) {
            if ($this->o_pgm->deleteByPeopleId($people_id)) {
                if ($this->o_people->delete($people_id)) {
                    if ($this->o_db->commitTransaction()) {
                        return true;
                    }
                    else {
                        $this->error_message = "Could not commit the transaction.";
                    }
                }
                else {
                    $this->error_message = $this->o_db->getSqlErrorMessage();
                }
            }
            else {
                $this->error_message = $this->o_pgm->getErrorMessage();
            }
            $this->o_db->rollbackTransaction();
        }
        else {
            $this->error_message = "Could not start transaction.";
        }
        return false;
    }

    /**
     * Gets the user values based on login_id or people_id.
     * @param int|string $people_id the people_id or login_id (as defined in the db)
     * @return array|bool the records for the person
     */
    public function readInfo($people_id = '')
    {
        $meth = __METHOD__ . '.';
        if ($people_id == '') {
            $this->error_message = 'People ID not provided.';
            return false;
        }

        $a_people_fields = $this->o_db->selectDbColumns($this->db_prefix . 'people');
        if (empty($a_people_fields)) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            return false;
        }
        $a_group_fields = $this->o_db->selectDbColumns($this->db_prefix . 'groups');
        if (empty($a_group_fields)) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            return false;
        }

        $fields = '';
        foreach ($a_people_fields as $field_name) {
            if ($field_name != 'password') {
                $fields .= $fields == ''
                    ? 'p.' . $field_name
                    : ', p.' . $field_name
                ;
            }
        }
        foreach ($a_group_fields as $field_name) {
            $fields .= ', g.' . $field_name;
        }


        if (is_numeric($people_id)) {
            $where          = "p.people_id = :people_id";
            $a_where_values = [':people_id' => $people_id];
        }
        else {
            $where          = "p.login_id = :login_id";
            $a_where_values = [':login_id' => $people_id];
        }
        $sql = "
            SELECT DISTINCT $fields
            FROM {$this->db_table} as p
            JOIN {$this->db_table}_group_map as pgm
                USING (people_id)
            JOIN {$this->db_prefix}groups as g
                USING (group_id)
            WHERE {$where}
            ORDER BY g.group_auth_level DESC, g.group_name ASC
        ";

        $this->logIt("Select User: {$sql}", LOG_OFF, $meth . __LINE__);
        $this->logIt("a_where_values: " . var_export($a_where_values, true), LOG_OFF, $meth . __LINE__);
        $a_people = $this->o_db->search($sql, $a_where_values);
        $this->logIt("a_people: " . var_export($a_people, true), LOG_OFF, $meth);
        if (isset($a_people[0]) && is_array($a_people[0])) {
            if (($a_people[0]['people_id'] == $people_id) || ($a_people[0]['login_id'] == $people_id)) {
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
                    if ($a_group['group_id'] == $previous_group) {
                        unset($a_groups[$key]);
                    }
                    else {
                        $previous_group = $a_group['group_id'];
                    }
                }
                $a_person = $a_people[0];
                unset($a_person['group_id']);
                unset($a_person['group_name']);
                unset($a_person['group_description']);
                unset($a_person['group_auth_level']);
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
     * Reads the people in the group provided.
     * @param int $group_id
     * @return bool|mixed
     */
    public function readByGroup($group_id = -1)
    {
        $meth = __METHOD__ . '.';
        $a_people_fields = $this->o_db->selectDbColumns($this->db_prefix . 'people');
        if (empty($a_people_fields)) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            return false;
        }
        $a_group_fields = $this->o_db->selectDbColumns($this->db_prefix . 'groups');
        if (is_numeric($group_id)) {
            $group_and = 'g.group_id = :group_id';
            $a_values = ['group_id' => $group_id, 'is_active' => 1];
        }
        else {
            $group_and = 'g.group_name = :group_name';
            $a_values = ['group_name' => $group_id, 'is_active' => 1];
        }
        $fields = '';
        foreach ($a_people_fields as $field_name) {
            if ($field_name != 'password') {
                $fields .= $fields == ''
                    ? 'p.' . $field_name
                    : ', p.' . $field_name
                ;
            }
        }
        foreach ($a_group_fields as $field_name) {
            $fields .= ', g.' . $field_name;
        }
        $sql =<<<SQL
SELECT $fields
FROM {$this->db_prefix}people as p
JOIN {$this->db_prefix}people_group_map pgm
  ON p.people_id = pgm.people_id
JOIN {$this->db_prefix}groups as g
  ON (
        g.group_id = pgm.group_id
    AND $group_and
    )
WHERE p.is_active = :is_active
SQL;
        $this->logIt("SQL: " . $sql, LOG_OFF, $meth . __LINE__);
        $log_message = 'values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_results = $this->o_db->search($sql, $a_values);
        if ($a_results === false) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
        }
        return $a_results;
    }

    /**
     * Saves the person.
     * If the values contain a value of people_id, person is updated
     * Else it is a new person.
     * @param array $a_person values to save
     * @return mixed, people_id or false
     */
    public function savePerson(array $a_person = array())
    {
        if (!Arrays::hasRequiredKeys($a_person, ['login_id', 'password'])) {
            return false;
        }
        if (!isset($a_person['people_id']) || $a_person['people_id'] == '') { // New User
            $a_required_keys = array(
                'login_id',
                'real_name',
                'password'
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
                        default:
                            break;
                    }
                }
            }
            $a_person['password'] = password_hash($a_person['password'], PASSWORD_DEFAULT);
            $a_groups = $this->makeGroupIdArray($a_person['groups']);
            if ($this->o_db->startTransaction()) {
                $a_ids = $this->o_people->create($a_person);
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
                'password'
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
                        default:
                            break;
                    }
                }
            }
            $a_person['password'] = password_hash($a_person['password'], PASSWORD_DEFAULT);
            $a_groups = $this->makeGroupIdArray($a_person['groups']);
            $a_pg_values = $this->makePgmArray($a_person['people_id'], $a_groups);
            if ($a_pg_values != array()) {
                if ($this->o_db->startTransaction()) {
                    if ($this->o_people->update($a_person)) {
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

    ### Utility Functions ###
    /**
     * Returns an array used in the creation of people group map records.
     * @param array $group_id
     * @param       $group_name
     * @return array
     */
    public function makeGroupIdArray($group_id = array(), $group_name = '')
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
     * Returns an array mapping a person to the group(s) specified.
     * @param string $people_id
     * @param array  $a_groups
     * @return array
     */
    public function makePgmArray($people_id = '', array $a_groups = array())
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

}
