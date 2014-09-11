<?php
/**
 *  @brief A basic entity class Groups table.
 *  @file GroupsEntity.php
 *  @ingroup library entities
 *  @namespace Ritc/Library/Entities
 *  @class GroupsEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2014-09-11 13:32:11
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - Initial version 09/11/2014 wer
 *  </pre>
 *  @note Create the _groups table. Replace dbPrefix with db_prefix
 *  <pre>
 *  CREATE TABLE `dbPrefix_groups` (
 *    `group_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `group_name` varchar(40) NOT NULL,
 *    `group_description` text NOT NULL,
 *    PRIMARY KEY (`group_id`),
 *    UNIQUE KEY `group_name` (`group_name`)
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 *  INSERT INTO `dbPrefix_groups` (`group_id`, `group_name`, `group_description`)
 *  VALUES (1, 'SuperAdmin', 'The group for super administrators');
 *  </pre>
 *  @todo Everything
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class GroupsEntity implements EntityInterface
{
    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array();
    }
    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
    {
        return true;
    }
}
