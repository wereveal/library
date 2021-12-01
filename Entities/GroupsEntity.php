<?php
/**
 * Class GroupsEntity
 * @package Ritc_Library
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class GroupsEntity.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2021-11-26 16:18:21
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-26 wer
 * - v1.0.0 - finished                                          - 07/29/2015 wer
 * - v0.1.0 - Initial version                                   - 09/11/2014 wer
 */
class GroupsEntity implements EntityInterface
{
    /** @var int $group_id entity */
    private int $group_id;
    /** @var string entity */
    private string $group_name;
    /** @var string entity */
    private string $group_description;

    /**
     * Gets all the entity properties.
     *
     * @return array
     */
    public function getAllProperties():array
    {
        return [
            'group_id'          => $this->group_id,
            'group_name'        => $this->group_name,
            'group_description' => $this->group_description
        ];
    }

    /**
     * Sets all the properties for the entity in one step.
     *
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array()):bool
    {
        $a_defaults = array(
            'group_id'           => 0,
            'group_name'         => '',
            'group_description'  => ''
        );
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

    /**
     * @return int
     */
    public function getGroupId():int
    {
        return $this->group_id;
    }

    /**
     * @param mixed $group_id
     */
    public function setGroupId(mixed $group_id):void
    {
        $this->group_id = $group_id;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->group_name;
    }

    /**
     * @param mixed $group_name
     */
    public function setGroupName(mixed $group_name):void
    {
        $this->group_name = $group_name;
    }

    /**
     * @return string
     */
    public function getGroupDescription(): string
    {
        return $this->group_description;
    }

    /**
     * @param mixed $group_description
     */
    public function setGroupDescription(mixed $group_description):void
    {
        $this->group_description = $group_description;
    }

}
