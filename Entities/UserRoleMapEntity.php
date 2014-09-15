<?php
/**
 *  @brief A basic entity class for the User Role table.
 *  @file UserRoleMapEntity.php
 *  @ingroup library entities
 *  @namespace Ritc/Library/Entities
 *  @class UserRoleMapEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2014-09-11 13:49:00
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - Initial version 09/11/2014 wer
 *  </pre>
 *  @note Be sure to replace 'dbPrefix' with the db prefix<pre>
 *  CREATE TABLE `dbPrefix_user_role_map` (
 *    `urm_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `user_id` int(11) NOT NULL,
 *    `role_id` int(11) NOT NULL,
 *    PRIMARY KEY (`urm_id`),
 *    UNIQUE KEY `user_role` (`user_id`,`role_id`),
 *    KEY `role_id` (`role_id`),
 *    KEY `user_id` (`user_id`)
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 *  INSERT INTO `dbPrefix_user_role_map` (`urm_id`, `user_id`, `role_id`)
 *  VALUES (1, 1, 1);
 *  ALTER TABLE `dbPrefix_user_role_map`
 *    ADD CONSTRAINT `dbPrefix_user_role_map_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `dbPrefix_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 *    ADD CONSTRAINT `dbPrefix_user_role_map_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `dbPrefix_roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;
 *  </pre>
 *  @todo Most Everything
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class UserRoleMapEntity implements EntityInterface
{
    private $urm_id;
    private $user_id;
    private $role_id;
    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'urm_id'  => $this->urm_id,
            'user_id' => $this->user_id,
            'role_id' => $this->role_id
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
            'urm_id'  => 0,
            'user_id' => 0,
            'role_id' => 0
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
}