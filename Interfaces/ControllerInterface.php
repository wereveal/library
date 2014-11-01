<?php
/**
 *  @brief Class used to set up controller classes.
 *  @file ControllerInterface.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Interfaces
 *  @class ControllerInterface
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.1
 *  @date 2014-10-31 19:26:05
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1 changed router to render - 10/31/2014 wer
 *      v1.0.0 initial versioning - 01/30/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

use Ritc\Library\Core\Session;

interface ControllerInterface
{
    public function render();
    public function setSession(Session $o_session);
    public function getSession();
}
