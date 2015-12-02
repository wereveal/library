<?php
/**
 *  @brief     A basic entity class Groups table.
 *  @ingroup   ritc_library entities
 *  @file      GroupsEntity.php
 *  @namespace Ritc\Library\Entities
 *  @class     GroupsEntity
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0
 *  @date      2015-07-29 11:41:03
 *  @note <b>SQL for table<b><pre>
 *      MySQL      - resources/sql/mysql/groups_mysql.sql
 *      PostgreSQL - resource\sql/postgresql/groups_pg.sql</pre>
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - finished        - 07/29/2015 wer
 *      v0.1.0 - Initial version - 09/11/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class GroupsEntity implements EntityInterface
{
    private $group_id;
    private $group_name;
    private $group_description;

    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return [
            'group_id'          => $this->group_id,
            'group_name'        => $this->group_name,
            'group_description' => $this->group_description
        ];
    }
    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
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
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * @param mixed $group_name
     */
    public function setGroupName($group_name)
    {
        $this->group_name = $group_name;
    }

    /**
     * @return mixed
     */
    public function getGroupDescription()
    {
        return $this->group_description;
    }

    /**
     * @param mixed $group_description
     */
    public function setGroupDescription($group_description)
    {
        $this->group_description = $group_description;
    }

}
