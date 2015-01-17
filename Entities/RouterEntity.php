<?php
/**
 *  SQL
 *  MySQL sql
    CREATE TABLE `dbPrefix_routes` (
      `route_id` int(11) NOT NULL AUTO_INCREMENT,
      `route_path` varchar(255) NOT NULL,
      `route_class` varchar(128) NOT NULL,
      `route_method` varchar(64) DEFAULT NULL,
      `route_action` varchar(255) NOT NULL,
      `route_default` tinyint(1) DEFAULT 1
      PRIMARY KEY (`router_id`),
      UNIQUE KEY `uri_path` (`uri_path``)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
**/

namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class RouterEntity implements EntityInterface
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
