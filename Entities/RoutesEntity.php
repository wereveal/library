<?php
/**
 *  @brief     Basic accessors for a routes entity.
 *  @ingroup   ritc_library lib_entities
 *  @file      RoutesEntity.php
 *  @namespace Ritc\Library\Entities
 *  @class     RoutesEntity
 *  @author    William E Reveal
 *  @version   1.0.0
 *  @date      2015-10-06 14:20:33
 *  @note <b>SQL for table<b><pre>
 *      MySQL      - resources/sql/mysql/routes_mysql.sql
 *      PostgreSQL - resources/sql/postgresql/routes_pg.sql</pre>
**/

namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class RoutesEntity implements EntityInterface
{
    private $route_id;
    private $route_path;
    private $route_class;
    private $route_method;
    private $route_action;
    private $route_immutable;

    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'route_id'        => $this->route_id,
            'route_path'      => $this->route_path,
            'route_class'     => $this->route_class,
            'route_method'    => $this->route_method,
            'route_action'    => $this->route_action,
            'route_immutable' => $this->route_immutable
        );
    }
    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
    {
        $a_default_values = [
            'route_id'       => 0,
            'route_path'     => '',
            'route_class'    => '',
            'route_method'   => '',
            'route_action'   => '',
            'route_immutable' => 1
        ];
        foreach ($a_default_values as $key_name => $default_value) {
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
     * @return int
     */
    public function getRouteId()
    {
        return $this->route_id;
    }

    /**
     * @param int $route_id
     */
    public function setRouteId($route_id)
    {
        $this->route_id = $route_id;
    }

    /**
     * @return string
     */
    public function getRoutePath()
    {
        return $this->route_path;
    }

    /**
     * @param string $route_path
     */
    public function setRoutePath($route_path)
    {
        $this->route_path = $route_path;
    }

    /**
     * @return string
     */
    public function getRouteClass()
    {
        return $this->route_class;
    }

    /**
     * @param string $route_class
     */
    public function setRouteClass($route_class)
    {
        $this->route_class = $route_class;
    }

    /**
     * @return string
     */
    public function getRouteMethod()
    {
        return $this->route_method;
    }

    /**
     * @param string $route_method
     */
    public function setRouteMethod($route_method)
    {
        $this->route_method = $route_method;
    }

    /**
     * @return string
     */
    public function getRouteAction()
    {
        return $this->route_action;
    }

    /**
     * @param string $route_action
     */
    public function setRouteAction($route_action)
    {
        $this->route_action = $route_action;
    }

    /**
     * @return int
     */
    public function getRouteDefault()
    {
        return $this->route_immutable;
    }

    /**
     * @param int $route_immutable
     */
    public function setRouteDefault($route_immutable)
    {
        $this->route_immutable = $route_immutable;
    }

}
