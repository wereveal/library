<?php
/**
 *  @brief     Manages Dependency injection / Inversion of Control for the site.
 *  @details   It is expected that this will be initialized in the setup file at
 *             the very begining. All other services needed to be used
 *             throughout the app then get added to it.
 *             This is real basic. You put an instanced service in and pull a service
 *             out. The service must have been instanced already.
 *  @ingroup   ritc_library services
 *  @file      Di.php
 *  @namespace Ritc\Library\Services
 *  @class     Access
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.1.0
 *  @date      2016-03-03 01:13:16
 *  @note <pre><b>Change Log</b>
 *      v1.1.0 - added function to return all objects           - 03/03/2016 wer
 *      v1.0.1 - removed unused use                             - 02/22/2016 wer
 *      v1.0.0 - it works, not sure why it wasn't out of beta   - 09/03/2015 wer
 *               Removed abstract Base as it wasn't being used
 *      v1.0.0β1 - initial version                              - 11/17/2014 wer
**/
namespace Ritc\Library\Services;

class Di
{
    /**
     * @var array
     */
    private $a_objects = array();

    /**
     * @param string $object_name
     * @param        $object
     * @return bool
     */
    public function set($object_name = '', $object)
    {
        if (!is_object($object) || $object_name == '') {
            return false;
        }
        $this->a_objects[$object_name] = $object;
        return true;
    }

    /**
     * @param string $object_name
     * @return bool
     */
    public function get($object_name = '')
    {
        if ($object_name != '' && isset($this->a_objects[$object_name]) && is_object($this->a_objects[$object_name])) {
            return $this->a_objects[$object_name];
        }
        return false;
    }

    /**
     * Returns the array of objects.
     * @return array
     */
    public function getObjects()
    {
        return $this->a_objects;
    }
}
