<?php
/**
 *  @brief Controller for the Page Admin page.
 *  @file PageAdminController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class PageAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-10-30 08:39:24
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version                              - 10/30/2015 wer
 *  </pre>
 * @TODO needs testing
 **/
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\PageAdminView;

class PageAdminController implements MangerControllerInterface
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
        $this->o_model   = new PageModel($o_db);
        $this->o_view    = new PageAdminView($o_di);
        $this->a_post    = $this->o_router->getPost();
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }

    /**
     * Returns the html for the route as determined.
     * @return string
     */
    public function render()
    {
        $meth = __METHOD__ . '.';
        $a_route_parts = $this->o_router->getRouterParts();
        $this->logIt('Route Parts' . var_export($a_route_parts, TRUE), LOG_OFF, $meth . __LINE__);
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
        $this->logIt("Main Action: {$main_action}", LOG_OFF, $meth . __LINE__);
        $this->logIt("Form Action: {$form_action}", LOG_OFF, $meth . __LINE__);
        switch ($main_action) {
            case 'save':
                return $this->save();
            case 'delete':
                return $this->delete();
            case 'update':
                if ($form_action == 'verify') {
                    return $this->o_view->renderVerify($this->a_post);
                }
                elseif ($form_action == 'update') {
                    return $this->update();
                }
                else {
                    $a_message = ViewHelper::failureMessage();
                    return $this->o_view->renderList($a_message);
                }
            case 'new':
            case 'modify':
                return $this->o_view->renderForm();
            case '':
            default:
                return $this->o_view->renderList();
        }
    }

    ### Required by Interface ###
    public function delete()
    {
        $page_id = isset($this->a_post['page_id']) ? $this->a_post['page_id'] : -1;
        if ($page_id == -1) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The page id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        $results = $this->o_model->delete($page_id);
        if ($results) {
            $a_results = ViewHelper::successMessage();
        }
        else {
            $error_message = $this->o_model->getErrorMessage();
            $a_results = ViewHelper::failureMessage($error_message);
        }
        return $this->o_view->renderList($a_results);
    }
    public function save()
    {
        $meth = __METHOD__ . '.';
        $a_page = $this->a_post['page'];
        $a_page['page_url'] = $this->fixUrl($a_page['page_url']);
        $this->logIt('Post Values' . var_export($a_page, TRUE), LOG_OFF, $meth . __LINE__);
        $results = $this->o_model->create($a_page);
        if ($results) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $message = $this->o_model->getErrorMessage();
            $a_message = ViewHelper::failureMessage($message);
        }
        return $this->o_view->renderList($a_message);
    }
    public function update()
    {
        $meth = __METHOD__ . '.';
        $a_page = $this->a_post['page'];
        if (isset($a_page['page_url'])) {
            $a_page['page_url'] = $this->fixUrl($a_page['page_url']);
        }
        $this->logIt('Posted Page: ' . var_export($a_page, TRUE), LOG_OFF, $meth . __LINE__);
        $results = $this->o_model->update($a_page);
        if ($results) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $message = $this->o_model->getErrorMessage();
            $a_message = ViewHelper::failureMessage($message);
        }
        return $this->o_view->renderList($a_message);
    }
    /*
     * Required by interface. Not called.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }
    /**
     * Adds slashes to url if needed.
     * @param string $url
     * @return string
     */
    private function fixUrl($url = '/')
    {
        if ($url == '/') {
            return '/';
        }
        $url = Strings::removeTags($url);
        $url = str_replace(' ', '_', $url);
        $url = preg_replace("/[^a-zA-Z0-9_\-\/]/", '', $url);
        if (substr($url, 0, 1) != '/') {
            $url = '/' . $url;
        }
        if (strrpos($url, '.') === false) { // url is not like /index.php
            if (substr($url, -1, 1) != '/') {
                $url .= '/';
            }
        }
        return $url;
    }
}
