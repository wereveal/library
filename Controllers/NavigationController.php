<?php
/**
 * @brief     Main Controller for the Navigation Management.
 * @ingroup   lib_controllers
 * @file      NavigationController * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-04-15 11:53:36
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2016-04-15 wer
 * @todo NavigationController- Everything
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\NavigationAdminView;

/**
 * Class NavigationController.
 * @class   NavigationController
 * @package Ritc\Library\Controllers
 */
class NavigationController implements ManagerControllerInterface
{
    use ControllerTraits, LogitTraits;

    protected $o_model;
    protected $o_view;

    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupController($o_di);
        $this->o_view = new NavigationAdminView($o_di);
        $this->o_model = new NavComplexModel($this->o_db);
    }

    /**
     * Main method used to render the page.
     * @return string
     */
    public function route()
    {
        $meth = __METHOD__ . '.';
        $log_message = 'route array: ' . var_export($this->a_router_parts, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $this->logIt("main action: " . $this->main_action, LOG_OFF, $meth . __LINE__);
        switch($this->main_action) {
            case 'new':
                return $this->o_view->renderForm();
            case 'save':
                return $this->save();
            case 'delete':
                return $this->delete();
            case 'modify':
                return $this->o_view->renderForm();
            case 'update':
                return $this->update();
            default:
                return $this->o_view->renderList();
        }
    }

    ### Reuqired by Interface ###
    /**
     * Method for saving data.
     * @return string
     */
    public function save()
    {

        return $this->o_view->renderList();
    }

    /**
     * Method for updating data.
     * @return string
     */
    public function update()
    {
        return $this->o_view->renderList();
    }

    /**
     * Method to display the verify delete form.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerifyDelete($this->a_post);
    }

    /**
     * Method to delete data.
     * @return string
     */
    public function delete()
    {
        return $this->o_view->renderList();
    }

    ### Extra ###
    private function modify()
    {
        switch ($this->form_action) {
            case 'verify':
                return $this->verifyDelete();
            case 'update':
                return $this->update();
            default:
                $a_message = ViewHelper::failureMessage('A problem occured. Please try again.');
                return $this->o_view->renderList($a_message);
        }
    }
}
