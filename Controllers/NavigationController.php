<?php
/**
 * Class NavigationController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Views\NavigationView;

/**
 * Class NavigationController - for the Navigation Management.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2016-04-15 11:53:36
 * @change_log
 * - v1.0.0-alpha.0 - Initial version                           - 2016-04-15 wer
 */
class NavigationController implements ManagerControllerInterface
{
    use ConfigControllerTraits, LogitTraits;

    /** @var \Ritc\Library\Models\NavComplexModel model object */
    protected $o_model;
    /** @var \Ritc\Library\Views\NavigationView view object */
    protected $o_view;

    /**
     * NavigationController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupManagerController($o_di);
        $this->o_view = new NavigationView($o_di);
        $this->o_model = new NavComplexModel($o_di);
    }

    /**
     * Main method used to render the page.
     *
     * @return string
     */
    public function route(): string
    {
        switch($this->form_action) {
            case 'new':
                return $this->o_view->renderForm();
            case 'modify':
                return $this->o_view->renderForm($this->a_post['nav_id']);
            case 'update':
                return $this->update();
            case 'verify_delete':
                return $this->verifyDelete();
            case 'save':
                return $this->save();
            case 'delete':
                return $this->delete();
            default:
                return $this->o_view->renderList();
        }
    }

    ### Required by Interface ###
    /**
     * Method for saving data.
     *
     * @return string
     */
    public function save(): string
    {
        try {
            $this->o_model->save($this->a_post);
            $a_msg = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_msg = ViewHelper::failureMessage('Unable to save the record.');
        }
        if ($this->use_cache) {
            $this->o_cache->clearTag('nav');
        }
        return $this->o_view->renderList($a_msg);
    }

    /**
     * Method for updating data.
     *
     * @return string
     */
    public function update(): string
    {
        return $this->save();
    }

    /**
     * Method to display the verify delete form.
     *
     * @return string
     */
    public function verifyDelete(): string
    {
        $a_values = [
            'what'         => 'Navigation ',
            'name'         => 'Navigation',
            'form_action'  => $this->o_router->getRequestUri(),
            'btn_value'    => 'Navigation',
            'hidden_name'  => 'nav_id',
            'hidden_value' => $this->a_post['nav_id'],

        ];
        $a_options = [
            'fallback' => 'renderList'
        ];
        return $this->o_view->renderVerifyDelete($a_values, $a_options);
    }

    /**
     * Method to delete data.
     *
     * @return string
     */
    public function delete(): string
    {
        try {
            $this->o_model->delete($this->a_post);
            $a_msg = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_msg = ViewHelper::failureMessage('Unable to delete the nav record.');
        }
        if ($this->use_cache) {
            $this->o_cache->clearTag('nav');
        }
        return $this->o_view->renderList($a_msg);
    }
}
