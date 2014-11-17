<?php
/**
 *  @brief The main Controller for the manager.
 *  @details It is expected that this class will be extended by the manager controller of the app
 *      in which it resides. However, it is written to work as is.
 *  @file ManagerController.php
 *  @ingroup library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class ManagerController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1ß
 *  @date 2014-11-17 14:08:21
 *  @note A file in RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1ß - changed to use IOC                    - 11/17/2014 wer
 *      v1.0.0ß - Initial version                       - 11/14/2014 wer
 *  </pre>
 * @TODO Session Control
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Views\ManagerView;
use Ritc\Library\Services\Di;

class ManagerController extends Base implements ControllerInterface
{
    private   $a_actions;
    protected $o_db;
    protected $o_session;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_session = $o_di->get('session');
        $this->o_db      = $o_di->get('db');
        $this->o_route   = $o_di->get('route');
        $this->a_actions = $a_actions;
    }

    public function render()
    {
        $o_view = new ManagerView($this->o_db);
        $route_method = $this->a_actions['route_method'];
        $route_action = $this->a_actions['route_action'];
        $a_route_args = $this->a_actions['args'];
        switch ($route_method)
        {
            case 'temp':
                switch ($route_action) {
                    default:
                        $html = $o_view->renderTempPage($a_route_args);
                }
                break;
            case '':
            default:
                $html = $o_view->renderLandingPage();
        }
        return $html;
    }
}
