<?php
/**
 * Interface EntityInterface
 * @package RITC_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface EntityInterface
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2014-01-30 14:18:05
 * ## Change Log
 * - v1.0.0 initial version                      - 01/30/2014 wer
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
