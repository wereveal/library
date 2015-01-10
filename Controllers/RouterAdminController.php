<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file RouterAdminController.php
 *  @ingroup library core
 *  @namespace Ritc/Library/Controllers
 *  @class RouterAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1ß
 *  @date 2014-12-05 11:06:06
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1ß - refactored for namespaces - 12/05/2014 wer
 *      v1.0.0ß - Initial version           - 11/14/2014 wer
 *  </pre>
 *  @pre The route to this controller has to already be in the database and should not be able to be deleted.
 *  @todo Everything
 *  @todo Test
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Session;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Models\RouterModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Views\RouterAdminView;

class RouterAdminController extends Base implements ControllerInterface
{
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
                return $this->o_view->renderList();
                break;
            case 'update':
                return $this->o_view->renderList();
                break;
            case 'verifyDelete':
                break;
            case 'delete':
                return $this->o_view->renderList();
                break;
            case '':
            default:
                return $this->o_view->renderList();
        }
    }
    public function save(array $a_values)
    {
        $results = $this->o_model->create($a_values);
        if ($results) {
            return $this->o_view->renderList();
        }
        else {
            $a_message = ['message' => 'A Problem Has Occured. The new route could not be saved.', 'type' => 'error'];
            return $this->o_view->renderList($a_message);
        }
    }
    public function update(array $a_values)
    {
        $results = $this->o_model->update($a_values);
        if ($results) {
            return $this->o_view->renderList();
        }
        else {
            $a_message = ['message' => 'A Problem Has Occured. The route could not be modified.', 'type' => 'error'];
            return $this->o_view->renderList($a_message);
        }
    }
    public function verifyDelete(array $a_values)
    {
        return $this->o_view->renderVerify($a_values);
    }
    public function delete($route_id = -1)
    {
        if ($route_id == -1) {
            $a_message = ['message' => 'A Problem Has Occured. The route id was not provided.', 'type' => 'error'];
            return $this->o_view->renderList($a_message);
        }
        $results = $this->o_model->delete($route_id);
        if ($results) {
            return $this->o_view->renderList();
        }
        else {
            $a_message = ['message' => 'A Problem Has Occured. The route could not be deleted.', 'type' => 'error'];
            return $this->o_view->renderList($a_message);
        }
    }
}
