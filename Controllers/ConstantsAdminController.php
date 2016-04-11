<?php
/**
 * @brief     Controller for the Configuration page.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/ConstantsAdminController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.3.2
 * @date      2016-04-11 11:30:43
 * @note <b>Change Log</b>
 * - v1.3.2 - bug fix                                         - 2016-04-11 wer
 * - v1.3.1 - bug fix                                         - 2016-03-08 wer
 * - v1.3.0 - added immutable code                            - 10/07/2015 wer
 * - v1.2.1 - code clean up                                   - 09/25/2015 wer
 * - v1.2.0 - No longer extends Base class, uses LogitTraits  - 08/19/2015 wer
 * - v1.1.0 - changed to implement ManagerControllerInterface - 01/16/2015 wer
 *             This class should only be called from the main
 *             manager controller which does session validation.
 * - v1.0.2 - changed to use the new Di class                 - 11/17/2014 wer
 * - v1.0.1 - Adjusted to match file name change              - 11/13/2014 wer
 * - v1.0.0 - Initial version                                 - 04/02/2014 wer
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Router;
use Ritc\Library\Views\ConstantsAdminView;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class ConstantsAdminController
 * @class ConstantsAdminController
 * @package Ritc\Library\Controllers
 */
class ConstantsAdminController implements ManagerControllerInterface
{
    use LogitTraits;

    /** @var array */
    private $a_post;
    /** @var array */
    private $a_router_parts;
    /** @var Di */
    private $o_di;
    /** @var ConstantsModel */
    private $o_model;
    /** @var ConstantsAdminView */
    private $o_view;

    /**
     * ConstantsAdminController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di     = $o_di;
        /** @var DbModel $o_db */
        $o_db           = $this->o_di->get('db');
        $this->o_model  = new ConstantsModel($o_db);
        $this->o_view   = new ConstantsAdminView($this->o_di);
        /** @var Router $o_router */
        $o_router       = $this->o_di->get('router');
        $a_router_parts = $o_router->getRouteParts();
        $this->a_post   = $a_router_parts['post'];
        $this->a_router_parts = $a_router_parts;
        if (DEVELOPER_MODE) { // instead of needing the setElog method
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }

    /**
     * Renders the html based on the route requested.
     * @return string html to be displayed.
     */
    public function render()
    {
        $main_action   = $this->a_router_parts['route_action'];
        $form_action   = $this->a_router_parts['form_action'];
        $url_action    = isset($this->a_router_parts['url_actions'][0])
            ? $this->a_router_parts['url_actions'][0]
            : '';
        if ($main_action == '' && $url_action != '') {
            $main_action = $url_action;
        }
        switch ($main_action) {
            case 'modify':
                switch ($form_action) {
                    case 'verify':
                        return $this->verifyDelete();
                    case 'update':
                        return $this->update();
                    default:
                        $a_message = ViewHelper::failureMessage('A problem occured. Please try again.');
                        return $this->o_view->renderList($a_message);
                }
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
     * Saves the constants record and returns the list of constants with a message.
     * @return mixed
     */
    public function save()
    {
        if ($this->a_router_parts['form_action'] != 'save_new') {
            $a_message = ViewHelper::errorMessage('An error occurred. The record could not be saved.');
            return $this->o_view->renderList($a_message);
        }
        $a_constants = $this->a_post['constant'];
        if (!isset($a_constants['const_immutable'])) {
            $a_constants['const_immutable'] = 0;
        }
        $results = $this->o_model->create($a_constants);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new constant could not be saved.');
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Updates the constants record and returns the list of constants with a message.
     * @return mixed
     */
    public function update()
    {
        $meth = __METHOD__ . '.';
        $a_constants = $this->a_post['constant'];
        if (!isset($a_constants['const_immutable'])) {
            $a_constants['const_immutable'] = 0;
        }
        $this->logIt('' . var_export($a_constants, TRUE), LOG_OFF, $meth . __LINE__);
        $results = $this->o_model->update($a_constants);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The constant could not be updated.');
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Returns the html to display a form to verify the delete.
     * Required by Interface.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post);
    }

    /**
     * Deletes the constants record and returns the list of constants with a message.
     * @return mixed
     */
    public function delete()
    {
        if ($this->a_router_parts['form_action'] != 'delete') {
            $a_message = ViewHelper::errorMessage('An error occurred. The record could not be deleted.');
            return $this->o_view->renderList($a_message);
        }
        $const_id = $this->a_post['const_id'];
        if ($const_id == -1 || $const_id == '') {
            $a_message = ViewHelper::errorMessage('An Error Has Occured. The config record id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        $a_results = $this->o_model->delete($const_id);
        return $this->o_view->renderList($a_results);
    }
}
