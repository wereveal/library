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
use Ritc\Library\Controllers\ConfigAdminController;
use Ritc\Library\Controllers\RouterAdminController;
use Ritc\Library\Controllers\UserAdminController;
use Ritc\Library\Helper\AccessHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Views\ManagerView;
use Ritc\Library\Services\Di;

class ManagerController extends Base implements ControllerInterface
{
    private $a_route_parts;
    private $a_post_values;
    private $form_action;
    private $main_action;
    private $o_access;
    private $o_di;
    private $o_manager_view;
    private $o_router;
    private $route_method;
    private $route_action;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_di = $o_di;
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
        $this->setPrivateProperties();
        $this->o_di           = $o_di;
        $this->o_router       = $o_di->get('router');
        $this->a_route_parts  = $this->o_router->getRouteParts();
        $this->route_action   = $this->a_route_parts['route_action'];
        $this->route_method   = $this->a_route_parts['route_method'];
        $this->form_action    = $this->a_route_parts['form_action'];
        $this->a_post_values  = $this->o_router->getPost();
        $this->o_access       = new AccessHelper($this->o_di);
        $this->o_manager_view = new ManagerView($this->o_di);
    }

    /**
     * Default page for the manager and login.
     * @return string
     */
    public function render()
    {
        if ($this->isLoggedIn() === false) {
            $this->route_action = 'login';
        }
        switch ($this->route_method) {
            case 'renderConfigAdmin':
                $html = $this->renderConfigAdmin();
            case 'renderRouterAdmin':
                $html = $this->renderRouterAdmin();
            case 'renderUserAdmin':
                $html = $this->renderUserAdmin();
            case 'render':
            case '':
            default:
                switch ($this->route_action) {
                    case 'verifyLogin':
                        if ($this->o_access->login($a_post) !== false) {
                            $html = $this->o_manager_view->renderLandingPage();
                        }
                        else {
                            $login_id = isset($a_post['login_id']) ? $a_post['login_id'] : '';
                            $html = $this->o_manager_view->renderLoginForm($login_id, 'Please Try Again');
                        }
                        break;
                    case 'landing':
                        $html = $this->o_manager_view->renderLandingPage();
                        break;
                    case '':
                    case 'login':
                    default:
                        $html = $this->o_manager_view->renderLoginForm();
                }
            // end default
        }
        return $html;
    }
    public function renderConfigAdmin()
    {
        if ($this->isLoggedIn() === false) {
            return $this->o_manager_view->renderLoginForm();
        }
        $o_config_admin = new ConfigAdminController($this->o_di);
        return $o_config_admin->render();
    }
    public function renderRouterAdmin()
    {
        if ($this->isLoggedIn() === false) {
            return $this->o_manager_view->renderLoginForm();
        }
        $o_router_admin = new RouterAdminController($this->o_di);
        return $o_router_admin->render();
    }
    public function renderUserAdmin()
    {
        if ($this->isLoggedIn() === false) {
            return $this->o_manager_view->renderLoginForm();
        }
        $o_user_admin = new UserAdminController($this->o_di);
        return $o_user_admin->render();
    }
    private function isLoggedIn()
    {
        if ($this->o_access->isLoggedIn() === false && $this->route_action != 'verifyLogin') {
            return false;
        }
        return true;
    }
}
