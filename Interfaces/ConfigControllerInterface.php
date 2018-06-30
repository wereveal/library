<?php
/**
 * Interface ConfigControllerInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface for manager controllers.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2018-06-06 11:19:39
 * @change_log
 * - v1.0.0 - initial version                                                       - 2018-06-06 wer
 */
interface ConfigControllerInterface
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
