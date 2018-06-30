<?php
/**
 * Interface ManagerControllerInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface for manager controllers.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2017-01-14 09:32:39
 * @change_log
 * - v2.0.0 - changed name of method render to route to reflect intended purpose    - 2017-01-14 wer
 * - v1.0.0 - initial version                                                       - 01/11/2015 wer
 */
interface ManagerControllerInterface
{
    /**
     * Main method used to route the page to the appropriate controller/view/model.
     *
     * @return string
     */
    public function route():string;

    /**
     * Method for saving data.
     *
     * @return string
     */
    public function save():string;

    /**
     * Method for updating data.
     *
     * @return string
     */
    public function update():string;

    /**
     * Method to display the verify delete form.
     *
     * @return string
     */
    public function verifyDelete():string;

    /**
     * Method to delete data.
     *
     * @return string
     */
    public function delete():string;
}
