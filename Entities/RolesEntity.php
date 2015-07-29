<?php
/**
 *  @brief A basic entity class for the Article table.
 *  @file RolesEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc/Library/Entities
 *  @class RolesEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-07-29 11:43:50
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - Finished        - 07/29/2015 wer
 *      v0.1.0 - Initial version - 09/11/2014 wer
 *  </pre>
 *  @note <pre>SQL for creating table
 *  MySQL
 *  CREATE TABLE `dbPrefix_roles` (
 *    `role_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `role_name` varchar(20) NOT NULL,
 *    `role_description` text NOT NULL,
 *    `role_level` int(11) NOT NULL DEFAULT '4',
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
 *  ALTER TABLE `dbPrefix_roles`
 *    ADD PRIMARY KEY (`role_id`),
 *    ADD UNIQUE KEY `rolename` (`role_name`)
 *
 *
 *  PostgreSQL
 *  CREATE SEQUENCE role_id_seq;
 *  CREATE TABLE {dbPrefix}roles (
 *      role_id integer DEFAULT nextval('role_id_seq'::regclass) NOT NULL,
 *      role_name character varying(40) NOT NULL,
 *      role_description text NOT NULL,
 *      role_level integer DEFAULT 4 NOT NULL
 *  );
 *  ALTER TABLE ONLY {dbPrefix}roles
 *      ADD CONSTRAINT {dbPrefix}roles_pkey PRIMARY KEY (role_id);
 *
 *  INSERT INTO ritc_roles (role_name, role_description, role_level) VALUES
 *  ('superadmin', 'Has Access to Everything.', 1),
 *  ('admin', 'Has complete access to the administration area.', 2),
 *  ('editor', 'Can add and modify records.', 3),
 *  ('registered', 'Registered User', 4),
 *  ('anonymous', 'Anonymous User', 5);
 *  </pre>
**/
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
