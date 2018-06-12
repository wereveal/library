<?php
/**
 * Class PageController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\PageView;

/**
 * Class PageController - Page Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.1
 * @date    2015-11-27 14:49:00
 * @change_log
 * - v1.0.1   - bug fix                                      - 2016-03-08 wer
 * - v1.0.0   - First working version                        - 11/27/2015 wer
 */
class PageController implements ManagerControllerInterface
{
    use LogitTraits;

    /** @var array */
    private $a_post;
    /** @var Di */
    private $o_di;
    /** @var PageModel */
    private $o_model;
    /** @var Router */
    private $o_router;
    /** @var Session */
    private $o_session;
    /** @var PageView */
    private $o_view;

    /**
     * PageController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di      = $o_di;
        /** @var DbModel $o_db */
        $o_db            = $o_di->get('db');
        $this->o_session = $o_di->get('session');
        $this->o_router  = $o_di->get('router');
        $this->o_model   = new PageModel($o_db);
        $this->o_view    = new PageView($o_di);
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
    public function route()
    {
        $meth = __METHOD__ . '.';
        $a_route_parts = $this->o_router->getRouteParts();
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
                    return $this->o_view->renderVerify();
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
    /**
     * @return string
     */
    public function delete()
    {
        $page_id = isset($this->a_post['page_id']) ? $this->a_post['page_id'] : -1;
        if ($page_id == -1) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The page id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        try {
            $this->o_model->delete($page_id);
            $a_results = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_results = ViewHelper::errorMessage('Error: ' . $e->getMessage());
        }
        return $this->o_view->renderList($a_results);
    }

    /**
     * Saves a record and returns the list again.
     * @return string
     */
    public function save()
    {
        $meth = __METHOD__ . '.';
        $a_page = $this->a_post['page'];
        $this->logIt('Post Values' . var_export($a_page, TRUE), LOG_OFF, $meth . __LINE__);
        try {
            $this->o_model->create($a_page);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $message = 'Error: ' . $e->getMessage();
            $a_message = ViewHelper::failureMessage($message);
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Updates a record and returns the list.
     * @return string
     */
    public function update()
    {
        $meth = __METHOD__ . '.';
        $a_page = $this->a_post['page'];
        $this->logIt('Posted Page: ' . var_export($a_page, TRUE), LOG_OFF, $meth . __LINE__);
        if (!isset($a_page['page_immutable'])) {
            $a_page['page_immutable'] = 'false';
        }
        try {
            $this->o_model->update($a_page);
            $a_message = ViewHelper::successMessage();
        }
        catch(ModelException $e) {
            $message = 'Error: ' . $e->getMessage();
            $a_message = ViewHelper::failureMessage($message);
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Required by interface. Not called.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerify();
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
