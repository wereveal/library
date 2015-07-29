<?php
/**
 *  @brief A basic entity class for the Article table.
 *  @file PeopleGroupMapEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc/Library/Entities
 *  @class PeopleGroupMapEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-07-29 11:43:02
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - Finished        - 07/29/2015 wer
 *      v0.1.0 - Initial version - 09/11/2014 wer
 *  </pre>
 *  @note Be sure to replace '{dbPrefix}' with the db prefix<pre>
 *  MySQL
 *  CREATE TABLE `{dbPrefix}user_group_map` (
 *    `pgm_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `people_id` int(11) NOT NULL,
 *    `group_id` int(11) NOT NULL,
 *    PRIMARY KEY (`pgm_id`),
 *    UNIQUE KEY `user_group` (`people_id`,`group_id`),
 *    KEY `group_id` (`group_id`),
 *    KEY `people_id` (`people_id`)
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 *
 *  PostgreSQL
 *  CREATE SEQUENCE pgm_id_seq;
 *  CREATE TABLE {dbPrefix}user_group_map (
 *      pgm_id integer DEFAULT nextval('pgm_id_seq'::regclass) NOT NULL,
 *      people_id integer NOT NULL,
 *      group_id integer NOT NULL
 *  );
 *  ALTER TABLE ONLY {dbPrefix}user_group_map
 *      ADD CONSTRAINT {dbPrefix}user_group_map_pkey PRIMARY KEY (pgm_id);
 *
 *  INSERT INTO {dbPrefix}user_group_map (people_id, group_id)
 *  VALUES (1, 1);
 *
 *  ALTER TABLE `dbPrefix_user_group_map`
 *    ADD CONSTRAINT `dbPrefix_user_group_map_ibfk_1` FOREIGN KEY (`people_id`) REFERENCES `dbPrefix_users` (`people_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 *    ADD CONSTRAINT `dbPrefix_user_group_map_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `dbPrefix_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;
 *  </pre>
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class PeopleGroupMapEntity implements EntityInterface
{
    private $pgm_id;
    private $people_id;
    private $group_id;
    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'pgm_id'    => $this->pgm_id,
            'people_id' => $this->people_id,
            'group_id'  => $this->group_id
        );
    }

    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
    {
        $a_defaults = [
            'pgm_id'    => -1,
            'people_id' => -1,
            'group_id'  => -1
        ];
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
     *  @param integer $pgm_id
     */
    public function setPgmId($pgm_id = -1)
    {
        $this->pgm_id = $pgm_id;
    }

    /**
     *  @return integer
     */
    public function getPgmId()
    {
        return $this->pgm_id;
    }

    /**
     *  @param integer $people_id
     */
    public function setPeopleId($people_id = -1)
    {
        $this->people_id = $people_id;
    }

    /**
     * @return integer
     */
    public function getPeopleId()
    {
        return $this->people_id;
    }

}
