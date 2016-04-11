<?php
/**
 * @brief     Controller for Urls admin.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/UrlsAdminController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-04-11 08:01:52
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2016-04-11 wer
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Views\UrlsView;

/**
 * Class UrlsAdminController.
 * @class   UrlsAdminController
 * @package Ritc\Library\Controller
 */
class UrlsAdminController implements ManagerControllerInterface
{
    use ControllerTraits;

    protected $o_urls_model;
    protected $o_urls_view;

    /**
     * UrlsAdminController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->o_urls_model = new UrlsModel($this->o_db);
        $this->o_urls_view  = new UrlsView($o_di);
    }

    /**
     * Main method used to render the page.
     * @return string
     */
    public function render()
    {
        $main_action   = $this->a_router_parts['route_action'];
        $form_action   = $this->a_router_parts['form_action'];
        $url_action    = isset($this->a_router_parts['url_actions'][0])
            ? $this->a_router_parts['url_actions'][0]
            : '';
        if ($main_action == '' && $url_action != '') {
            $main_action = $url_action;
        }
        switch ($main_action) {
            case 'modify':
                switch ($form_action) {
                    case 'verify':
                        return $this->verifyDelete();
                    case 'update':
                        return $this->update();
                    default:
                        $a_message = ViewHelper::failureMessage('A problem occured. Please try again.');
                        return $this->o_urls_view->renderList($a_message);
                }
            case 'save':
                return $this->save();
            case 'delete':
                return $this->delete();
            default:
                return $this->o_urls_view->renderList();
        }
    }

    /**
     * Method for saving data.
     * @return string
     */
    public function save()
    {
        $url       = $this->a_post['url'];
        $immutable = $this->a_post['immutable'];
        list($scheme, $text) = explode('://', $url);
        $a_values = [
            'url_text'      => str_replace($_SERVER['HTTP_HOST'], '', $text),
            'url_scheme'    => $scheme,
            'url_immutable' => $immutable
        ];
        $results = $this->o_urls_model->create($a_values);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new url could not be saved.');
        }
        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Method for updating data.
     * @return string
     */
    public function update()
    {
        if (!isset($this->a_post['url_id']) || !isset($this->a_post['url']) || !isset($this->a_post['immutable'])) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The url could not be updated.');
            return $this->o_urls_view->renderList($a_message);
        }

        list($scheme, $text) = explode($this->a_post['url']);
        $a_values = [
            'url_id'        => $this->a_post['url_id'],
            'url_text'      => str_replace($_SERVER['HTTP_HOST'], '', $text),
            'url_scheme'    => $scheme,
            'url_immutable' => $this->a_post['immutable']
        ];
        $results = $this->o_urls_model->update($a_values);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The url could not be updated.');
        }
        return $this->o_urls_view->renderList($a_message);
    }

    /**
     * Method to display the verify delete form.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_urls_view->renderVerify($this->a_post);
    }

    /**
     * Method to delete data.
     * @return string
     */
    public function delete()
    {
        $results = false;
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new configuration could not be saved.');
        }
        return $this->o_urls_view->renderList($a_message);
    }

}