<?php
/**
 *  @brief     Controller for the Routes Admin page.
 *  @ingroup   ritc_library controllers
 *  @file      RoutesAdminController.php
 *  @namespace Ritc\Library\Controllers
 *  @class     RoutesAdminController
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   2.2.0
 *  @date      2015-12-08 15:05:12
 *  @pre       The route to this controller has to already be in the database and should not be able to be deleted.
 *  @note <pre><b>Change Log</b>
 *      v2.2.0   - New field in the db table                    - 12/08/2015 wer
 *      v2.1.0   - Route Paths all have to start with a slash.  - 10/06/2015 wer
 *                 If the route doesn't end with a file ext
 *                 add a slash to the end as well.
 *      v2.0.0   - renamed                                      - 09/26/2015 wer
 *      v1.0.0   - first working version                        - 01/28/2015 wer
 *      v1.0.0β2 - refactored for namespaces                    - 12/05/2014 wer
 *      v1.0.0β1 - Initial version                              - 11/14/2014 wer
 *  </pre>
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\RoutesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\RoutesAdminView;

class RoutesAdminController implements MangerControllerInterface
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
        $this->o_model   = new RoutesModel($o_db);
        $this->o_view    = new RoutesAdminView($o_di);
        $this->a_post    = $this->o_router->getPost();
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }
    public function render()
    {
        $a_route_parts = $this->o_router->getRouterParts();
        $main_action = $a_route_parts['route_action'];
        $form_action = $a_route_parts['form_action'];
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
            case 'new':
                return $this->o_view->renderForm();
            case 'modify':
                return $this->modify();
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
                    $a_message = ViewHelper::failureMessage();
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
            $a_message = ViewHelper::errorMessage('A Problem Has Occured. The route id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        $a_results = $this->o_model->delete($route_id);
        return $this->o_view->renderList($a_results);
    }
    public function modify()
    {
        return $this->o_view->renderForm($this->a_post);
    }
    public function save()
    {
        $a_route = $this->fixRoute($this->a_post['route']);
        $results = $this->o_model->create($a_route);
        if ($results) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new route could not be saved.');
        }
        return $this->o_view->renderList($a_message);
    }
    public function update()
    {
        $a_route = $this->fixRoute($this->a_post['route']);
        $results = $this->o_model->update($a_route);
        if ($results) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The route could not be updated.');
        }
        return $this->o_view->renderList($a_message);
    }
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }
    /**
     * Removes tags and adds appropriate slashes to route path.
     * @param array $a_route defaults to empty array.
     * @return array
     */
    private function fixRoute(array $a_route = array())
    {
        if ($a_route == array()) {
            return [
                'route_path'      => '',
                'route_class'     => '',
                'route_method'    => '',
                'route_action'    => '',
                'route_immutable' => 0
            ];
        }
        foreach ($a_route as $key => $value) {
            if ($key != 'route_immutable') {
                $a_route[$key] = Strings::removeTagsWithDecode($value, ENT_QUOTES);
            }
        }
        if (substr($a_route['route_path'], 0, 1) != '/') {
            $a_route['route_path'] = '/' . $a_route['route_path'];
        }
        if (strrpos($a_route['route_path'], '.') === false) {
            if (substr($a_route['route_path'], -1, 1) != '/') {
                $a_route['route_path'] .= '/';
            }
        }
        return $a_route;
    }
}
