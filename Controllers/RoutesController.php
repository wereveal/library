<?php
/**
 * Class RoutesController
 * @package Ritc_Library
 * @todo Review Class and fix warnings, especially missing try/catch/throws
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\RoutesComplexModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\RoutesView;

/**
 * Class RoutesController - Controller for the Routes Admin page.
 * The route to this controller has to already be in the database and
 * should not be able to be deleted.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.0.0
 * @date    2018-06-20 13:56:13
 * @change_log
 * - v3.0.0   - Working version after a lot of refactoring.  - 2018-06-20 wer
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

    /** @var RoutesComplexModel $o_complex */
    private $o_complex;
    /** @var RoutesView $o_view */
    private $o_view;

    /**
     * RoutesController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view         = new RoutesView($o_di);
        $this->o_complex      = new RoutesComplexModel($o_di);
        $this->a_object_names = ['o_model'];
        $this->setupElog($o_di);
    }

    /**
     * Main router for the controller.
     *
     * @return string
     */
    public function route():string
    {
        switch ($this->form_action) {
            case 'delete':
                return $this->delete();
            case 'save_new':
                return $this->save();
            case 'update':
                 return $this->update();
            case 'verify':
                return $this->verifyDelete();
            case '':
            default:
                return $this->o_view->renderList();
        }
    }

    ### Required by Interface ###
    /**
     * Deletes a record.
     *
     * @return string
     */
    public function delete():string
    {
        $route_id = $this->a_post['route_id'];
        if (empty($route_id) || $route_id < 1) {
            $a_message = ViewHelper::errorMessage('A Problem Has Occured. The route id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        try {
            $this->o_complex->delete($route_id);
            if ($this->use_cache) {
                $this->o_cache->clearTag('route');
            }
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage('Error: ' . $e->getMessage());
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Saves a record
     *
     * @return string
     */
    public function save():string
    {
        try {
            $this->o_complex->saveNew($this->a_post);
            $a_message = ViewHelper::successMessage();
            if ($this->use_cache) {
                $this->o_cache->clearTag('route');
            }
        }
        catch (ModelException $e) {
            $msg  = 'A Problem Has Occured. The route could not be saved.';
            $msg .= DEVELOPER_MODE
                ? ' -- ' . $e->getMessage()
                : '';
            $a_message = ViewHelper::failureMessage($msg);
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Updates the record.
     *
     * @return string
     */
    public function update():string
    {
        try {
            $this->o_complex->update($this->a_post);
            $a_message = ViewHelper::successMessage();
            if ($this->use_cache) {
                $this->o_cache->clearTag('route');
            }
        }
        catch (ModelException $e) {
            $msg = 'A Problem Has Occured. The route could not be updated.';
            $msg .= DEVELOPER_MODE
                ? ' -- ' . $e->getMessage()
                : '';
            $a_message = ViewHelper::failureMessage($msg);
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Verifies the deletion.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        $route_id = $this->a_post['route']['route_id'];
        $cache_key = 'route.url.for.' . $route_id;
        $url = '';
        if ($this->use_cache) {
            $url = $this->o_cache->get($cache_key);
        }
        if (empty($url)) {
            try {
                $a_route = $this->o_complex->readWithUrl($route_id);
                $url = $a_route[0]['url_text'];
                if ($this->use_cache) {
                    $this->o_cache->set($cache_key, $url, 'route');
                }
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::errorMessage('Unable to delete the record');
                return $this->o_view->renderList($a_message);
            }
        }

        $a_values = [
            'what'          => 'Route',
            'name'          => 'Route for ' . $url,
            'form_action'   => '/manager/config/routes/',
            'btn_value'     => 'Route',
            'hidden_name'   => 'route_id',
            'hidden_value'  => $route_id,
        ];
        $a_options = [
            'tpl'         => 'verify_delete',
            'location'    => '/manager/config/routes/',
            'fallback'    => 'renderList'
        ];
        return $this->o_view->renderVerifyDelete($a_values, $a_options);
    }
}
