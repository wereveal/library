<?php
/**
 * Class NavgroupsController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ConfigControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Views\NavgroupsView;

/**
 * Controller for the Navgroups manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-06-19 12:05:41
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-06-19 wer
 * @todo NavgroupsController.php - Everything
 */
class NavgroupsController implements ConfigControllerInterface
{
    use ConfigControllerTraits;

    private NavgroupsView $o_view;

    /**
     * NavgroupsController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view = new NavgroupsView($o_di);
    }

    /**
     * Main method used to route the page to the appropriate controller/view/model.
     *
     * @return string
     */
    public function route():string
    {
        return match ($this->form_action) {
            'new'    => $this->o_view->renderForm(),
            'modify' => $this->o_view->renderForm($this->a_post),
            'verify' => $this->verifyDelete(),
            'save'   => $this->save(),
            'update' => $this->update(),
            'delete' => $this->delete(),
            default  => $this->o_view->render()
        };
    }

    /**
     * Method for saving data.
     *
     * @return string
     */
    public function save():string
    {
        $a_message = [];
        return $this->o_view->render($a_message);
    }

    /**
     * Method for updating data.
     *
     * @return string
     */
    public function update():string
    {
        return '';
    }

    /**
     * Method to display the verify delete form.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        return '';
    }

    /**
     * Method to delete data.
     *
     * @return string
     */
    public function delete():string
    {
        return '';
    }
}
