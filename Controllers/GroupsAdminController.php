<?php
/**
 *  @brief Controller for the Groups Admin page.
 *  @file GroupsAdmimController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class GroupsAdmimController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-01-28 15:17:59
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version                              - 01/28/2015 wer
 *  </pre>
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\GroupRoleMapModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\GroupsAdminView;

class GroupsAdmimController implements MangerControllerInterface
{
    use LogitTraits;

    private $a_post;
    private $o_di;
    private $o_model;
    private $o_router;
    private $o_session;
    private $o_view;

    public function __construct(Di $o_di)
    {
        $this->o_di      = $o_di;
        $o_db            = $o_di->get('db');
        $this->o_router  = $o_di->get('router');
        $this->o_session = $o_di->get('session');
        $this->o_model   = new GroupsModel($o_db);
        $this->o_grm     = new GroupRoleMapModel($o_db);
        $this->o_view    = new GroupsAdminView($o_di);
        $this->a_post    = $this->o_router->getPost();
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
            $this->o_grm->setElog($this->o_elog);
        }
    }
    public function render()
    {
        $a_route_parts = $this->o_router->getRouterParts();
        $main_action   = $a_route_parts['route_action'];
        $form_action   = $a_route_parts['form_action'];
        $url_action    = isset($a_route_parts['url_actions'][0])
            ? $a_route_parts['url_actions'][0]
            : '';
        if ($main_action == '' && $url_action != '') {
            $main_action = $url_action;
        }
        if ($main_action == 'save' || $main_action == 'update' || $main_action == 'delete') {
            if ($this->o_session->isNotValidSession($this->a_post, true)) {
                header("Location: " . SITE_URL . '/manager/login/');
            }
        }
        $this->logIt("Main Action: " . $main_action, LOG_OFF, __METHOD__);
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
                    $a_message = ViewHelper::errorMessage();
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
        $group_id = $this->a_post['group_id'];
        if ($group_id == -1) {
            $a_message = ViewHelper::errorMessage('An Error Has Occured. The group id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        $results = $this->o_model->deleteWithRelated($group_id);
        $a_message = $results
            ? ViewHelper::successMessage()
            : ViewHelper::failureMessage();
        return $this->o_view->renderList($a_message);
    }
    public function save()
    {
        $meth = __METHOD__ . '.';
        $a_group = $this->a_post['groups'];
        $this->logIt(var_export($a_group, true), LOG_OFF, $meth . __LINE__);
        $results = $this->o_model->create($a_group);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $error_msg = $this->o_model->getErrorMessage();
            $this->o_elog->write("Error_message: " . var_export($error_msg, true));
            $a_message = ViewHelper::failureMessage($error_msg);
        }
        return $this->o_view->renderList($a_message);
    }
    public function update()
    {
        $meth = __METHOD__ . '.';
        $a_group = $this->a_post['groups'];
        $this->logIt("Update vars: " . var_export($a_group, true), LOG_OFF, $meth . __LINE__);
        $results = $this->o_model->update($a_group);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $error_msg = $this->o_model->getErrorMessage();
            $a_message = ViewHelper::failureMessage($error_msg);
        }
        return $this->o_view->renderList($a_message);
    }
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }

}
