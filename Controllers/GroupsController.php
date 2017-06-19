<?php
/**
 * @brief     Controller for the Groups Admin page.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/GroupsController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.0
 * @date      2017-05-14 16:41:01
 * @note <b>Change Log</b>
 * - v2.0.0   - name refactoring         - 2017-05-14 wer
 * - v1.0.1   - bug fix                  - 2016-03-08 wer
 * - v1.0.0   - First working version    - 11/27/2015 wer
 * - v1.0.0β1 - Initial version          - 01/28/2015 wer
 * @todo Refactor to match model refactoring
 * @todo Refactor to use ControlerTraits or ManagerControllerTraits
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\GroupsView;

/**
 * Class GroupsController.
 * @class GroupsController
 * @package Ritc\Library\Controllers
 */
class GroupsController implements ManagerControllerInterface
{
    use LogitTraits;

    /** @var array */
    private $a_post;
    /** @var Di */
    private $o_di;
    /** @var GroupsModel */
    private $o_model;
    /** @var Router */
    private $o_router;
    /** @var Session */
    private $o_session;
    /** @var GroupsView */
    private $o_view;

    /**
     * GroupsController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di      = $o_di;
        $o_db            = $o_di->get('db');
        $this->o_router  = $o_di->get('router');
        $this->o_session = $o_di->get('session');
        $this->o_model   = new GroupsModel($o_db);
        $this->o_view    = new GroupsView($o_di);
        $this->a_post    = $this->o_router->getPost();
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }

    /**
     * @return string
     */
    public function route()
    {
        $a_route_parts = $this->o_router->getRouteParts();
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
    /**
     * Deletes the record.
     * @return string
     */
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

    /**
     * @return string
     */
    public function save()
    {
        $meth = __METHOD__ . '.';
        $a_group = $this->a_post['groups'];
        $a_group['group_name'] = Strings::makeCamelCase($a_group['group_name'], false);
        $this->logIt(var_export($a_group, true), LOG_OFF, $meth . __LINE__);
        $results = $this->o_model->create($a_group);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $error_msg = $this->o_model->getErrorMessage();
            $this->logIt("Error_message: " . var_export($error_msg, true), LOG_OFF, $meth . __LINE__);
            $a_message = ViewHelper::failureMessage($error_msg);
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * @return string
     */
    public function update()
    {
        $meth = __METHOD__ . '.';
        $a_group = $this->a_post['groups'];
        $a_group['group_name'] = Strings::makeCamelCase($a_group['group_name'], false);
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

    /**
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }

}
