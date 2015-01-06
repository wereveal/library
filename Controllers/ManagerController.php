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
 *  @version 1.0.1β
 *  @date 2014-11-17 14:08:21
 *  @note A file in RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1β - Set up to render a basic landing page - 01/06/2015 wer
 *      v1.0.0β - changed to use IOC                    - 11/17/2014 wer
 *      v1.0.0α - Initial version                       - 11/14/2014 wer
 *  </pre>
 * @TODO Test
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\AccessHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Views\ManagerView;
use Ritc\Library\Services\Di;

class ManagerController extends Base implements ControllerInterface
{
    private $o_di;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_di = $o_di;
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
    }

    /**
     * Main router and puker outer.
     * @return string
     */
    public function render()
    {
        $o_router      = $this->o_di->get('router');
        $o_view        = new ManagerView($this->o_di);
        $o_access      = new AccessHelper($this->o_di);
        $a_route_parts = $o_router->getRouteParts();
        $route_action  = $a_route_parts['route_action'];
        $a_post        = $o_router->getPost();
        if ($o_access->isLoggedIn() === false && $route_action != 'verifyLogin') {
            $route_action = 'login';
        }
        switch ($route_action)
        {
            case 'verifyLogin':
                if ($o_access->login($a_post) !== false) {
                    $html = $o_view->renderLandingPage();
                }
                else {
                    $login_id = isset($a_post['login_id']) ? $a_post['login_id'] : '';
                    $html = $o_view->renderLoginForm($login_id, 'Please Try Again');
                }
                break;
            case 'landing':
                $html = $o_view->renderLandingPage();
                break;
            case '':
            case 'login':
            default:
                $html = $o_view->renderLoginForm();
        }
        return $html;
    }
}
