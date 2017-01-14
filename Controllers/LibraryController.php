<?php
/**
 * @brief     The main Controller for the manager.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/LibraryController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   v2.1.0
 * @date      2016-04-11 10:13:40
 * @note <b>Change Log</b>
 * - v2.1.0   - Added UrlAdminController              - 2016-04-11 wer
 * - v2.0.0   - Renamed Class to be more specific     - 2016-03-31 wer
 * - v1.0.1   - needed to change private to protected - 12/01/2015 wer
 *              in order to extend this class.
 * - v1.0.0   - first working version                 - 11/27/2015 wer
 * - v1.0.0β8 - bug fixes                             - 11/18/2015 wer
 * - v1.0.0β7 - added page controller                 - 11/12/2015 wer
 *                also fixed logic bugs.
 * - v1.0.0β6 - added tests controller                - 10/23/2015 wer
 * - v1.0.0β5 - working for groups                    - 09/25/2015 wer
 * - v1.0.0β4 - working for Roles                     - 01/28/2015 wer
 * - v1.0.0β3 - working for router and config         - 01/16/2015 wer
 * - v1.0.0β2 - Set up to render a basic landing page - 01/06/2015 wer
 * - v1.0.0β1 - changed to use IOC                    - 11/17/2014 wer
 * - v1.0.0α1 - Initial version                       - 11/14/2014 wer
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\LibraryView;
use Ritc\Library\Views\NavigationAdminView;

/**
 * Class LibraryController.
 * @class LibraryController
 * @package Ritc\Library\Controllers
 */
class LibraryController implements ControllerInterface
{
    use LogitTraits;

    /** @var array */
    protected $a_route_parts;
    /** @var array */
    protected $a_post_values;
    /** @var string */
    protected $form_action;
    /** @var AuthHelper */
    protected $o_auth;
    /** @var Di */
    protected $o_di;
    /** @var LibraryView */
    protected $o_manager_view;
    /** @var Router */
    protected $o_router;
    /** @var Session */
    protected $o_session;
    /** @var string */
    protected $route_method;
    /** @var string */
    protected $route_action;

    /**
     * LibraryController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di           = $o_di;
        $this->o_router       = $o_di->get('router');
        $this->o_session      = $o_di->get('session');
        $this->a_route_parts  = $this->o_router->getRouteParts();
        $this->route_action   = $this->a_route_parts['route_action'];
        $this->route_method   = $this->a_route_parts['route_method'];
        $this->form_action    = $this->a_route_parts['form_action'];
        $this->a_post_values  = $this->a_route_parts['post'];
        $this->o_auth         = new AuthHelper($this->o_di);
        $this->o_manager_view = new LibraryView($this->o_di);
        if (!defined('LIB_TWIG_PREFIX')) {
            define('LIB_TWIG_PREFIX', 'lib_');
        }
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
    }

    /**
     * Default page for the library manager and login.
     * @return string
     */
    public function route()
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
     * Renders login form after resetting session.
     * @param string $login_id
     * @param array  $a_message optional e.g. ['message' => '', 'type' => 'info']
     * @return string
     */
    private function renderLogin($login_id = '', array $a_message = array())
    {
        $this->o_session->resetSession();
        return $this->o_manager_view->renderLoginForm($login_id, $a_message);
    }

    /**
     * Passes over control to the navigation manager controller.
     * @return string
     */
    public function renderNavigationAdmin()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $o_nav_admin = new NavigationAdminController($this->o_di);
                return $o_nav_admin->render();
            }
        }
        $a_message = ViewHelper::warningMessage("Access Prohibited");
        return $this->renderLogin('', $a_message);
    }

    /**
     * Returns the html for the page admin.
     * @return string
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
     * Passes control over to the tests admin controller.
     * @return string
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
     * Passes control over to the url admin controller.
     * @return string
     */
    public function renderUrlsAdmin()
    {
        if (isset($_SESSION['login_id']) && $_SESSION['login_id'] != '') {
            $min_auth_level = $this->a_route_parts['min_auth_level'];
            if ($this->o_auth->isAllowedAccess($_SESSION['login_id'], $min_auth_level)) {
                $o_urls_admin = new UrlsAdminController($this->o_di);
                return $o_urls_admin->render();
            }
        }
        $a_message = ViewHelper::warningMessage("You need to login with a valid usename and password.");
        return $this->renderLogin('', $a_message);
    }

    /**
     * Authorizes the person and allows access or kicks them.
     * @return bool
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
