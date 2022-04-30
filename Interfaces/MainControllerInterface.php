<?php
/**
 * Interface MainControllerInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface for the main controller for an app.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-05-01 16:29:40
 * @change_log
 * - v1.0.0 - initial version                                                       - 2018-05-01 wer
 */
interface MainControllerInterface
{
    /**
     * Main method to route to the appropriate controller/view/model
     *
     * @return string
     */
    public function route():string;

    /**
     * Method to reset the route to / and display that page.
     *
     * For example:
     *   $this->o_router->setRouteParts('/');
     *   $o_controller = new HomeController($this->o_di);
     *   return $o_controller->route();
     * @return string
     */
    public function goHome():string;
}
