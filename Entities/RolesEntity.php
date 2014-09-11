<?php
/**
 *  @brief A basic entity class for the Article table.
 *  @file RolesEntity.php
 *  @ingroup library entities
 *  @namespace Ritc/Library/Entities
 *  @class RolesEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2014-09-11 13:35:59
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - Initial version 09/11/2014 wer
 *  </pre>
 *  @note <pre>
 *  CREATE TABLE `dbPrefix_roles` (
 *    `role_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `role_name` varchar(20) NOT NULL,
 *    `role_description` text NOT NULL,
 *    `role_level` int(11) NOT NULL DEFAULT '4',
 *    PRIMARY KEY (`role_id`),
 *    KEY `name` (`role_name`)
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
 *  INSERT INTO `ritc_roles` (`role_id`, `role_name`, `role_description`, `role_level`) VALUES
 *  (1, 'superadmin', 'Has Access to Everything.', 1),
 *  (2, 'admin', 'Has complete access to the administration area.', 2),
 *  (3, 'editor', 'Can add and modify records.', 3),
 *  (4, 'registered', 'Registered User', 4),
 *  (5, 'anonymous', 'Anonymous User', 5);
 *  </pre>
 *  @todo Everything
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class RolesEntity implements EntityInterface
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
