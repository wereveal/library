<?php
/**
 * @brief     Controller for the Routes to Group Admin page.
 * @detail    Allows one to map which routes have specific group access.
 *            If you are not in the group, you can't go there.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/RoutesGroupController.phpnamespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2015-08-04 04:25:11
 * @pre       The route to this controller has to already be in the database
 *            and should not be able to be deleted.
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.0 - Initial version                                       - 08/04/2015 wer
 * @TODO everything
 */

namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ManagerControllerInterface;

/**
 * Class RoutesGroupController.
 * @class   RoutesGroupController
 * @package Ritc\Library\Controllers
 */
class RoutesGroupController implements ManagerControllerInterface
{
    /**
     * @return string
     */
    public function route()
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
