<?php
/**
 * @brief     A basic entity class for the Article table.
 * @ingroup   lib_entities
 * @file      Ritc/Library/Entities/PeopleGroupMapEntity.php
 * @namespace Ritc\Library\Entities
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2015-07-29 11:43:02
 * @note <b>SQL for table<b><pre>
 * - MySQL      - resources/sql/mysql/people_group_map_mysql.sql
 * - PostgreSQL - resources/sql/postgresql/people_group_map_pg.sql</pre>
 * @note <b>Change Log</b>
 * - v1.0.0 - Finished        - 07/29/2015 wer
 * - v0.1.0 - Initial version - 09/11/2014 wer
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class PeopleGroupMapEntity
 * @class   PeopleGroupMapEntity
 * @package Ritc\Library\Entities
 */
class PeopleGroupMapEntity implements EntityInterface
{
    /** @var int */
    private $pgm_id;
    /** @var int */
    private $people_id;
    /** @var int */
    private $group_id;

    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'pgm_id'    => $this->pgm_id,
            'people_id' => $this->people_id,
            'group_id'  => $this->group_id
        );
    }

    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
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
     * @param integer $group_id
     */
    public function setGroupId($group_id = -1)
    {
        $this->group_id = $group_id;
    }

    /**
     * @return integer
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @param integer $pgm_id
     */
    public function setPgmId($pgm_id = -1)
    {
        $this->pgm_id = $pgm_id;
    }

    /**
     * @return integer
     */
    public function getPgmId()
    {
        return $this->pgm_id;
    }

    /**
     * @param integer $people_id
     */
    public function setPeopleId($people_id = -1)
    {
        $this->people_id = $people_id;
    }

    /**
     * @return integer
     */
    public function getPeopleId()
    {
        return $this->people_id;
    }
}
