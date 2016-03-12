<?php
/**
 * @brief     A basic entity class for the Article table.
 * @file      RolesEntity.php
 * @ingroup   ritc_library entities
 * @namespace Ritc\Library\Entities
 * @class     RolesEntity
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2015-07-29 11:43:50
 * @note <b>Change Log</b>
 * - v1.0.0 - Finished        - 07/29/2015 wer
 * - v0.1.0 - Initial version - 09/11/2014 wer
 * @note <b>SQL for table<b><pre>
 * - MySQL      - resources/sql/mysql/roles_mysql.sql
 * - PostgreSQL - resources/sql/postgresql/roles_pg.sql</pre>
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class RolesEntity implements EntityInterface
{
    private $role_id;
    private $role_name;
    private $role_description;
    private $role_level;

    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'role_id'          => $this->role_id,
            'role_name'        => $this->role_name,
            'role_description' => $this->role_description,
            'role_level'       => $this->role_level

        );
    }

    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
    {
        $a_default_values = array(
            'role_id'          => 0,
            'role_name'        => '',
            'role_description' => '',
            'role_level'       => 0

        );
        foreach ($a_default_values as $key_name => $default_value) {
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

    /**
     * @return mixed
     */
    public function getRoleName()
    {
        return $this->role_name;
    }

    /**
     * @param mixed $role_name
     */
    public function setRoleName($role_name)
    {
        $this->role_name = $role_name;
    }

    /**
     * @return mixed
     */
    public function getRoleDescription()
    {
        return $this->role_description;
    }

    /**
     * @param mixed $role_description
     */
    public function setRoleDescription($role_description)
    {
        $this->role_description = $role_description;
    }

    /**
     * @return mixed
     */
    public function getRoleLevel()
    {
        return $this->role_level;
    }

    /**
     * @param mixed $role_level
     */
    public function setRoleLevel($role_level)
    {
        $this->role_level = $role_level;
    }

}
