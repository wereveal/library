<?php
/**
 * Class RoutesEntity
 * @package Ritc_Library
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class RoutesEntity - Basic accessors for a routes entity.
 *
 * @author  William E Reveal
 * @version v2.0.0
 * @date    2021-11-26 16:24:50
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-26 wer
 * - v1.0.0 - Initial version                                   - 2015-10-06 wer
 */
class RoutesEntity implements EntityInterface
{
    /** @var int */
    private int $route_id;
    /** @var string */
    private string $route_path;
    /** @var string */
    private string $route_class;
    /** @var string */
    private string $route_method;
    /** @var string */
    private string $route_action;
    /** @var int */
    private int $route_immutable;

    /**
     * Gets all the entity properties.
     *
     * @return array
     */
    public function getAllProperties():array
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
     *
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array()):bool
    {
        $a_default_values = [
            'route_id'        => 0,
            'route_path'      => '',
            'route_class'     => '',
            'route_method'    => '',
            'route_action'    => '',
            'route_immutable' => 'true'
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
    public function getRouteId():int
    {
        return $this->route_id;
    }

    /**
     * @param int $route_id
     */
    public function setRouteId(int $route_id):void
    {
        $this->route_id = $route_id;
    }

    /**
     * @return string
     */
    public function getRoutePath():string
    {
        return $this->route_path;
    }

    /**
     * @param string $route_path
     */
    public function setRoutePath(string $route_path):void
    {
        $this->route_path = $route_path;
    }

    /**
     * @return string
     */
    public function getRouteClass():string
    {
        return $this->route_class;
    }

    /**
     * @param string $route_class
     */
    public function setRouteClass(string $route_class):void
    {
        $this->route_class = $route_class;
    }

    /**
     * @return string
     */
    public function getRouteMethod():string
    {
        return $this->route_method;
    }

    /**
     * @param string $route_method
     */
    public function setRouteMethod(string $route_method):void
    {
        $this->route_method = $route_method;
    }

    /**
     * @return string
     */
    public function getRouteAction():string
    {
        return $this->route_action;
    }

    /**
     * @param string $route_action
     */
    public function setRouteAction(string $route_action):void
    {
        $this->route_action = $route_action;
    }

    /**
     * @return int
     */
    public function getRouteDefault():int
    {
        return $this->route_immutable;
    }

    /**
     * @param int $route_immutable
     */
    public function setRouteDefault(int $route_immutable):void
    {
        $this->route_immutable = $route_immutable;
    }
}
