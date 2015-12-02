<?php
/**
 * @brief     The main Controller for the manager.
 * @file      ManagerController.php
 * @ingroup   ritc_library controllers
 * @namespace Ritc/Library/Controllers
 * @class     ManagerController
 * @author    William Reveal <bill@revealitconsulting.com>
 * @version   v1.0.1
 * @date      2015-12-01 21:41:39
 * @note      A file in RITC Library
 * @note <pre><b>Change Log</b>
 *      v1.0.1   - needed to change private to protected - 12/01/2015 wer
 *                 in order to extend this class.
 *      v1.0.0   - first working version                 - 11/27/2015 wer
 *      v1.0.0β8 - bug fixes                             - 11/18/2015 wer
 *      v1.0.0β7 - added page controller                 - 11/12/2015 wer
 *                 also fixed logic bugs.
 *      v1.0.0β6 - added tests controller                - 10/23/2015 wer
 *      v1.0.0β5 - working for groups                    - 09/25/2015 wer
 *      v1.0.0β4 - working for Roles                     - 01/28/2015 wer
 *      v1.0.0β3 - working for router and config         - 01/16/2015 wer
 *      v1.0.0β2 - Set up to render a basic landing page - 01/06/2015 wer
 *      v1.0.0β1 - changed to use IOC                    - 11/17/2014 wer
 *      v1.0.0α1 - Initial version                       - 11/14/2014 wer
 * </pre>
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\ManagerView;

class ManagerController implements ControllerInterface
{
    use LogitTraits;

    protected $a_route_parts;
    protected $a_post_values;
    protected $form_action;
    protected $o_auth;
    protected $o_di;
    protected $o_manager_view;
    protected $o_router;
    protected $o_session;
    protected $route_method;
    protected $route_action;

    public function __construct(Di $o_di)
    {
        $this->o_di           = $o_di;
        $this->o_router       = $o_di->get('router');
        $this->o_session      = $o_di->get('session');
        $this->a_route_parts  = $this->o_router->getRouterParts();
        $this->route_action   = $this->a_route_parts['route_action'];
        $this->route_method   = $this->a_route_parts['route_method'];
        $this->form_action    = $this->a_route_parts['form_action'];
        $this->a_post_values  = $this->a_route_parts['post'];
        $this->o_auth         = new AuthHelper($this->o_di);
        $this->o_manager_view = new ManagerView($this->o_di);
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
    }

    /**
     * Default page for the manager and login.
     * @return string
     */
    public function render()
    {
        $meth = __METHOD__ . '.';
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                switch ($this->route_action) {
                    case 'logout':
                        $this->o_auth->logout($_SESSION['login_id']);
                        header("Location: " . SITE_URL . '/manager/');
                        break;
                    default:
                        $this->logIt('Session: ' . var_export($_SESSION, TRUE), LOG_OFF, $meth . __LINE__);
                        return $this->o_manager_view->renderLandingPage();
                }
            }
        }
        if ($this->form_action == 'verifyLogin' || $this->route_action == 'verifyLogin') {
            $a_message = $this->verifyLogin();
            $this->logIt('Login Message: ' . var_export($a_message, TRUE), LOG_OFF, $meth . __LINE__);
            $this->logIt('Session after login: ' . var_export($_SESSION, TRUE), LOG_OFF, $meth . __LINE__);
            if ($a_message['type'] == 'success') {
                return $this->o_manager_view->renderLandingPage($a_message);
            }
            else {
                $login_id = isset($this->a_post_values['login_id'])
                    ? $this->a_post_values['login_id']
                    : '';
                return $this->renderLogin($login_id, $a_message);
            }
        }
        else {
            return $this->renderLogin();
        }
    }
    /**
     * Passes control over to the Constants Admin Controller.
     * @return string
     */
    public function renderConstantsAdmin()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $o_constants_admin = new ConstantsAdminController($this->o_di);
                return $o_constants_admin->render();
            }
        }
        $a_message = ViewHelper::warningMessage("Access Prohibited");
        return $this->renderLogin('', $a_message);
    }
    /**
     * Passes control over to the groups admin controller.
     * @return string
     */
    public function renderGroupsAdmin()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $o_groups_admin = new GroupsAdmimController($this->o_di);
                return $o_groups_admin->render();
            }
        }
        $a_message = ViewHelper::warningMessage("Access Prohibited");
        return $this->renderLogin('', $a_message);
    }
    /**
     *  Renders login form after resetting session.
     *  @param string $login_id
     *  @param array  $a_message optional e.g. ['message' => '', 'type' => 'info']
     *  @return string
     */
    private function renderLogin($login_id = '', array $a_message = array())
    {
        $this->o_session->resetSession();
        return $this->o_manager_view->renderLoginForm($login_id, $a_message);
    }
    /**
     *  Returns the html for the page admin.
     *  @return string
     */
    public function renderPageAdmin()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $o_page_admin = new PageAdminController($this->o_di);
                return $o_page_admin->render();
            }
        }
        $a_message = ViewHelper::warningMessage("Access Prohibited");
        return $this->renderLogin('', $a_message);
    }
    /**
     * Passes control over to the people admin controller.
     * @return string
     */
    public function renderPeopleAdmin()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $o_people_admin = new PeopleAdminController($this->o_di);
                return $o_people_admin->render();
            }
        }
        $a_message = ViewHelper::warningMessage("Access Prohibited");
        return $this->renderLogin('', $a_message);
    }
    /**
     * Passes control over to the router admin controller.
     * @return string
     */
    public function renderRoutesAdmin()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $o_router_admin = new RoutesAdminController($this->o_di);
                return $o_router_admin->render();
            }
        }
        $a_message = ViewHelper::warningMessage("Access Prohibited");
        return $this->renderLogin('', $a_message);
    }
    /**
     *  Passes control over to the tests admin controller.
     *  @return string
     */
    public function renderTestsAdmin()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $o_tests = new TestsAdminController($this->o_di);
                return $o_tests->render();
            }
        }
        $a_message = ViewHelper::warningMessage("Access Prohibited");
        return $this->renderLogin('', $a_message);
    }
    /**
     *  Authorizes the person and allows access or kicks them.
     *  @return bool
     */
    protected function verifyLogin()
    {
        $meth = __METHOD__ . '.';
        $a_results = $this->o_auth->login($this->a_post_values); // authentication part
        $this->logIt("Login Results: " . var_export($a_results, true), LOG_OFF, $meth . __LINE__);
        if ($a_results['is_logged_in'] == 1) {
            $this->o_session->setVar('login_id', $a_results['login_id']);
            $this->logIt('The Session: ' . var_export($_SESSION, TRUE), LOG_OFF, $meth . __LINE__);
            $this->logIt('Route Parts: ' . var_export($this->a_route_parts, TRUE), LOG_OFF, $meth . __LINE__);
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($a_results['people_id'], $min_auth_level)) { // authorization part
                return ViewHelper::successMessage('Success, you are now logged in!');
            }
        }
        /* well, apparently they weren't allowed access so kick em to the curb */
        if ($a_results['is_logged_in'] == 1) {
            $this->o_auth->logout($a_results['people_id']);
        }
        return isset($a_results['message'])
            ? ViewHelper::failureMessage($a_results['message'])
            : ViewHelper::failureMessage('Login Id or Password was incorrect. Please Try Again');
    }
}
