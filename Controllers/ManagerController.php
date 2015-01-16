<?php
/**
 *  @brief The main Controller for the manager.
 *  @details It is expected that this class will be extended by the manager controller of the app
 *      in which it resides. However, it is written to work as is.
 *  @file ManagerController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class ManagerController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β3
 *  @date 2015-01-16 12:12:43
 *  @note A file in RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β3 - working for router and config         - 01/16/2015 wer
 *      v1.0.0β2 - Set up to render a basic landing page - 01/06/2015 wer
 *      v1.0.0β1 - changed to use IOC                    - 11/17/2014 wer
 *      v1.0.0α1 - Initial version                       - 11/14/2014 wer
 *  </pre>
 * @TODO Test
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Views\ManagerView;
use Ritc\Library\Services\Di;

class ManagerController extends Base implements ControllerInterface
{
    private $a_route_parts;
    private $a_post_values;
    private $form_action;
    private $o_auth;
    private $o_di;
    private $o_manager_view;
    private $o_router;
    private $o_session;
    private $route_method;
    private $route_action;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
        $this->o_di           = $o_di;
        $this->o_router       = $o_di->get('router');
        $this->o_session      = $o_di->get('session');
        $this->a_route_parts  = $this->o_router->getRouteParts();
        $this->route_action   = $this->a_route_parts['route_action'];
        $this->route_method   = $this->a_route_parts['route_method'];
        $this->form_action    = $this->a_route_parts['form_action'];
        $this->a_post_values  = $this->a_route_parts['post'];
        $this->o_auth         = new AuthHelper($this->o_di);
        $this->o_manager_view = new ManagerView($this->o_di);
    }

    /**
     * Default page for the manager and login.
     * @return string
     */
    public function render()
    {
        if ($this->o_auth->isLoggedIn() === false && $this->route_action != 'verifyLogin') {
            $this->o_session->resetSession();
            $this->route_action = 'login';
        }
        switch ($this->route_action) {
            case 'verifyLogin':
                $a_results = $this->o_auth->login($this->a_post_values);
                $this->logIt("Login Results: " . var_export($a_results, true), LOG_ON, __METHOD__ . '.' . __LINE__);
                if ($a_results['is_logged_in'] == 1) {
                    $this->o_session->setVar('login_id', $this->a_post_values['login_id']);
                    $html = $this->o_manager_view->renderLandingPage();
                }
                else {
                    $login_id = isset($this->a_post_values['login_id']) ? $this->a_post_values['login_id'] : '';
                    $message  = isset($a_results['message'])
                        ? $a_results['message']
                        : 'Login Id or Password was incorrect. Please Try Again';
                    $html = $this->o_manager_view->renderLoginForm($login_id, $message);
                }
                break;
            case '':
            case 'landing':
                $html = $this->o_manager_view->renderLandingPage();
                break;
            case 'login':
            default:
                $html = $this->o_manager_view->renderLoginForm();
        }
        return $html;
    }
    public function renderConfigAdmin()
    {
        if ($this->o_auth->isLoggedIn() === false) {
            return $this->o_manager_view->renderLoginForm();
        }
        $o_config_admin = new ConfigAdminController($this->o_di);
        return $o_config_admin->render();
    }
    public function renderRouterAdmin()
    {
        if ($this->o_auth->isLoggedIn() === false) {
            return $this->o_manager_view->renderLoginForm();
        }
        $o_router_admin = new RouterAdminController($this->o_di);
        return $o_router_admin->render();
    }
    public function renderUserAdmin()
    {
        if ($this->o_auth->isLoggedIn() === false) {
            return $this->o_manager_view->renderLoginForm();
        }
        $o_user_admin = new ManagersAdminController($this->o_di);
        return $o_user_admin->render();
    }
    public function renderRolesAdmin()
    {
        // TODO write renderRolesAdmin method
        return '';
    }
    public function renderGroupsAdmin()
    {
        // TODO write renderGroupsAdmin method
        return '';
    }
    private function isLoggedIn()
    {
        if ($this->o_auth->isLoggedIn() === false && $this->route_action != 'verifyLogin') {
            return false;
        }
        return true;
    }
}
