<?php
/**
 * Class NavgroupsController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ConfigControllerInterface;
use Ritc\Library\Models\NavgroupsModel;
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
 * @todo NavgroupsController.php - Test
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
            'new'    => $this->o_view->render([]),
            'modify' => $this->o_view->render($this->a_post),
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
        $a_message    = [];
        $o_ng         = new NavgroupsModel($this->o_db);
        $ng_active    = $this->a_post['ng_active'] ?? 'false';
        $ng_default   = $this->a_post['ng_default'] ?? 'false';
        $ng_immutable = $this->a_post['ng_immutable'] ?? 'false';
        $a_values     = [
            'ng_name'      => $this->a_post['ng_name'],
            'ng_active'    => $ng_active,
            'ng_default'   => $ng_default,
            'ng_immutable' => $ng_immutable
        ];
        try {
            $o_ng->create($a_values);
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage("Could not save the new record: <br>" . $e->errorMessage());
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
        $o_ng         = new NavgroupsModel($this->o_db);
        $ng_active    = $this->a_post['ng_active'] ?? 'false';
        $ng_default   = $this->a_post['ng_default'] ?? 'false';
        $ng_immutable = $this->a_post['ng_immutable'] ?? 'false';
        $a_values     = [
            'ng_id'        => $this->a_post['ng_id'],
            'ng_name'      => $this->a_post['ng_name'],
            'ng_active'    => $ng_active,
            'ng_default'   => $ng_default,
            'ng_immutable' => $ng_immutable
        ];
        try {
            $o_ng->update($a_values);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage("Could not update the record: <br>" . $e->errorMessage());
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
        $ng_id     = $this->a_post['ng_id'];
        $ng_name   = $this->a_post['ng_name'];
        $a_values  = [
            'what'         => 'Navgroup ',
            'name'         => $ng_name,
            'submit_value' => 'delete',
            'form_action'  => '/manager/config/navgroups/',
            'btn_value'    => 'Navgroup',
            'hidden_name'  => 'ng_id',
            'hidden_value' => $ng_id,
        ];
        $a_options = [
            'a_message' => ['type' => 'success', 'message' => 'Success'],
            'fallback'  => 'render' // if something goes wrong, which method to fallback
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
        $o_ng = new NavgroupsModel($this->o_db);
        try {
            $o_ng->delete($this->a_post['ng_id']);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage("Could not delete the record:<br>" . $e->errorMessage());
        }
        return $this->o_view->render($a_message);
    }
}
