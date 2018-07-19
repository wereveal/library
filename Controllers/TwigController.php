<?php
/**
 * Class TwigController
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Models\TwigComplexModel;
use Ritc\Library\Models\TwigDirsModel;
use Ritc\Library\Models\TwigPrefixModel;
use Ritc\Library\Models\TwigTemplatesModel;
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

    /** @var \Ritc\Library\Views\TwigView  */
    private $o_view;

    /**
     * TwigController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view = new TwigView($this->o_di);
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
            case 'update_dir':
                return $this->saveDir();
            case 'new_tp':
            case 'update_tp':
                return $this->savePrefix();
            case 'new_tpl':
            case 'update_tpl':
                return $this->saveTpl();
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
     * @return string
     */
    private function saveDir():string
    {
        $o_dir = new TwigDirsModel($this->o_db);
        $o_dir->setupElog($this->o_di);
        $a_message = ViewHelper::successMessage('testing');
        $a_values = [
            'tp_id'   => $this->a_post['tp_id'],
            'td_name' => $this->a_post['td_name']
        ];
        if ($this->form_action === 'new_dir') {
            try {
                $o_dir->create($a_values);
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::failureMessage();
            }
        }
        else {
            $a_values['td_id'] = $this->a_post['td_id'];
            try {
                $o_dir->update($a_values, ['tp_id']);
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::failureMessage();
            }
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Creates or Updates a twig prefix record.
     *
     * @return string
     */
    private function savePrefix():string
    {
        $o_prefix = new TwigPrefixModel($this->o_db);
        $o_prefix->setupElog($this->o_di);
        $a_message = ViewHelper::successMessage('testing');
        $a_values = [
            'tp_prefix'  => $this->a_post['tp_prefix'],
            'td_path'    => $this->a_post['tp_path'],
            'tp_active'  => empty($this->a_post['tp_active']) ? 'false' : $this->a_post['tp_active'],
            'tp_default' => empty($this->a_post['tp_default']) ? 'false' : $this->a_post['tp_default']
        ];
        if ($this->form_action === 'update_tp') {
            $a_values['tp_id'] = $this->a_post['tp_id'];
        }
        try {
            $a_values = $o_prefix->clearDefaultPrefix($a_values);
            if ($this->form_action === 'new_tp') {
                $o_prefix->create($a_values);
            }
            else {
                $o_prefix->update($a_values);
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage();
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Creates or updates a new twig template record.
     *
     * @return string
     */
    private function saveTpl():string
    {
        $o_tpl = new TwigTemplatesModel($this->o_db);
        $o_tpl->setupElog($this->o_di);
        $a_message = ViewHelper::codeMessage('testing.');
        $tpl_name = Strings::makeSnakeCase($this->a_post['tpl_name']);
        $a_values = [
            'td_id'         => $this->a_post['td_id'],
            'tpl_name'      => $tpl_name,
            'tpl_immutable' => empty($this->a_post['tpl_immutable']) ? 'false' : $this->a_post['tpl_immutable']
        ];
        try {
            if ($this->form_action === 'new_tpl') {
                $o_tpl->create($a_values);
            }
            else {
                $o_tpl->update($a_values, ['td_id']);
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage();
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
        $a_message = ViewHelper::codeMessage('Testing');
        $o_tc = new TwigComplexModel($this->o_di);
        if ($o_tc->canBeDeleted('dir', $this->a_post['td_id'])) {
            $o_dir = new TwigDirsModel($this->o_db);
            $o_dir->setupElog($this->o_di);
            try {
                $o_dir->delete($this->a_post['td_id']);
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::failureMessage();
            }
        }
        else {
            $a_message = ViewHelper::errorMessage('The directory has templates still assigned to it.');
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
        $a_message = ViewHelper::codeMessage('Testing');
        $o_tc = new TwigComplexModel($this->o_di);
        if ($o_tc->canBeDeleted('prefix', $this->a_post['tp_id'])) {
            $o_prefix = new TwigPrefixModel($this->o_db);
            $o_prefix->setupElog($this->o_di);
            try {
                $o_prefix->delete($this->a_post['tp_id']);
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::failureMessage();
            }
        }
        else {
            $a_message = ViewHelper::errorMessage('The prefix has directories still assigned to it.');
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
        $a_message = ViewHelper::codeMessage('Testing.');
        $o_tc = new TwigComplexModel($this->o_di);
        if ($o_tc->canBeDeleted('tpl', $this->a_post['tpl_id'])) {
            $o_tpl = new TwigTemplatesModel($this->o_db);
            $o_tpl->setupElog($this->o_di);
            try {
                $o_tpl->delete($this->a_post['tpl_id']);
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::failureMessage();
            }
        }
        else {
            $a_message = ViewHelper::errorMessage('The template is immutable.');
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
                if (empty($this->a_post['tpl_id'])) {
                    return $this->o_view->render(ViewHelper::errorMessage('Could not delete the template, unknown template.'));
                }
                return $this->o_view->renderDeleteTpl($this->a_post['tpl_id']);
            case 'tp':
                if (empty($this->a_post['tp_id'])) {
                    return $this->o_view->render(ViewHelper::errorMessage('Could not delete the twig prefix, unknown prefix.'));
                }
                return $this->o_view->renderDeleteTp($this->a_post['tp_id']);
            case 'dir':
                if (empty($this->a_post['td_id'])) {
                    return $this->o_view->render(ViewHelper::errorMessage('Could not delete the twig prefix, unknown directory.'));
                }
                return $this->o_view->renderDeleteDir($this->a_post['td_id']);
            default:
                $a_message = ViewHelper::errorMessage('Missing a valid value.');
                return $this->o_view->render($a_message);
        }
    }
}
