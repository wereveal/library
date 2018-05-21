<?php
/**
 * Class GroupsController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\GroupsView;

/**
 * Controller for the Groups Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2018-05-19 15:07:53
 * ## Change Log
 * - v2.1.0   - updated to use ModelException           - 2018-05-19 wer
 *              Updated to use ConfigControllerTraits
 * - v2.0.0   - name refactoring                        - 2017-05-14 wer
 * - v1.0.0   - First working version                   - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                         - 01/28/2015 wer
 */
class GroupsController implements ManagerControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var GroupsModel */
    private $o_model;
    /** @var GroupsView */
    private $o_view;

    /**
     * GroupsController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_model   = new GroupsModel($this->o_db);
        $this->o_view    = new GroupsView($o_di);
        $this->setupElog($o_di);
        $this->o_model->setElog($this->o_elog);
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
        try {
            $results = $this->o_model->deleteWithRelated($group_id);
            $a_message = $results
                ? ViewHelper::successMessage()
                : ViewHelper::failureMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
        }
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
        try {
            $results = $this->o_model->create($a_group);
            if ($results !== false) {
                $a_message = ViewHelper::successMessage();
            }
            else {
                $error_msg = $this->o_model->getErrorMessage();
                $this->logIt("Error_message: " . var_export($error_msg, true), LOG_OFF, $meth . __LINE__);
                $a_message = ViewHelper::failureMessage($error_msg);
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
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
        try {
            $results = $this->o_model->update($a_group);
            $a_message = $results
                ? ViewHelper::failureMessage($this->o_model->getErrorMessage())
                : ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
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
