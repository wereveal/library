<?php
/**
 *  @brief Controller for the Router Admin page.
 *  @file RouterAdminController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class RouterAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-01-28 14:50:14
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0   - first working version     - 01/28/2015 wer
 *      v1.0.0β2 - refactored for namespaces - 12/05/2014 wer
 *      v1.0.0β1 - Initial version           - 11/14/2014 wer
 *  </pre>
 *  @pre The route to this controller has to already be in the database and should not be able to be deleted.
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\RouterModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\RouterAdminView;

class RouterAdminController implements MangerControllerInterface
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
        $this->o_session = $o_di->get('session');
        $this->o_router  = $o_di->get('router');
        $this->o_model   = new RouterModel($o_db);
        $this->o_view    = new RouterAdminView($o_di);
        $this->a_post    = $this->o_router->getPost();
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }
    public function render()
    {
        $a_route_parts = $this->o_router->getRouteParts();
        $main_action = $a_route_parts['route_action'];
        $form_action = $a_route_parts['form_action'];
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
                    return $this->verifyDelete($a_route_parts);
                }
                elseif ($form_action == 'update') {
                    return $this->update();
                }
                else {
                    $a_message = [
                        'message' => 'A Problem Has Occured. Please Try Again.',
                        'type' => 'failure'
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
        $route_id = $this->a_post['route_id'];
        if ($route_id == -1) {
            $a_message = ['message' => 'A Problem Has Occured. The route id was not provided.', 'type' => 'error'];
            return $this->o_view->renderList($a_message);
        }
        $a_results = $this->o_model->delete($route_id);
        return $this->o_view->renderList($a_results);
    }
    public function save()
    {
        $a_route = $this->a_post['route'];
        $results = $this->o_model->create($a_route);
        if ($results) {
            $a_message = ['message' => 'Success!', 'type' => 'success'];
            return $this->o_view->renderList($a_message);
        }
        else {
            $a_message = [
                'message' => 'A Problem Has Occured. The new route could not be saved.',
                'type' => 'failure'
            ];
            return $this->o_view->renderList($a_message);
        }
    }
    public function update()
    {
        $a_route = $this->a_post['route'];
        $results = $this->o_model->update($a_route);
        if ($results) {
            $a_message = ['message' => 'Success!', 'type' => 'success'];
            return $this->o_view->renderList($a_message);
        }
        else {
            $a_message = [
                'message' => 'A Problem Has Occured. The route could not be updated.',
                'type' => 'failure'
            ];
            return $this->o_view->renderList($a_message);
        }
    }
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }
}
