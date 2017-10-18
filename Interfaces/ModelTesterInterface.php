<?php
/**
 * @brief     Interface used to set up model tester classes.
 * @detail    Model classes should be implementing the ModelInterface to match.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/ModelTesterInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2017-10-18 12:10:55
 * @note <b>Change Log</b>
 * - v1.0.0 initial versioning                                  - 2017-10-18 wer
*/
namespace Ritc\Library\Interfaces;

/**
* Interface ModelTesterInterface
* @class ModelTesterInterface
* @package Ritc\Library\Interfaces
*/
interface ModelTesterInterface {
    /**
     * Tests the create method.
     * @return string
     */
    public function createTester();

    /**
     * Tests the read method.
     * @return string
     */
    public function readTester();

    /**
     * Tests the update method.
     * @return string
     */
    public function updateTester();

    /**
     * Tests the delete method.
     * @return string
     */
    public function deleteTester();
}
