<?php
/**
 *  @brief A basic entity class for the Article table.
 *  @file UserGroupMapEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc/Library/Entities
 *  @class UserGroupMapEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2014-09-11 13:50:24
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - Initial version 09/11/2014 wer
 *  </pre>
 *  @note Be sure to replace '{dbPrefix}' with the db prefix<pre>
 *  MySQL
 *  CREATE TABLE `{dbPrefix}user_group_map` (
 *    `ugm_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `user_id` int(11) NOT NULL,
 *    `group_id` int(11) NOT NULL,
 *    PRIMARY KEY (`ugm_id`),
 *    UNIQUE KEY `user_group` (`user_id`,`group_id`),
 *    KEY `group_id` (`group_id`),
 *    KEY `user_id` (`user_id`)
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 *
 *  PostgreSQL
 *  CREATE SEQUENCE ugm_id_seq;
 *  CREATE TABLE {dbPrefix}user_group_map (
 *      ugm_id integer DEFAULT nextval('ugm_id_seq'::regclass) NOT NULL,
 *      user_id integer NOT NULL,
 *      group_id integer NOT NULL
 *  );
 *  ALTER TABLE ONLY {dbPrefix}user_group_map
 *      ADD CONSTRAINT {dbPrefix}user_group_map_pkey PRIMARY KEY (ugm_id);
 *
 *  INSERT INTO {dbPrefix}user_group_map (user_id, group_id)
 *  VALUES (1, 1);
 *
 *  ALTER TABLE `dbPrefix_user_group_map`
 *    ADD CONSTRAINT `dbPrefix_user_group_map_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `dbPrefix_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 *    ADD CONSTRAINT `dbPrefix_user_group_map_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `dbPrefix_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;
 *  </pre>
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class UserGroupMapEntity implements EntityInterface
{
    private $ugm_id;
    private $user_id;
    private $group_id;
    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'ugm_id'   => $this->ugm_id,
            'user_id'  => $this->user_id,
            'group_id' => $this->group_id
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
            'ugm_id'   => -1,
            'user_id'  => -1,
            'group_id' => -1
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
     *  @param integer $group_id
     */
    public function setGroupId($group_id = -1)
    {
        $this->group_id = $group_id;
    }

    /**
     * @return integer
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     *  @param integer $ugm_id
     */
    public function setUgmId($ugm_id = -1)
    {
        $this->ugm_id = $ugm_id;
    }

    /**
     *  @return integer
     */
    public function getUgmId()
    {
        return $this->ugm_id;
    }

    /**
     *  @param integer $user_id
     */
    public function setUserId($user_id = -1)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

}
