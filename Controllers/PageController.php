<?php
/**
 * Class PageController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ControllerException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Exceptions\ViewException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\PageModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\PageView;

/**
 * Class PageController - Page Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2018-06-21 07:04:13
 * @change_log
 * - v2.0.0   - Refactored to use ConfigControllerTraits     - 2018-06-21 wer
 * - v1.0.0   - First working version                        - 11/27/2015 wer
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
     *
     * @param Di $o_di
     * @throws ControllerException
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->a_object_names = ['o_model'];
        $this->o_model = new PageModel($this->o_db);
        try {
            $this->o_view = new PageView($o_di);
        }
        catch (ViewException $e) {
            throw new ControllerException($e->getMessage(), $e->getCode(), $e);
        }
        $this->setupElog($o_di);
    }

    /**
     * Returns the html for the route as determined.
     *
     * @return string
     */
    public function route():string
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
            case 'new_page':
                return $this->o_view->renderForm('new_page');
            case 'modify':
                return $this->o_view->renderForm('modify_page');
            case '':
            default:
                return $this->o_view->renderList();
        }
    }

    ### Required by Interface ###
    /**
     * Deletes specifed record then displays the page list with results.
     *
     * @return string
     */
    public function delete():string
    {
        $page_id = $this->a_post['page_id'] ?? -1;
        if ($page_id === -1) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The page id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        try {
            $this->o_model->delete($page_id);
            if ($this->use_cache) {
                $this->o_cache->clearTag('page');
            }
            $a_results = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_results = ViewHelper::errorMessage('Error: ' . $e->getMessage());
        }
        return $this->o_view->renderList($a_results);
    }

    /**
     * Saves a record and returns the list again.
     *
     * @return string
     */
    public function save():string
    {
        $meth = __METHOD__ . '.';
        $a_page = $this->a_post['page'];
        $this->logIt('Post Values' . var_export($a_page, TRUE), LOG_OFF, $meth . __LINE__);
        try {
            $this->o_model->create($a_page);
            if ($this->use_cache) {
                $this->o_cache->clearTag('page');
            }
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
     *
     * @return string
     */
    public function update():string
    {
        $meth = __METHOD__ . '.';
        $a_page = $this->a_post['page'];
        $this->logIt('Posted Page: ' . var_export($a_page, TRUE), LOG_OFF, $meth . __LINE__);
        if (!isset($a_page['page_immutable'])) {
            $a_page['page_immutable'] = 'false';
        }
        try {
            $this->o_model->update($a_page);
            if ($this->use_cache) {
                $this->o_cache->clearTag('page');
            }
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
     *
     * @return string
     */
    public function verifyDelete():string
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
}
