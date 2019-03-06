<?php
/**
 * Class PageController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ControllerException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Exceptions\ViewException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\DatesTimes;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\BlocksModel;
use Ritc\Library\Models\PageComplexModel;
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

    /** @var PageComplexModel */
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
        try {
            $this->o_model = new PageComplexModel($o_di);
        }
        catch (ModelException $e) {
            throw new ControllerException($e->getMessage(), $e->getCode(), $e);
        }
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
                $page_id = $this->a_post['page']['page_id'];
                return $this->o_view->renderForm('modify_page', $page_id);
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
            $this->o_model->deletePageValues($page_id);
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
        $meth              = __METHOD__ . '.';
        $a_blocks          = [];
        $a_message         = [];
        $a_required_fields = [
            'url_id',
            'page_title',
            'page_base_url',
            'page_type',
            'page_lang',
            'page_charset',
            'tpl_id'
        ];
        $a_page            = $this->a_post['page'];
        $a_missing_keys    = Arrays::findMissingKeys($a_page, $a_required_fields);
        if (!empty($a_missing_keys)) {
            $message = 'Missing Values for the Page: ';
            foreach ($a_missing_keys as $the_key) {
                $message .= $the_key . ', ';
            }
            $message = Strings::removeLastCharacters(trim($message), ',');
            $a_msg_values = [
                'message' => $message,
                'type'    => 'error'
            ];
            $a_message = ViewHelper::fullMessage($a_msg_values);
        }
        if (empty($this->a_post['blocks']) && empty($a_message)) {
            $o_blocks = new BlocksModel($this->o_db);
            $o_blocks->setupElog($this->o_di);
            try {
                $a_block_results = $o_blocks->read(['b_name' => 'body'], ['a_fields' => ['b_id']]);
                $a_blocks = [$a_block_results[0]['b_id'] => 'true'];
            }
            catch (ModelException $e) {
                $a_msg_values = [
                    'message' => 'A problem with the blocks occurred. Please try again.',
                    'type'    => 'error'
                ];
                $a_message = ViewHelper::fullMessage($a_msg_values);
            }
        }
        else {
            $a_blocks = $this->a_post['blocks'];
        }
        if (!empty($a_page['page_up'])) {
            $a_page['page_up'] = DatesTimes::convertDateTimeWith('Y-m-d H:i:s', $a_page['page_up']);
        }
        if (!empty($a_page['page_down'])) {
            $a_page['page_down'] = DatesTimes::convertDateTimeWith('Y-m-d H:i:s', $a_page['page_down']);
            $a_page['created_on'] = date('Y-m-d H:i:s');
        }
        $a_page['updated_on'] = date('Y-m-d H:i:s');
        $a_page['a_page']     = $a_page;
        $a_page['a_blocks']   = [];
        foreach ($a_blocks as $block_id => $value) {
            if ($value === 'true') {
                $a_page['a_blocks'][] = $block_id;
            }
        }
          $this->logIt('Page Values' . var_export($a_page, TRUE), LOG_OFF, $meth . __LINE__);
        if (empty($a_message)) {
            try {
                $this->o_model->savePageValues($a_page);
                if ($this->use_cache) {
                    $this->o_cache->clearTag('page');
                }
                $a_message = ViewHelper::successMessage();
            }
            catch (ModelException $e) {
                $a_msg_values = [
                    'message' => 'Error: ' . $e->getMessage(),
                    'type'    => 'error'
                ];
                $a_message = ViewHelper::fullMessage($a_msg_values);
            }
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
        $a_page['a_page']   = $this->a_post['page'];
        $a_page['a_blocks'] = [];
        foreach ($this->a_post['blocks'] as $block_id => $value) {
            if ($value === 'true') {
                $a_page['a_blocks'][] = $block_id;
            }
        }
        $this->logIt('Posted Page: ' . var_export($a_page, TRUE), LOG_OFF, $meth . __LINE__);
        if (!isset($a_page['page_immutable'])) {
            $a_page['page_immutable'] = 'false';
        }
        try {
            $this->o_model->updatePageValues($a_page);
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
        $a_page = $this->a_post['page'];
        $a_values = [
            'what'          => 'Page',
            'name'          => $a_page['page_title'],
            'extra_message' => 'This is significant! It will delete both the content and the page. The url will still remain but may point to nothing!',
            'submit_value'  => 'delete',
            'form_action'   => $this->a_router_parts['request_uri'],
            'cancel_action' => $this->a_router_parts['request_uri'],
            'btn_value'     => $a_page['page_title'],
            'hidden_name'   => 'page_id',
            'hidden_value'  => $a_page['page_id']
        ];
        $a_options = [
            'fallback'    => 'renderList' // if something goes wrong, which method to fallback
        ];
        return $this->o_view->renderVerifyDelete($a_values, $a_options);
    }
}
