<?php
/**
 * Class PeopleGroupMapEntity
 * @package Ritc_Library
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class PeopleGroupMapEntity
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2021-11-26 16:23:18
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-26 wer
 * - v1.0.0 - Finished                                          - 07/29/2015 wer
 * - v0.1.0 - Initial version                                   - 09/11/2014 wer
 */
class PeopleGroupMapEntity implements EntityInterface
{
    /** @var int */
    private int $pgm_id;
    /** @var int */
    private int $people_id;
    /** @var int */
    private int $group_id;

    /**
     * Gets all the entity properties.
     *
     * @return array
     */
    public function getAllProperties():array
    {
        return array(
            'pgm_id'    => $this->pgm_id,
            'people_id' => $this->people_id,
            'group_id'  => $this->group_id
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
        $a_defaults = [
            'pgm_id'    => -1,
            'people_id' => -1,
            'group_id'  => -1
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

    /**
     * @param int $group_id
     */
    public function setGroupId(int $group_id = -1):void
    {
        $this->group_id = $group_id;
    }

    /**
     * @return int
     */
    public function getGroupId():int
    {
        return $this->group_id;
    }

    /**
     * @param int $pgm_id
     */
    public function setPgmId(int $pgm_id = -1):void
    {
        $this->pgm_id = $pgm_id;
    }

    /**
     * @return int
     */
    public function getPgmId():int
    {
        return $this->pgm_id;
    }

    /**
     * @param int $people_id
     */
    public function setPeopleId(int $people_id = -1):void
    {
        $this->people_id = $people_id;
    }

    /**
     * @return int
     */
    public function getPeopleId():int
    {
        return $this->people_id;
    }
}
