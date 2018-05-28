<?php
/**
 * Class RoutesGroupMapEntity
 * @package Ritc_Library
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class RoutesGroupMapEntity - Entity to map the Router to the Groups.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-beta.1
 * @date    2015-09-03 12:47:56
 * @change_log
 *      v1.0.0-beta.1 - Initial version                             - 09/03/2015 wer
 */
class RoutesGroupMapEntity implements EntityInterface
{
    /** @var int $rgm_id */
    private $rgm_id;
    /** @var int $route_id */
    private $route_id;
    /** @var int $group_id */
    private $group_id;

    /**
     * @return array
     */
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
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @param mixed $group_id
     */
    public function setGroupId($group_id)
    {
        $this->group_id = $group_id;
    }

    /**
     * @param array $a_entity
     * @return bool
     */
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
