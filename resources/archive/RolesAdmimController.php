<?php
/**
 *  @brief     Controller for the Roles Admin page.
 *  @file      RolesAdmimController.php
 *  @ingroup   ritc_library controllers
 *  @namespace Ritc\Library\Controllers
 *  @class     RolesAdmimController
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.1.0
 *  @date      2015-10-07 14:31:15
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.1.0   - added immutable code            - 10/07/2015 wer
 *      v1.0.1   - changes to model reflected here - 09/24/2015 wer
 *      v1.0.0   - First working version           - 01/28/2015 wer
 *      v1.0.0β1 - Initial version                 - 01/20/2015 wer
 *  </pre>
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\RolesAdminView;

class RolesAdmimController implements ManagerControllerInterface
{
    use LogitTraits;

    /** @var array  */
    private $a_post;
    /** @var \Ritc\Library\Services\Di  */
    private $o_di;
    /** @var \Ritc\Library\Models\RolesModel  */
    private $o_model;
    /** @var \Ritc\Library\Services\Router  */
    private $o_router;
    /** @var \Ritc\Library\Services\Session  */
    private $o_session;
    /** @var \Ritc\Library\Views\RolesAdminView  */
    private $o_view;

    /**
     * RolesAdmimController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di      = $o_di;
        $o_db            = $o_di->get('db');
        $this->o_router  = $o_di->get('router');
        $this->o_session = $o_di->get('session');
        $this->o_model   = new RolesModel($o_db);
        $this->o_view    = new RolesAdminView($o_di);
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
            $this->o_view->setElog($this->o_elog);
        }
    }
    public function render()
    {
        $a_route_parts = $this->o_router->getRouteParts();
        $this->a_post  = $a_route_parts['post'];
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
                    $a_message = ViewHelper::failureMessage('A Problem Has Occured. Please Try Again.');
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
            $a_message = ViewHelper::errorMessage('A Problem Has Occured. The role id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        $results = $this->o_model->delete($role_id);
        if ($results === 1) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $error_msg = $this->o_model->getErrorMessage($results);
            $a_message = ViewHelper::failureMessage($error_msg);
        }
        return $this->o_view->renderList($a_message);
    }
    public function save()
    {
        $a_role = $this->a_post['roles'];
        error_log(var_export($a_role, true));
        if (!isset($a_role['role_immutable'])) {
            $a_role['role_immutable'] = 0;
        }
        $results = $this->o_model->create($a_role);
        if ($results > 0) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $error_msg = $this->o_model->getErrorMessage($results);
            $a_message = ViewHelper::failureMessage($error_msg);
        }
        return $this->o_view->renderList($a_message);
    }
    public function update()
    {
        $a_role = $this->a_post['roles'];
        if (!isset($a_role['role_immutable'])) {
            $a_role['role_immutable'] = 0;
        }
        $results = $this->o_model->update($a_role);
        if ($results === 1) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $error_msg = $this->o_model->getErrorMessage($results);
            $a_message = ViewHelper::failureMessage($error_msg);
        }
        return $this->o_view->renderList($a_message);
    }
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }

}
