<?php
/**
 *  @brief Class used to set up controller classes.
 *  @details has one required method, renderPage
 *  @file ControllerInterface.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Interfaces
 *  @class ControllerInterface
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-01-30 14:18:05
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 initial versioning 01/30/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

use Ritc\Library\Core\Session;

interface ControllerInterface
{
    public function router(array $a_actions = array(), array $a_values = array());
    public function setSession(Session $o_session);
    public function getSession();
}
