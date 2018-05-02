<?php
/**
 * @brief     Interface used to set up the main controller class.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/MainControllerInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2018-05-01 16:29:40
 * @note <b>Change Log</b>
 * - v1.0.0 - initial version                                                       - 2018-05-01 wer
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface MainControllerInterface
 * @class MainControllerInterface
 * @package Ritc\Library\Interfaces
 */
interface MainControllerInterface
{
    /**
     * Main method to route to the appropriate controller/view/model
     * @return string
     */
    public function route();

    /**
     * Method to reset the route to / and display that page.
     * For example:
     *   $this->o_router->setRouteParts('/');
     *   $o_controller = new HomeController($this->o_di);
     *   return $o_controller->route();
     * @return string
     */
    public function goHome();
}
