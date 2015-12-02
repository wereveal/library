<?php
/**
 *  @brief     A basic entity class for the Group Role Map table.
 *  @file      GroupRoleMapEntity.php
 *  @ingroup   ritc_library entities
 *  @namespace Ritc\Library\Entities
 *  @class     GroupRoleMapEntity
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0β
 *  @date      2015-07-29 11:38:19
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β - changed to a mapping between group and role - 07/29/2015 wer
 *                Users now can only belong to multiple groups, the groups determine their role.
 *      v0.1.0  - Initial version                             - 09/11/2014 wer
 *  </pre>
 *  @note  <b>SQL for table<b><pre>
 *      MySQL      - resources/sql/mysql/group_role_map_mysql.sql
 *      PostgreSQL - resources/sql/postgresql/group_role_map_pg.sql</pre>
 *
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class GroupRoleMapEntity implements EntityInterface
{
    private $grm_id;
    private $group_id;
    private $role_id;
    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'grm_id'   => $this->grm_id,
            'group_id' => $this->group_id,
            'role_id'  => $this->role_id
        );
    }

    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
    {
        $a_defaults = array(
            'grm_id'   => 0,
            'group_id' => 0,
            'role_id'  => 0
        );
        foreach ($a_defaults as $key_name => $default_value) {
            if (array_key_exists($key_name, $a_entity)) {
                $this->$key_name = $a_entity[$key_name];
            }
            else {
                $this->$key_name = $default_value;
            }
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getGrmId()
    {
        return $this->grm_id;
    }

    /**
     * @param mixed $grm_id
     */
    public function setGrmId($grm_id)
    {
        $this->grm_id = $grm_id;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @param mixed $group_id
     */
    public function setGroupId($group_id)
    {
        $this->group_id = $group_id;
    }

    /**
     * @return mixed
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * @param mixed $role_id
     */
    public function setRoleId($role_id)
    {
        $this->role_id = $role_id;
    }

}
