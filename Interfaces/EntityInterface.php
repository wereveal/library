<?php
/**
 *  @brief Class used to set up entity classes.
 *  @file EntityInterface.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Interfaces
 *  @class EntityInterface
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-01-30 14:18:05
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 initial versioning 01/30/2014 wer
 *  </pre>
**/
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
