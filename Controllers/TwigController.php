<?php
/**
 * Class TwigController
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Models\TwigComplexModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\TwigView;

/**
 * Controller admin for Twig config.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-05-14 14:36:29
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-14 wer
 */
class TwigController implements ControllerInterface
{
    use ConfigControllerTraits, LogitTraits;

    /** @var TwigComplexModel $o_tc */
    private $o_tc;
    /** @var TwigView */
    private $o_view;

    /**
     * TwigController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view = new TwigView($this->o_di);
        $this->o_tc = new TwigComplexModel($this->o_di);
        $this->setupElog($o_di);
    }

    /**
     * Routes things around to do the Twig management.
     *
     * @return string
     */
    public function route():string
    {
        $a_message = [];
        $action = empty($this->form_action)
            ? 'renderList'
            : $this->form_action;
        switch ($action) {
            case 'delete_dir':
                return $this->deleteDir();
            case 'delete_tp':
                return $this->deletePrefix();
            case 'delete_tpl':
                return $this->deleteTpl();
            case 'new_dir':
                return $this->saveDir('new');
            case 'update_dir':
                return $this->saveDir('update');
            case 'new_tp':
                return $this->savePrefix('new');
            case 'update_tp':
                return $this->savePrefix('update');
            case 'new_tpl':
                return $this->saveTpl('new');
            case 'update_tpl':
                return $this->saveTpl('update');
            case 'verify_delete_tpl':
                return $this->verifyDelete('tpl');
            case 'verify_delete_tp':
                return $this->verifyDelete('tp');
            case 'verify_delete_dir':
                return $this->verifyDelete('dir');
            case 'renderList':
            default:
                // just render the list
        }
        return $this->o_view->render($a_message);
    }

    ### Model calls ###
    /**
     * Updates or Creates a new twig directory record.
     *
     * @param string $action
     * @return string
     */
    private function saveDir($action = 'update'):string
    {
        try {
            $this->o_tc->saveDir($this->a_post, $action);
            if ($action === 'update') {
                $message = 'Update of the directory successful.';
            }
            else {
                $message = 'New Directory Record Saved.';
            }
            $a_message = ViewHelper::successMessage($message);
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage($e->getMessage());
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Creates or Updates a twig prefix record.
     *
     * @param string $action
     * @return string
     */
    private function savePrefix($action = 'update'):string
    {
        try {
            $this->o_tc->savePrefix($this->a_post, $action);
            if ($action === 'update') {
                $message = 'Update of the prefix successful.';
            }
            else {
                $message = 'New prefix record saved.';
            }
            $a_message = ViewHelper::successMessage($message);
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage($e->getMessage());
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Creates or updates a new twig template record.
     *
     * @param string $action
     * @return string
     */
    private function saveTpl($action = 'update'):string
    {
        try {
            $this->o_tc->saveTpl($this->a_post, $action);
            if ($action === 'update') {
                $message = 'Update of the template successful.';
            }
            else {
                $message = 'New template record saved.';
            }
            $a_message = ViewHelper::successMessage($message);
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage($e->getMessage());
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Deletes a twig directory record.
     *
     * @return string
     */
    private function deleteDir():string
    {
        try {
            $this->o_tc->deleteDir($this->a_post['td_id']);
            $a_message = ViewHelper::successMessage('Delete Successful.');
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage($e->getMessage());
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Deletes a twig prefix record.
     *
     * @return string
     */
    private function deletePrefix():string
    {
        try {
            $this->o_tc->deletePrefix($this->a_post['tp_id']);
            $a_message = ViewHelper::successMessage('Delete Successful.');
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage($e->getMessage());
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Deletes a twig template record.
     *
     * @return string
     */
    private function deleteTpl():string
    {
        try {
            $this->o_tc->deleteTpl($this->a_post['tpl_id']);
            $a_message = ViewHelper::successMessage('Delete Successful.');
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage($e->getMessage());
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Routes to the correct verifyDelete form.
     *
     * @param string $which_one
     * @return string
     */
    private function verifyDelete($which_one = ''):string
    {
        switch ($which_one) {
            case 'tpl':
                if (!empty($this->a_post['tpl_id'])) {
                    return $this->o_view->renderDeleteTpl($this->a_post['tpl_id']);
                }
                $a_message =ViewHelper::errorMessage('Could not delete the template, unknown template.');
                break;
            case 'tp':
                if (!empty($this->a_post['tp_id'])) {
                    return $this->o_view->renderDeleteTp($this->a_post['tp_id']);
                }
                $a_message = ViewHelper::errorMessage('Could not delete the twig prefix, unknown prefix.');
                break;
            case 'dir':
                if (!empty($this->a_post['td_id'])) {
                    return $this->o_view->renderDeleteDir($this->a_post['td_id']);
                }
                $a_message = ViewHelper::errorMessage('Could not delete the twig prefix, unknown directory.');
                break;
            default:
                $a_message = ViewHelper::errorMessage('Missing a valid value.');
        }
        return $this->o_view->render($a_message);
    }
}
