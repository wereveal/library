<?php
/**
 * @brief     Interface used to set up entity classes.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/EntityInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2014-01-30 14:18:05
 * @note <b>Change Log</b>
 * - v1.0.0 initial versioning 01/30/2014 wer
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface EntityInterface
 * @class EntityInterface
 * @package Ritc\Library\Interfaces
 */
interface EntityInterface
{
    /**
     * Returns an array of the properties.
     * @return array
     */
    public function getAllProperties();

    /**
     * Sets the values of all the entity properties.
     * @param array $a_entity e.g., array('id'=>'', 'password'=>'', 'uid'=>'', 'gid'=>'', 'dir'=>'')
     * @return bool success or failure
     */
    public function setAllProperties(array $a_entity = array());
}
