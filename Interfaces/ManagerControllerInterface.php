<?php
/**
 * @brief     Class used to set up controller classes in the manager.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/ManagerControllerInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.0
 * @date      2017-01-14 09:32:39
 * @note <b>Change Log</b>
 * - v2.0.0 - changed name of method render to route to reflect intended purpose    - 2017-01-14 wer
 * - v1.0.0 - initial version                                                       - 01/11/2015 wer
 * @todo refactor - rename interface to LibraryControllerInterface or ConfigControllerInterface
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
     * Main method used to route the page to the appropriate controller/view/model.
     * @return string
     */
    public function route();

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
