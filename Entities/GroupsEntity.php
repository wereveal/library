<?php
/**
 *  @brief A basic entity class Groups table.
 *  @file GroupsEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc/Library/Entities
 *  @class GroupsEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-07-29 11:41:03
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - finished        - 07/29/2015 wer
 *      v0.1.0 - Initial version - 09/11/2014 wer
 *  </pre>
 *  @note Create the {$dbPrefix}groups table. Replace {$dbPrefix} with db_prefix
 *  <pre>
 *  MySQL
CREATE TABLE `{$dbPrefix}groups` (
`group_id` int(11) NOT NULL AUTO_INCREMENT,
`group_name` varchar(40) NOT NULL,
`group_description` varchar(128) NOT NULL DEFAULT '',
PRIMARY KEY (`group_id`),
UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{$dbPrefix}groups` (`group_id`, `group_name`, `group_description`)
VALUES
(1,'SuperAdmin','The group for super administrators. There should be only a couple of these.'),
(2,'Managers','Most people accessing the manager should be in this group.'),
(3,'Editor','Editor for the CMS'),
(4,'Registered','The group for people that shouldn\'t have access to the manager.'),
(5,'Anonymous','Not logged in, possibly unregistered');
 *
 *
 *  PostgreSQL
CREATE SEQUENCE group_id_seq;
CREATE TABLE {$dbPrefix}groups (
    group_id integer DEFAULT nextval('group_id_seq'::regclass) NOT NULL,
    group_name character varying(40) NOT NULL,
    group_description character varying(128) NOT NULL
);
ALTER TABLE ONLY {$dbPrefix}groups
    ADD CONSTRAINT {$dbPrefix}groups_group_name_key UNIQUE (group_name);
ALTER TABLE ONLY {$dbPrefix}groups
    ADD CONSTRAINT {$dbPrefix}groups_pkey PRIMARY KEY (group_id);
INSERT INTO {$dbPrefix}groups (group_name, group_description)
VALUES
(1,'SuperAdmin','The group for super administrators. There should be only a couple of these.'),
(2,'Managers','Most people accessing the manager should be in this group.'),
(3,'Editor','Editor for the CMS'),
(4,'Registered','The group for people that shouldn\'t have access to the manager.'),
(5,'Anonymous','Not logged in, possibly unregistered');
 *  </pre>
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class GroupsEntity implements EntityInterface
{
    private $group_id;
    private $group_name;
    private $group_description;

    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return [
            'group_id'          => $this->group_id,
            'group_name'        => $this->group_name,
            'group_description' => $this->group_description
        ];
    }
    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
    {
        $a_defaults = array(
            'group_id'           => 0,
            'group_name'         => '',
            'group_description'  => ''
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
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * @param mixed $group_name
     */
    public function setGroupName($group_name)
    {
        $this->group_name = $group_name;
    }

    /**
     * @return mixed
     */
    public function getGroupDescription()
    {
        return $this->group_description;
    }

    /**
     * @param mixed $group_description
     */
    public function setGroupDescription($group_description)
    {
        $this->group_description = $group_description;
    }

}
