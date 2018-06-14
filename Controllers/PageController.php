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
use Ritc\Library\Traits\ConfigControllerTraits;
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
 * @todo Convert to use ConfigControllerTraits
 */
class PageController implements ManagerControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var PageModel */
    private $o_model;
    /** @var PageView */
    private $o_view;

    /**
     * PageController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->a_object_names = ['o_model'];
        $this->o_model = new PageModel($this->o_db);
        $this->o_view = new PageView($o_di);
        $this->setupElog($o_di);
    }

    /**
     * Returns the html for the route as determined.
     * @return string
     */
    public function route()
    {
        switch ($this->form_action) {
            case 'save':
                return $this->save();
            case 'delete':
                return $this->delete();
            case 'update':
                return $this->update();
            case 'verify':
                return $this->verifyDelete();
            case 'new':
            case 'modify':
                return $this->o_view->renderForm($this->form_action);
            case '':
            default:
                return $this->o_view->renderList();
        }
    }

    ### Required by Interface ###
    /**
     * Deletes specifed record then displays the page list with results.
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
     * Required by interface.
     * @return string
     */
    public function verifyDelete()
    {

        $a_values = [
            'what'          => 'Page',
            'name'          => 'Something to help one know which one, e.g. myConstant',
            'extra_message' => 'an extra message',
            'submit_value'  => 'value that is being submitted by button, defaults to delete',
            'form_action'   => 'the url, e.g. /manger/config/constants/',
            'cancel_action' => 'the url for canceling the delete if different from form action',
            'btn_value'     => 'What the Button says, e.g. Constants',
            'hidden_name'   => 'primary id name, e.g., const_id',
            'hidden_value'  => 'primary id, e.g. 1',
        ];
        $a_options = [
            'fallback'    => 'renderList' // if something goes wrong, which method to fallback
        ];
        return $this->o_view->renderVerifyDelete($a_values, $a_options);
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
