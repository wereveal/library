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
 * @version v1.0.0-alpha.1
 * @date    2021-11-26 15:26:31
 * @change_log
 * - v1.0.0-alpha.1 - updated for php8                          - 2021-11-26 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-05-14 wer
 */
class TwigController implements ControllerInterface
{
    use LogitTraits;
    use ConfigControllerTraits;

    /** @var TwigComplexModel $o_tc */
    private TwigComplexModel $o_tc;
    /** @var TwigView */
    private TwigView $o_view;

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
        return match ($action) {
            'delete_dir'        => $this->deleteDir(),
            'delete_tp'         => $this->deletePrefix(),
            'delete_tpl'        => $this->deleteTpl(),
            'new_dir'           => $this->saveDir('new'),
            'update_dir'        => $this->saveDir(),
            'new_tp'            => $this->savePrefix('new'),
            'update_tp'         => $this->savePrefix(),
            'new_tpl'           => $this->saveTpl('new'),
            'update_tpl'        => $this->saveTpl(),
            'verify_delete_tpl' => $this->verifyDelete('tpl'),
            'verify_delete_tp'  => $this->verifyDelete('tp'),
            'verify_delete_dir' => $this->verifyDelete('dir'),
            default             => $this->o_view->render($a_message),
        };
    }

    ### Model calls ###
    /**
     * Updates or Creates a new twig directory record.
     *
     * @param string $action
     * @return string
     */
    private function saveDir(string $action = 'update'):string
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
    private function savePrefix(string $action = 'update'):string
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
    private function saveTpl(string $action = 'update'):string
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
    private function verifyDelete(string $which_one = ''):string
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
