<?php
/**
 *  @brief     Controller for the Routes to Group Admin page.
 *  @detail    Allows one to map which routes have specific group access.
 *             If you are not in the group, you can't go there.
 *  @ingroup   ritc_library lib_controllers
 *  @file      Ritc/Library/Controllers/RoutesGroupAdminController.php
 *  @namespace Ritc\Library\Controllers
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0ß1
 *  @date      2015-08-04 04:25:11
 *  @pre       The route to this controller has to already be in the database
 *             and should not be able to be deleted.
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version           - 08/04/2015 wer
 *  </pre>
 *  @TODO everything
 **/

namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ManagerControllerInterface;

/**
 * Class RoutesGroupAdminController.
 * @class   RoutesGroupAdminController
 * @package Ritc\Library\Controllers
 */
class RoutesGroupAdminController implements ManagerControllerInterface
{
    /**
     * @return string
     */
    public function render()
    {
        return '';
    }

    /**
     * @return bool
     */
    public function save()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function update()
    {
        return false;
    }

    /**
     * @return string
     */
    public function verifyDelete()
    {
        return '';
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return false;
    }
}
