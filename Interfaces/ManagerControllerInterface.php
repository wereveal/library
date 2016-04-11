<?php
/**
 * @brief     Class used to set up controller classes in the manager.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/ManagerControllerInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0+2
 * @date      2016-04-11 08:52:52
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
     * @return mixed
     */
    public function render();

    /**
     * Method for saving data.
     * @return mixed
     */
    public function save();

    /**
     * Method for updating data.
     * @return mixed
     */
    public function update();

    /**
     * Method to display the verify delete form.
     * @return mixed
     */
    public function verifyDelete();

    /**
     * Method to delete data.
     * @return mixed
     */
    public function delete();
}
