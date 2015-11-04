<?php
/**
 * @brief Entity to map the Router to the Roles.
 * @file RoutesGroupMapEntity.php
 * @ingroup ritc_library entities
 * @namespace Ritc\Library\Entities
 * @class RouterGroupMapEntity
 * @author William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0β1
 * @date 2015-09-03 12:47:56
 * @note <pre><b>Change Log</b>
 *       v1.0.0β1 - Initial version                             - 09/03/2015 wer
 *       </pre>
 *  @note <b>SQL for table<b><pre>
 *      MySQL      - resources/sql/mysql/routes_group_map_mysql.sql
 *      PostgreSQL - resources/sql/postgresql/routes_group_map_pg.sql</pre>
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
