<?php
/**
 *  @brief Controller for the Roles Admin page.
 *  @file RolesAdmimController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class RolesAdmimController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-01-20 06:04:58
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version           - 01/20/2015 wer
 *  </pre>
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Views\RolesAdminView;

class RolesAdmimController extends Base implements MangerControllerInterface
{
    private $a_post;
    private $o_di;
    private $o_model;
    private $o_router;
    private $o_session;
    private $o_view;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_di      = $o_di;
        $o_db            = $o_di->get('db');
        $this->o_router  = $o_di->get('router');
        $this->o_session = $o_di->get('session');
        $this->o_model   = new RolesModel($o_db);
        $this->o_view    = new RolesAdminView($o_di);
    }
    public function render()
    {
        $a_route_parts = $this->o_router->getRouteParts();
        $main_action   = $a_route_parts['route_action'];
        $form_action   = $a_route_parts['form_action'];
        $this->a_post  = $a_route_parts['post'];
        if ($main_action == 'save' || $main_action == 'update' || $main_action == 'delete') {
            if ($this->o_session->isNotValidSession($this->a_post, true)) {
                header("Location: " . SITE_URL . '/manager/login/');
            }
        }
        switch ($main_action) {
            case 'save':
                return $this->save();
            case 'delete':
                return $this->delete();
            case 'update':
                if ($form_action == 'verify') {
                    return $this->verifyDelete();
                }
                elseif ($form_action == 'update') {
                    return $this->update();
                }
                else {
                    $a_message = [
                        'message' => 'A Problem Has Occured. Please Try Again.',
                        'type'    => 'failure'
                    ];
                    return $this->o_view->renderList($a_message);
                }
            case '':
            default:
                return $this->o_view->renderList();
        }
    }

    ### Required by Interface ###
    public function delete()
    {
        $role_id = $this->a_post['role_id'];
        if ($role_id == -1) {
            $a_message = ['message' => 'A Problem Has Occured. The role id was not provided.', 'type' => 'error'];
            return $this->o_view->renderList($a_message);
        }
        $a_results = $this->o_model->delete($role_id);
        return $this->o_view->renderList($a_results);
    }
    public function save()
    {
        $a_role = $this->a_post['roles'];
        $results = $this->o_model->create($a_role);
        if ($results) {
            $a_message = ['message' => 'Success!', 'type' => 'success'];
            return $this->o_view->renderList($a_message);
        }
        else {
            $a_message = [
                'message' => 'A Problem Has Occured. The new role could not be saved.',
                'type' => 'failure'
            ];
            return $this->o_view->renderList($a_message);
        }
    }
    public function update()
    {
        $a_role = $this->a_post['roles'];
        $results = $this->o_model->update($a_role);
        if ($results) {
            $a_message = ['message' => 'Success!', 'type' => 'success'];
            return $this->o_view->renderList($a_message);
        }
        else {
            $a_message = [
                'message' => 'A Problem Has Occured. The role could not be updated.',
                'type'    => 'failure'
            ];
            return $this->o_view->renderList($a_message);
        }
    }
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }

}