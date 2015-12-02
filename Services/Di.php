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
 *  @version   1.0.0
 *  @date      2015-09-03 12:29:11
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - it works, not sure why it wasn't out of beta   - 09/03/2015 wer
 *               Removed abstract Base as it wasn't being used
 *      v1.0.0β1 - initial version                              - 11/17/2014 wer
**/
namespace Ritc\Library\Services;

use Ritc\Library\Abstracts\Base;

class Di
{
    private $a_objects = array();

    public function set($object_name = '', $object)
    {
        if (!is_object($object) || $object_name == '') {
            return false;
        }
        $this->a_objects[$object_name] = $object;
        return true;
    }
    public function get($object_name = '')
    {
        if ($object_name != '' && isset($this->a_objects[$object_name]) && is_object($this->a_objects[$object_name])) {
            return $this->a_objects[$object_name];
        }
        return false;
    }
}
