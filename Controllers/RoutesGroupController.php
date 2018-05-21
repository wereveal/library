<?php
/**
 * Class RoutesGroupController
 * @package RITC_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ManagerControllerInterface;

/**
 * Class RoutesGroupController - Controller for the Routes to Group Admin page.
 * @details Allows one to map which routes have specific group access.
 *          If you are not in the group, you can't go there.
 *          The route to this controller has to already be in the database
 *          and should not be able to be deleted.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2015-08-04 04:25:11
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial version                                       - 08/04/2015 wer
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
