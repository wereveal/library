<?php
/**
 *  @brief A basic entity class for the Group Role Map table.
 *  @file GroupRoleMapEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc/Library/Entities
 *  @class GroupRoleMapEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β
 *  @date 2015-07-29 11:38:19
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β - changed to a mapping between group and role - 07/29/2015 wer
 *                Users now can only belong to multiple groups, the groups determine their role.
 *      v0.1.0  - Initial version                             - 09/11/2014 wer
 *  </pre>
 *  @note Be sure to replace '{dbPrefix}' with the db prefix<pre>
 *  CREATE TABLE `{dbPrefix}group_role_map` (
 *    `grm_id` int(11) NOT NULL,
 *    `group_id` int(11) NOT NULL,
 *    `role_id` int(11) NOT NULL,
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 *
 *  ALTER TABLE `{dbPrefix}group_role_map`
 *    ADD PRIMARY KEY (`grm_id`),
 *    ADD UNIQUE KEY `group_role` (`group_id`,`role_id`),
 *    ADD KEY `role_id` (`role_id`),
 *    ADD KEY `group_id` (`group_id`);
 *
 *  ALTER TABLE `{dbPrefix}group_group_map`
 *  MODIFY `ugm_id` int(11) NOT NULL AUTO_INCREMENT;
 *
 *  ALTER TABLE `{dbPrefix}group_role_map`
 *    ADD CONSTRAINT `{dbPrefix}group_role_map_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `{dbPrefix}users` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 *    ADD CONSTRAINT `{dbPrefix}group_role_map_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `{dbPrefix}roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;
 *
 *  PostgreSQL
 *  CREATE SEQUENCE grm_id_seq;
 *  CREATE TABLE {dbPrefix}group_role_map (
 *      grm_id integer DEFAULT nextval('grm_id_seq'::regclass) NOT NULL,
 *      group_id integer NOT NULL,
 *      role_id integer NOT NULL
 *  );
 *  ALTER TABLE ONLY {dbPrefix}group_role_map
 *      ADD CONSTRAINT {dbPrefix}group_role_map_pkey PRIMARY KEY (grm_id);
 *  CREATE INDEX {dbPrefix}group_role_map_role_id_idx ON {dbPrefix}group_role_map USING btree (role_id);
 *  CREATE INDEX {dbPrefix}group_role_map_group_id_idx ON {dbPrefix}group_role_map USING btree (group_id);
 *  ALTER TABLE ONLY {dbPrefix}group_role_map
 *      ADD CONSTRAINT {dbPrefix}group_role_map_ibfk_1 FOREIGN KEY (group_id) REFERENCES {dbPrefix}users(group_id) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
 *  ALTER TABLE ONLY {dbPrefix}group_role_map
 *      ADD CONSTRAINT {dbPrefix}group_role_map_ibfk_2 FOREIGN KEY (role_id) REFERENCES {dbPrefix}roles(role_id) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
 *
 *  INSERT INTO {dbPrefix}group_role_map (group_id, role_id)
 *  VALUES (1, 1);
 *  </pre>
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
