<?php
namespace Ritc\Library\Interfaces;

/**
 * Interface ControllerInterface
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2017-01-14 09:31:45
 * ## Change Log
 * - v2.0.0 - change method name from render to route to reflect its intended purpose   - 2017-01-14 wer
 * - v1.1.0 - changed to match the change to DI/IOC in the app                          - 11/15/2014 wer
 * - v1.0.1 - changed router to render                                                  - 10/31/2014 wer
 * - v1.0.0 - initial versioning                                                        - 01/30/2014 wer
 */
interface ControllerInterface
{
    /**
     * Main method to route to the appropriate controller/view/model
     * @return mixed
     */
    public function route();
}
