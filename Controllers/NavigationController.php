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
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Views\NavigationView;

/**
 * Class NavigationController - for the Navigation Management.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-11-26 15:07:20
 * @change_log
 * - v1.0.0-alpha.1 - Updated for php8, still has bugs          - 2021-11-26 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2016-04-15 wer
 */
class NavigationController implements ManagerControllerInterface
{
    use ConfigControllerTraits;

    /** @var NavComplexModel model object */
    protected NavComplexModel $o_model;
    /** @var NavigationView view object */
    protected NavigationView $o_view;

    /**
     * NavigationController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view = new NavigationView($o_di);
        $this->o_model = new NavComplexModel($o_di);
    }

    /**
     * Main method used to route the activity.
     *
     * @return string
     */
    public function route():string
    {
        return match ($this->form_action) {
            'new'           => $this->o_view->renderForm(),
            'modify'        => $this->o_view->renderForm($this->a_post['nav_id']),
            'update'        => $this->update(),
            'verify_delete' => $this->verifyDelete(),
            'save'          => $this->save(),
            'delete'        => $this->delete(),
            default         => $this->o_view->renderList(),
        };
    }

    ### Required by Interface ###
    /**
     * Method for saving data.
     *
     * @return string
     */
    public function save():string
    {
        try {
            $this->o_model->save($this->a_post);
            $a_msg = ViewHelper::successMessage();
        }
        catch (ModelException) {
            $a_msg = ViewHelper::failureMessage('Unable to save the record.');
        }
        if ($this->use_cache) {
            $this->o_cache->clearTag('nav');
        }
        return $this->o_view->renderList($a_msg);
    }

    /**
     * Method for updating data.
     * Required by interface, stub for self::save().
     *
     * @return string
     */
    public function update():string
    {
        return $this->save();
    }

    /**
     * Method to display the verify delete form.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        $a_values = [
            'what'         => 'Navigation ',
            'name'         => 'Navigation',
            'form_action'  => $this->o_router->getRequestUri(),
            'btn_value'    => 'Navigation',
            'hidden_name'  => 'nav_id',
            'hidden_value' => $this->a_post['nav_id']
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
    public function delete():string
    {
        try {
            $this->o_model->delete($this->a_post);
            $a_msg = ViewHelper::successMessage();
        }
        catch (ModelException) {
            $a_msg = ViewHelper::failureMessage('Unable to delete the nav record.');
        }
        if ($this->use_cache) {
            $this->o_cache->clearTag('nav');
        }
        return $this->o_view->renderList($a_msg);
    }
}
