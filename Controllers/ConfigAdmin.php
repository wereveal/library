<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file ConfigAdmin.php
 *  @ingroup library core
 *  @namespace Ritc/Library/Controllers
 *  @class ConfigAdmin
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-04-02 13:04:04
 *  @note A file in Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - Initial version 04/02/2014 wer
 *  </pre>
 *  @todo Add the session validation setup
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Core\Elog;
use Ritc\Library\Core\Session;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Models\ConfigAdminModel;
use Ritc\Library\Views\ConfigAdminView;

class ConfigAdmin implements ControllerInterface
{
    private $o_elog;
    private $o_config;
    private $o_view;
    private $o_session;

    public function __construct(Session $o_session = '')
    {
        $this->o_elog    = Elog::start();
        $this->o_config  = new ConfigAdminModel();
        $this->o_view    = new ConfigAdminView();
        $this->o_session = is_object($o_session) ? $o_session : Session::start();

    }
    /**
     *  Routes the code to the appropriate methods and classes. Returns a string.
     *  @param array $a_actions optional, the actions derived from the URL/Form
     *  @param array $a_values optional, the values from a form
     *  @return string html to be displayed.
    **/
    public function route(array $a_actions = array(), array $a_values = array())
    {
        $main_action = isset($a_actions['action3']) ? $a_actions['action3'] : '';
        $form_action = isset($a_values['form_action']) ? $a_values['form_action'] : '';
        // Make sure this is a good session
        if ($main_action == 'modify' || $main_action == 'save' || $main_action == 'delete') {
            if ($this->o_session->isNotValidSession($a_values, true)) {
                $main_action = '';
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
                $this->o_elog->write('config values before create config' . var_export($a_config, TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
                $results = $this->o_config->create($a_config);
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
                $results = $this->o_config->update($a_config);
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
                $results = $this->o_config->delete($a_values['config_id']);
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
