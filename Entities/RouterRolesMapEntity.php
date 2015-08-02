<?php
/**
 * @brief Entity to map the Router to the Roles.
 * @file RouterRolesMapEntity.php
 * @ingroup ritc_library entities
 * @namespace Ritc\Library\Entities
 * @author William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0ß1
 * @date 2015-08-01 13:33:57
 * @note <pre><b>Change Log</b>
 *       v1.0.0ß1 - Initial version                    - 08/01/2015 wer
 *       </pre>
 * @note Be sure to replace '{dbPrefix}' with the db prefix<pre>
 * MySQL
CREATE TABLE `{$dbPrefix}routes_roles_map` (
    `rrm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `route_id` int(11) NOT NULL DEFAULT '0',
    `role_id` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`rrm_id`),
    UNIQUE KEY `rrm_key` (`route_id`,`role_id`),
    KEY `role_id` (`role_id`),
    CONSTRAINT `{$dbPrefix}routes_roles_map_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `ftp_roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `{$dbPrefix}routes_roles_map_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `ftp_routes` (`route_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 * PostgreSQL
CREATE SEQUENCE rrm_id_seq;
CREATE TABLE {$dbPrefix}routes_roles_map (
    rrm_id integer DEFAULT nextval('rrm_id_swq'::regclass) NOT NULL,
    route_id integer NOT NULL DEFAULT 0,
    role_id integer NOT NULL DEFAULT 0
);
ALTER TABLE ONLY {$dbPrefix}routes_roles_map
    ADD CONSTRAINT {$dbPrefix}routes_roles_map_pkey PRIMARY KEY (rrm_id);
ALTER TABLE `dbPrefix_user_group_map`
    ADD CONSTRAINT `{$dbPrefix}routes_roles_map_ibfk_1` FOREIGN KEY (`people_id`) REFERENCES `dbPrefix_users` (`people_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `{$dbPrefix}routes_roles_map_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `dbPrefix_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

 * </pre>
 */

namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class RouterRolesMapEntity implements EntityInterface
{
    private $rrm_id;
    private $route_id;
    private $role_id;

    public function getAllProperties()
    {
        return [
            'rrm_id' => $this->rrm_id,
            'route_id' => $this->route_id,
            'role_id' => $this->role_id
        ];
    }

    /**
     * @return mixed
     */
    public function getRrmId()
    {
        return $this->rrm_id;
    }

    /**
     * @param mixed $rrm_id
     */
    public function setRrmId($rrm_id)
    {
        $this->rrm_id = $rrm_id;
    }

    /**
     * @return mixed
     */
    public function getRouteId()
    {
        return $this->route_id;
    }

    /**
     * @param mixed $route_id
     */
    public function setRouteId($route_id)
    {
        $this->route_id = $route_id;
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

    public function setAllProperties(array $a_entity = array())
    {
        $a_defaults = [
            'rrm_id'   => -1,
            'route_id' => -1,
            'role_id'  => -1
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
}