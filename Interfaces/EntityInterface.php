<?php
namespace Ritc\Library\Interfaces;

interface EntityInterface
{
    /**
     *  returns an array of the properties
     *  @return array
    **/
    public function getAllProperties();
    /**
     *  Sets the values of all the entity properties.
     *  @param array $a_entity e.g., array('id'=>'', 'password'=>'', 'uid'=>'', 'gid'=>'', 'dir'=>'')
     *  @return bool success or failure
    **/
    public function setAllProperties(array $a_entity = array());
}
