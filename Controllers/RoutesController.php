<?php
/**
 * Class RoutesController
 * @package Ritc_Library
 * @todo Review Class and fix warnings, especially missing try/catch/throws
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\RoutesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\RoutesView;

/**
 * Class RoutesController - Controller for the Routes Admin page.
 * The route to this controller has to already be in the database and
 * should not be able to be deleted.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.2.0
 * @date    2016-04-13 08:52:26
 * @change_log
 * - v2.2.0   - Refactored to work with the Urls class/model - 2016-04-13 wer
 * - v2.1.1   - bug fix                                      - 2016-03-08 wer
 * - v2.1.0   - Route Paths all have to start with a slash.  - 10/06/2015 wer
 *                If the route doesn't end with a file ext
 *                add a slash to the end as well.
 * - v2.0.0   - renamed                                      - 09/26/2015 wer
 * - v1.0.0   - first working version                        - 01/28/2015 wer
 * - v1.0.0β2 - refactored for namespaces                    - 12/05/2014 wer
 * - v1.0.0β1 - Initial version                              - 11/14/2014 wer
 */
class RoutesController implements ManagerControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var RoutesModel */
    private $o_model;
    /** @var RoutesView */
    private $o_view;

    /**
     * RoutesController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_model   = new RoutesModel($this->o_db);
        $this->o_view    = new RoutesView($o_di);
        $this->a_object_names = ['o_model'];
        $this->setupElog($o_di);
    }

    /**
     * @return string
     */
    public function route()
    {
        $a_route_parts = $this->o_router->getRouteParts();
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
    /**
     * Deletes a record.
     * @return string
     */
    public function delete()
    {
        $route_id = $this->a_post['route_id'];
        if ($route_id == -1) {
            $a_message = ViewHelper::errorMessage('A Problem Has Occured. The route id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        $results = $this->o_model->delete($route_id);
        if ($results) {
            $a_results = [
                'message' => 'Success!',
                'type'    => 'success'
            ];
        }
        else {
            $message = $this->o_model->getErrorMessage();
            $a_results = [
                'message' => $message,
                'type'    => 'failure'
            ];
        }

        return $this->o_view->renderList($a_results);
    }

    /**
     * Saves a record
     * @return string
     */
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

    /**
     * Updates the record.
     * @return string
     */
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

    /**
     * Verifies the deletion.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }

    /**
     * Removes tags and makes fields single words, camelCase.
     * @param array $a_route defaults to empty array.
     * @return array
     */
    private function fixRoute(array $a_route = array())
    {
        if ($a_route == array()) {
            return [
                'url_id'          => '',
                'route_class'     => '',
                'route_method'    => '',
                'route_action'    => '',
                'route_immutable'=> 'false',
                'route_id'        => 0
            ];
        }
        foreach ($a_route as $key => $value) {
            switch ($key) {
                case 'route_id':
                case 'route_immutable':
                case 'url_id':
                    break;
                case 'route_class':
                    $value = Strings::removeTagsWithDecode($value, ENT_QUOTES);
                    $a_route[$key] = Strings::makeCamelCase($value, false);
                    break;
                default:
                    $value = Strings::removeTagsWithDecode($value, ENT_QUOTES);
                    $a_route[$key] = Strings::makeCamelCase($value, true);
            }
        }
        return $a_route;
    }
}
