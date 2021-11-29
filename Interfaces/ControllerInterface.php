<?php
/**
 * Interface ControllerInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface ControllerInterface
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.1
 * @date    2021-11-29 17:09:36
 * @change_log
 * - v2.0.1 - fixed return value of route, needed to be string                          - 2021-11-29 wer
 * - v2.0.0 - change method name from render to route to reflect its intended purpose   - 2017-01-14 wer
 * - v1.1.0 - changed to match the change to DI/IOC in the app                          - 11/15/2014 wer
 * - v1.0.1 - changed router to render                                                  - 10/31/2014 wer
 * - v1.0.0 - initial version                                                           - 01/30/2014 wer
 */
interface ControllerInterface
{
    /**
     * Main method to route to the appropriate controller/view/model
     * @return string
     */
    public function route(): string;
}
