<?php
/**
 * @brief Entity to map the Router to the Roles.
 * @file RoutesRolesMapEntity.php
 * @ingroup ritc_library entities
 * @namespace Ritc\Library\Entities
 * @author William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0ß1
 * @date 2015-08-01 13:33:57
 * @note <pre><b>Change Log</b>
 *       v1.0.0ß1 - Initial version                    - 08/01/2015 wer
 *       </pre>
 *  @note <b>SQL for table<b><pre>
 *      MySQL      - resources/sql/mysql/routes_roles_map_mysql.sql
 *      PostgreSQL - resources/sql/postgresql/routes_roles_map_pg.sql</pre>
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
