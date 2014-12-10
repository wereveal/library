<?php
/**
 *  @brief Class used to set up controller classes.
 *  @file ControllerInterface.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Interfaces
 *  @class ControllerInterface
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.1.0
 *  @date 2014-11-15 14:54:02
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.1.0 - changed to match the change to DI/IOC in the app - 11/15/2014 wer
 *      v1.0.1 changed router to render                           - 10/31/2014 wer
 *      v1.0.0 initial versioning                                 - 01/30/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

interface ControllerInterface
{
    public function render();
}
