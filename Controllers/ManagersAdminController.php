<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file ManagersAdminController.php
 *  @ingroup library core
 *  @namespace Ritc/Library/Controllers
 *  @class ManagersAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.3β
 *  @date 2015-01-06 12:14:23
 *  @note A file in Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.3β - Realized this is nowhere near done            - 01/06/2015 wer
 *               This code was copied from somewhere else and
 *               not modified to fit the need.
 *      v1.0.2β - refactoring of namespaces                     - 12/05/2014 wer
 *      v1.0.1β - Adjusted to match file name change            - 11/13/2014 wer
 *      v1.0.0β - Initial version                               - 04/02/2014 wer
 *  </pre>
 *  @todo everything - too many things have changed to not go over every method
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\AccessHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Views\UserAdminView;

class ManagersAdminController extends Base implements ControllerInterface
{
    private $a_route_parts;
    private $a_post_values;
    private $form_action;
    private $main_action;
    private $o_di;
    private $o_router;
    private $o_view;
    private $o_session;
    private $route_action;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_di          = $o_di;
        $this->o_view        = new UserAdminView($o_di);
        $this->o_session     = $o_di->get('session');
        $this->o_router      = $o_di->get('router');
        $this->a_route_parts = $this->o_router->getRouteParts();
        $this->route_action  = $this->a_route_parts['route_action'];
        $this->form_action   = $this->a_route_parts['form_action'];
        $this->a_post_values = $this->o_router->getPost();
        $this->isSessionOk();
        $this->setMainAction();
    }
    /**
     *  Routes the code to the appropriate methods and classes. Returns a string.
     *  @return string html to be displayed.
    **/
    public function render()
    {
        switch ($this->main_action) {
            case 'login':
                header("Location: " . SITE_URL);
                break;
            case 'create':
                // save the record
                $a_config = $this->a_post_values['config'];
                $this->logit(
                    'config values before create config' . var_export($a_config, TRUE),
                    LOG_OFF,
                    __METHOD__ . '.' . __LINE__
                );
                $results = $this->o_model->create($a_config);
                if ($results === false) {
                    $a_message = array('type' => 'failure', 'message' => 'Could not save the configuration values.');
                }
                else {
                    $a_message = array('type' => 'success', 'message' => 'Success!');
                }
                return $this->o_view->renderConfigs($a_message);
            case 'update':
                // save the record
                $a_config = $this->a_post_values['config'];
                $results = $this->o_model->update($a_config);
                if ($results === false) {
                    $a_message = array('type' => 'failure', 'message' => 'Could not update the configuration.');
                }
                else {
                    $a_message = array('type' => 'success', 'message' => 'Success!');
                }
                return $this->o_view->renderConfigs($a_message);
            case 'verify':
                return $this->o_view->renderVerify($this->a_post_values);
            case 'delete':
                // delete the record
                $results = $this->o_model->delete($this->a_post_values['config_id']);
                // $results = false;
                if ($results === false) {
                    $a_message = array('type' => 'failure', 'message' => 'Could not delete the configuration.');
                }
                else {
                    $a_message = array('type' => 'success', 'message' => 'Success!');
                }
                return $this->o_view->renderConfigs($a_message);
            default:
                return $this->o_view->renderConfigs();
        }
    }
    public function renderGroups()
    {
        return '';
    }
    public function renderRoles()
    {
        return '';
    }

    /**
     * Sets the class property $main_action based on the route action and the form action.
     * Kind of of kludge but it seems to do what I want.
     * @return null
     */
    private function setMainAction()
    {
        switch ($this->route_action) {
            case 'modify':
                switch ($this->form_action) {
                    case 'verify':
                        $this->main_action = 'verify';
                        break;
                    case 'update':
                        $this->main_action = 'update';
                        break;
                    default:
                        $this->main_action = '';
                }
                break;
            case 'save':
                if ($this->form_action == 'save_new') {
                    $this->main_action = 'create';
                }
                else {
                    $this->main_action = '';
                }
                break;
            case 'delete':
                if ($this->form_action == 'delete') {
                    $this->main_action = 'delete';
                }
                else {
                    $this->main_action = '';
                }
                break;
            case 'login':
                $this->main_action = 'login';
                break;
            default:
                $this->main_action = '';
        }
    }

    /**
     * Checks to make sure we have a valid session going and the user is logged in.
     * @return bool
     */
    private function isSessionOk()
    {
        $o_access = new AccessHelper($this->o_di);
        if ($this->route_action    == 'modify'
            || $this->route_action == 'save'
            || $this->route_action == 'delete'
        ) {
            if ($this->o_session->isNotValidSession($this->a_post_values, true)) {
                $bail = true;
            }
            else {
                $bail = false;
            }
        }
        elseif (!$o_access->isLoggedIn()) {
            $bail = true;
        }
        else {
            $bail = false;
        }
        if ($bail) {
            $this->route_action = 'login';
            $this->o_session->clear();
            $this->o_session->setSessionVars();
            return false;
        }
        return true;
    }
}
