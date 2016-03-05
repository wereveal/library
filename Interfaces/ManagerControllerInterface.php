<?php
/**
 *  @brief     Class used to set up controller classes in the manager.
 *  @ingroup   ritc_library lib_interfaces
 *  @file      ManagerControllerInterface.php
 *  @namespace Ritc\Library\Interfaces
 *  @class     ManagerControllerInterface
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0
 *  @date      2015-01-11 11:25:07
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - initial version                                  - 01/11/2015 wer
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

interface ManagerControllerInterface
{
    public function render();
    public function save();
    public function update();
    public function verifyDelete();
    public function delete();
}
