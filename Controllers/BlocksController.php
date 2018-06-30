<?php
/**
 * Class BlocksController.
 * @package Ritc_Library
 */

namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ConfigControllerInterface;
use Ritc\Library\Models\BlocksModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Views\BlocksView;

/**
 * Manages Blocks.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-03 16:35:26
 * @change_log
 * - v1.0.0 - Initial version.                                    - 2018-06-03 wer
 */
class BlocksController implements ConfigControllerInterface
{
    use LogitTraits, ControllerTraits;

    /** @var BlocksModel */
    private $o_model;
    /** @var BlocksView  */
    private $o_view;

    /**
     * BlocksController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->a_object_names = ['o_model'];
        $this->o_view = new BlocksView($o_di);
        $this->o_model = new BlocksModel($this->o_db);
        $this->setupElog($o_di);
    }

    /**
     * Main router for controller as required by interface.
     *
     * @return string
     */
    public function route():string
    {
        switch ($this->form_action) {
            case 'verify':
                return $this->verifyDelete();
            case 'update':
                return $this->update();
            case 'save_new':
                return $this->save();
            case 'delete':
                return $this->delete();
            default:
                return $this->o_view->render();
        }
    }

    /**
     * Method for saving data.
     *
     * @return string
     */
    public function save():string
    {
        $a_blocks = $this->a_post['blocks'];
        try {
            $this->o_model->create($a_blocks);
            $a_message = ViewHelper::successMessage();
            if ($this->use_cache) {
                $this->o_cache->clearTag('blocks');
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('Unable to save the new block.');
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Method for updating data.
     *
     * @return string
     */
    public function update():string
    {
        $a_blocks = $this->a_post['blocks'];
        if (empty($a_blocks['b_active'])) {
            $a_blocks['b_active'] = 'false';
        }
        if (empty($a_blocks['b_immutable'])) {
            $a_blocks['b_immutable'] = 'false';
        }
        try {
            $this->o_model->update($a_blocks, ['b_name', 'b_type']);
            if ($this->use_cache) {
                $this->o_cache->clearTag('blocks');
            }
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('Unable to update the block.');
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Method to display the verify delete form.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        $a_values = [
            'what'         => 'block',
            'name'         => $this->a_post['blocks']['b_name'],
            'form_action'  => '/manager/config/blocks/',
            'btn_value'    => 'Block',
            'hidden_name'  => 'b_id',
            'hidden_value' => $this->a_post['blocks']['b_id'],
        ];
        $a_options = [
            'tpl'      => 'verify_delete',
            'location' => '/manager/config/blocks/'
        ];
        return $this->o_view->renderVerifyDelete($a_values, $a_options);
    }

    /**
     * Method to delete data.
     *
     * @return string
     */
    public function delete():string
    {
        $b_id = $this->a_post['b_id'];
        try {
            $this->o_model->delete($b_id);
            $a_message = ViewHelper::successMessage();
            if ($this->use_cache) {
                $this->o_cache->clearTag('blocks');
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('Unable to delete the block.');
        }
        return $this->o_view->render($a_message);
    }
}
