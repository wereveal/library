<?php
/**
 *  @brief Controller for the Router to Roles Admin page.
 *  @detail Allows one to map which routes have specific role access.
 *      If you don't have the role, you can't go there.
 *  @file RouterRolesAdminController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class RouterRolesAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0ß1
 *  @date 2015-08-04 04:25:11
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version           - 08/04/2015 wer
 *  </pre>
 *  @pre The route to this controller has to already be in the database
 *       and should not be able to be deleted.
 **/

namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\MangerControllerInterface;

class RouterRolesAdminController implements MangerControllerInterface
{

}
