<?php
/**
 * @brief     Class used to set up controller classes in the manager.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/ManagerControllerInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0+3
 * @date      2016-04-15 12:14:33
 * @note <b>Change Log</b>
 * - v1.0.0 - initial version                                  - 01/11/2015 wer
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface ManagerControllerInterface
 * @class   ManagerControllerInterface
 * @package Ritc\Library\Interfaces
 */
interface ManagerControllerInterface
{
    /**
     * Main method used to render the page.
     * @return string
     */
    public function render();

    /**
     * Method for saving data.
     * @return string
     */
    public function save();

    /**
     * Method for updating data.
     * @return string
     */
    public function update();

    /**
     * Method to display the verify delete form.
     * @return string
     */
    public function verifyDelete();

    /**
     * Method to delete data.
     * @return string
     */
    public function delete();
}
