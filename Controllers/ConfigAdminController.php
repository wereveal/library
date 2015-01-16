<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file ConfigAdminController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class ConfigAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.1.0
 *  @date 2015-01-16 11:48:35
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.1.0 - changed to implement ManagerControllerInterface - 01/16/2015 wer
 *               This class should only be called from the main
 *               manager controller which does session validation.
 *      v1.0.2 - changed to use the new Di class                 - 11/17/2014 wer
 *      v1.0.1 - Adjusted to match file name change              - 11/13/2014 wer
 *      v1.0.0 - Initial version                                 - 04/02/2014 wer
 *  </pre>
 *  @todo Add the session validation setup
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\ConfigModel;
use Ritc\Library\Views\ConfigAdminView;
use Ritc\Library\Services\Di;

class ConfigAdminController extends Base implements MangerControllerInterface
{
    private $a_post;
    private $o_di;
    private $o_model;
    private $o_view;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_di = $o_di;
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
    }
    /**
     *  Renders the html based on the route requested.
     *  @return string html to be displayed.
    **/
    public function render()
    {
        $o_db            = $this->o_di->get('db');
        $this->o_model   = new ConfigModel($o_db);
        $this->o_view    = new ConfigAdminView($this->o_di);
        $o_router        = $this->o_di->get('router');
        $a_config_parts  = $o_router->getRouteParts();
        $main_action     = $a_config_parts['route_action'];
        $form_action     = $a_config_parts['form_action'];
        $this->a_post    = $a_config_parts['post'];
        // Make sure only valid routes are followed
        switch ($main_action) {
            case 'modify':
                switch ($form_action) {
                    case 'verify':
                        return $this->verifyDelete();
                    case 'update':
                        return $this->update();
                    default:
                        $a_message = [
                            'message' => 'A problem occured. Please try again.',
                            'type'    => 'failure'
                        ];
                        return $this->o_view->renderConfigs($a_message);
                }
            case 'save':
                if ($form_action == 'save_new') {
                    return $this->save();
                }
                else {
                    $a_message = [
                        'message' => 'A problem occurred so the record could not be saved.',
                        'type'    => 'failure'
                    ];
                    return $this->o_view->renderConfigs($a_message);
                }
                break;
            case 'delete':
                if ($form_action == 'delete') {
                    return $this->delete();
                }
                else {
                    $a_message = [
                        'message' => 'A problem occurred so the record could not be deleted.',
                        'type'    => 'failure'
                    ];
                    return $this->o_view->renderConfigs($a_message);
                }
            default:
                return $this->o_view->renderConfigs();
        }
    }

    ### Required by Interface ###
    public function save()
    {

        $a_config = $this->a_post['config'];
        $results = $this->o_model->create($a_config);
        if ($results !== false) {
            $a_message = ['message' => 'Success!', 'type' => 'success'];
        }
        else {
            $a_message = [
                'message' => 'A Problem Has Occured. The new configuration could not be saved.',
                'type' => 'failure'
            ];
        }
        return $this->o_view->renderConfigs($a_message);
    }
    public function update()
    {
        $a_config = $this->a_post['config'];
        $results = $this->o_model->update($a_config);
        if ($results !== false) {
            $a_message = ['message' => 'Success!', 'type' => 'success'];
        }
        else {
            $a_message = [
                'message' => 'A Problem Has Occured. The route could not be updated.',
                'type' => 'failure'
            ];
        }
        return $this->o_view->renderConfigs($a_message);
    }
    /**
     *  Returns the html to display a form to verify the delete.
     *  Required by Interface.
     *  @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }
    public function delete()
    {
        $config_id = $this->a_post['config_id'];
        if ($config_id == -1 || $config_id == '') {
            $a_message = [
                'message' => 'A Problem Has Occured. The config record id was not provided.',
                'type' => 'error'
            ];
            return $this->o_view->renderConfigs($a_message);
        }
        $a_results = $this->o_model->delete($config_id);
        return $this->o_view->renderConfigs($a_results);
    }
}
