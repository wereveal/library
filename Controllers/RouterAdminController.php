<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file RouterAdminController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class RouterAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1β
 *  @date 2014-12-05 11:06:06
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1β - refactored for namespaces - 12/05/2014 wer
 *      v1.0.0β - Initial version           - 11/14/2014 wer
 *  </pre>
 *  @pre The route to this controller has to already be in the database and should not be able to be deleted.
 *  @todo Everything
 *  @todo Test
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\RouterModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Views\RouterAdminView;

class RouterAdminController extends Base implements MangerControllerInterface
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
        $this->o_session = $o_di->get('session');
        $this->o_router  = $o_di->get('router');
        $this->o_model   = new RouterModel($o_db);
        $this->o_view    = new RouterAdminView($o_di);
        $this->a_post    = $this->o_router->getPost();
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
            $this->o_view->setElog($this->o_elog);
        }
    }
    public function render()
    {
        $a_route_parts = $this->o_router->getRouteParts();
        $main_action = $a_route_parts['route_action'];
        switch ($main_action) {
            case 'save':
            case 'update':
            case 'delete':
                if ($this->o_session->isValidSession($this->a_post, true)) {
                    return $this->$main_action();
                }
                else {
                    header("Location: " . SITE_URL);
                }
            case 'verifyDelete':
                return $this->verifyDelete();
            case '':
            default:
                return $this->o_view->renderList();
        }
    }
    public function save()
    {
        $a_route = $this->a_post['route'];
        $results = $this->o_model->create($a_route);
        if ($results) {
            return $this->o_view->renderList();
        }
        else {
            $a_message = ['message' => 'A Problem Has Occured. The new route could not be saved.', 'type' => 'failure'];
            return $this->o_view->renderList($a_message);
        }
    }
    public function update()
    {
        $a_route = $this->a_post['route'];
        $results = $this->o_model->update($a_values);
        if ($results) {
            return $this->o_view->renderList();
        }
        else {
            $a_message = ['message' => 'A Problem Has Occured. The route could not be modified.', 'type' => 'failure'];
            return $this->o_view->renderList($a_message);
        }
    }
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }
    public function delete()
    {
        $a_route = $this->a_post['route'];
        $route_id = $a_values['route_id'];
        if ($route_id == -1) {
            $a_message = ['message' => 'A Problem Has Occured. The route id was not provided.', 'type' => 'error'];
            return $this->o_view->renderList($a_message);
        }
        $results = $this->o_model->delete($route_id);
        if ($results) {
            return $this->o_view->renderList();
        }
        else {
            $a_message = ['message' => 'A Problem Has Occured. The route could not be deleted.', 'type' => 'failure'];
            return $this->o_view->renderList($a_message);
        }
    }
}
