<?php
/**
 *  @brief A basic entity class for the Article table.
 *  @file RolesEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc/Library/Entities
 *  @class RolesEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2014-09-11 13:35:59
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - Initial version 09/11/2014 wer
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
