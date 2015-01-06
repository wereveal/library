<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file ConfigAdminController.php
 *  @ingroup library core
 *  @namespace Ritc/Library/Controllers
 *  @class ConfigAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.2
 *  @date 2014-11-17 14:01:13
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.2 - changed to use the new Di class - 11/17/2014 wer
 *      v1.0.1 - Adjusted to match file name change - 11/13/2014 wer
 *      v1.0.0 - Initial version - 04/02/2014 wer
 *  </pre>
 *  @todo Add the session validation setup
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Models\ConfigModel;
use Ritc\Library\Views\ConfigAdminView;
use Ritc\Library\Services\Di;

class ConfigAdminController extends Base implements ControllerInterface
{
    private $o_model;
    private $o_router;
    private $o_session;
    private $o_view;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $o_db            = $o_di->get('db');
        $this->o_model   = new ConfigModel($o_db);
        $this->o_view    = new ConfigAdminView($o_di);
        $this->o_session = $o_di->get('session');
        $this->o_router  = $o_di->get('router');

    }
    /**
     *  Renders the html based on the route requested.
     *  @return string html to be displayed.
    **/
    public function render()
    {
        $main_action = isset($a_actions['action3']) ? $a_actions['action3'] : '';
        $form_action = isset($a_values['form_action']) ? $a_values['form_action'] : '';
        // Make sure this is a good session
        if ($main_action == 'modify' || $main_action == 'save' || $main_action == 'delete') {
            if ($this->o_session->isNotValidSession($a_values, true)) {
                $main_action = '';
                $this->o_session->clear();
                $this->o_session->setSessionVars();
            }
        }
        // Make sure only valid routes are followed
        switch ($main_action) {
            case 'modify':
                switch ($form_action) {
                    case 'verify':
                        $main_action = 'verify';
                        break;
                    case 'update':
                        $main_action = 'update';
                        break;
                    default:
                        $main_action = '';
                }
                break;
            case 'save':
                if ($form_action == 'save_new') {
                    $main_action = 'save_new';
                }
                else {
                    $main_action = '';
                }
                break;
            case 'delete':
                if ($form_action == 'delete') {
                    $main_action = 'delete';
                }
                else {
                    $main_action = '';
                }
                break;
            default:
                $main_action = '';
        }

        switch ($main_action) {
            case 'save_new':
                // save the record
                $a_config = $a_values['config'];
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
                $a_config = $a_values['config'];
                $results = $this->o_model->update($a_config);
                if ($results === false) {
                    $a_message = array('type' => 'failure', 'message' => 'Could not update the configuration.');
                }
                else {
                    $a_message = array('type' => 'success', 'message' => 'Success!');
                }
                return $this->o_view->renderConfigs($a_message);
            case 'verify':
                return $this->o_view->renderVerify($a_values);
            case 'delete':
                // delete the record
                $results = $this->o_model->delete($a_values['config_id']);
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
    /**
     * @param Session $o_session
     */
    public function setSession(Session $o_session)
    {
        $this->o_session = $o_session;
    }
    public function getSession()
    {
        return $this->o_session;
    }
}
