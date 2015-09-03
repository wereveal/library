<?php
/**
 * @brief Entity to map the Router to the Roles.
 * @file RouterGroupMapEntity.php
 * @ingroup ritc_library entities
 * @namespace Ritc\Library\Entities
 * @class RouterGroupMapEntity
 * @author William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0β1
 * @date 2015-09-03 12:47:56
 * @note <pre><b>Change Log</b>
 *       v1.0.0β1 - Initial version                             - 09/03/2015 wer
 *       </pre>
 * @note Be sure to replace '{$dbPrefix}' with the db prefix<pre>
 * MySQL
CREATE TABLE `{$dbPrefix}routes_group_map` (
    `rgm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `route_id` int(11) NOT NULL DEFAULT '0',
    `group_id` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`rgm_id`),
    UNIQUE KEY `rgm_key` (`route_id`,`group_id`),
    KEY `group_id` (`group_id`),
    CONSTRAINT `{$dbPrefix}routes_group_map_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `{$dbPrefix}groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `{$dbPrefix}routes_group_map_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `{$dbPrefix}routes` (`route_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 * PostgreSQL
CREATE SEQUENCE rgm_id_seq;
CREATE TABLE {$dbPrefix}routes_group_map (
    rgm_id integer DEFAULT nextval('rgm_id_seq'::regclass) NOT NULL,
    route_id integer NOT NULL DEFAULT 0,
    group_id integer NOT NULL DEFAULT 0
);
ALTER TABLE ONLY {$dbPrefix}routes_group_map
    ADD CONSTRAINT {$dbPrefix}routes_group_map_pkey PRIMARY KEY (rgm_id);
ALTER TABLE `{$dbPrefix}routes_group_map`
    ADD CONSTRAINT {$dbPrefix}routes_group_map_ibfk_1 FOREIGN KEY (route_id) REFERENCES {$dbPrefix}routes (people_id) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT {$dbPrefix}routes_group_map_ibfk_2 FOREIGN KEY (group_id) REFERENCES {$dbPrefix}groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE;

 * INSERT
INSERT INTO ftp_routes_group_map (rgm_id, route_id, group_id)
VALUES
(1,1,1),
(2,1,2),
(3,2,1),
(4,2,2),
(5,3,1),
(6,3,2),
(7,4,1),
(8,4,2),
(9,5,1),
(10,5,2),
(11,6,1),
(12,6,2),
(13,7,1),
(14,7,2),
(15,8,1),
(16,8,2),
(17,9,1),
(18,9,2),
(19,10,1),
(20,10,2),
(21,11,1),
(22,11,2),
(23,12,1),
(24,12,2),
(25,13,1),
(26,13,2),
(27,14,1),
(28,14,2),
(29,15,1),
(30,15,2),
(31,16,1),
(32,16,2),
(33,17,1),
(34,17,2),
(35,18,1),
(36,18,2),
(37,19,1),
(38,19,2),
(39,20,1),
(40,20,2),
(41,21,1),
(42,21,2),
(43,22,1),
(44,22,2),
(45,23,1),
(46,23,2),
(47,24,1),
(48,24,2),
(49,25,1),
(50,25,2),
(51,26,1),
(52,26,2),
(53,27,1),
(54,27,2);

 * </pre>
 */

namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class RouterGroupMapEntity implements EntityInterface
{
    private $rgm_id;
    private $route_id;
    private $group_id;

    public function getAllProperties()
    {
        return [
            'rgm_id'   => $this->rgm_id,
            'route_id' => $this->route_id,
            'group_id' => $this->group_id
        ];
    }

    /**
     * @return mixed
     */
    public function getRrmId()
    {
        return $this->rgm_id;
    }

    /**
     * @param mixed $rgm_id
     */
    public function setRrmId($rgm_id)
    {
        $this->rgm_id = $rgm_id;
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
        return $this->group_id;
    }

    /**
     * @param mixed $group_id
     */
    public function setRoleId($group_id)
    {
        $this->group_id = $group_id;
    }

    public function setAllProperties(array $a_entity = array())
    {
        $a_defaults = [
            'rgm_id'   => -1,
            'route_id' => -1,
            'group_id' => -1
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
