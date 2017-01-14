<?php
/**
 * @brief     Class used to set up controller classes.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/ControllerInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.0
 * @date      2017-01-14 09:31:45
 * @note <b>Change Log</b>
 * - v2.0.0 - change method name from render to route to reflect its intended purpose   - 2017-01-14 wer
 * - v1.1.0 - changed to match the change to DI/IOC in the app                          - 11/15/2014 wer
 * - v1.0.1 - changed router to render                                                  - 10/31/2014 wer
 * - v1.0.0 - initial versioning                                                        - 01/30/2014 wer
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface ControllerInterface
 * @class ControllerInterface
 * @package Ritc\Library\Interfaces
 */
interface ControllerInterface
{
    /**
     * Main method to route to the appropriate controller/view/model
     * @return mixed
     */
    public function route();
}
