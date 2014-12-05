<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file UserAdminController.php
 *  @ingroup library core
 *  @namespace Ritc/Library/Controllers
 *  @class UserAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.2
 *  @date 2014-12-05 11:06:59
 *  @note A file in Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.2 - refactoring of namespaces          - 12/05/2014 wer
 *      v1.0.1 - Adjusted to match file name change - 11/13/2014 wer
 *      v1.0.0 - Initial version                    - 04/02/2014 wer
 *  </pre>
 *  @todo Add the session validation setup
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Session;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Views\UserAdminView;

class UserAdminController extends Base implements ControllerInterface
{
    private $o_model;
    private $o_view;
    private $o_session;

    public function __construct(Session $o_session, DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->o_view    = new UserAdminView($o_db);
        $this->o_session = $o_session;

    }
    /**
     *  Routes the code to the appropriate methods and classes. Returns a string.
     *  @param array $a_actions optional, the actions derived from the URL/Form
     *  @param array $a_values optional, the values from a form
     *  @return string html to be displayed.
    **/
    public function render(array $a_actions = array(), array $a_values = array())
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
