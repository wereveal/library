<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file ConstantsAdminController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class ConstantsAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.2.1
 *  @date 2015-09-25 10:48:45
 *  @note A file in Library
 *  @note <pre><b>Change Log</b>
 *      v1.2.1 - code clean up                                   - 09/25/2015 wer
 *      v1.2.0 - No longer extends Base class, uses LogitTraits  - 08/19/2015 wer
 *      v1.1.0 - changed to implement ManagerControllerInterface - 01/16/2015 wer
 *               This class should only be called from the main
 *               manager controller which does session validation.
 *      v1.0.2 - changed to use the new Di class                 - 11/17/2014 wer
 *      v1.0.1 - Adjusted to match file name change              - 11/13/2014 wer
 *      v1.0.0 - Initial version                                 - 04/02/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Views\ConstantsAdminView;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

class ConstantsAdminController implements MangerControllerInterface
{
    use LogitTraits;

    private $a_post;
    private $a_router_parts;
    private $o_di;
    private $o_model;
    private $o_view;

    public function __construct(Di $o_di)
    {
        $this->o_di     = $o_di;
        $o_db           = $this->o_di->get('db');
        $this->o_model  = new ConstantsModel($o_db);
        $this->o_view   = new ConstantsAdminView($this->o_di);
        $o_router       = $this->o_di->get('router');
        $a_router_parts = $o_router->getRouterParts();
        $this->a_post   = $a_router_parts['post'];
        $this->a_router_parts = $a_router_parts;
        if (DEVELOPER_MODE) { // instead of needing the setElog method
            $this->o_elog = $o_di->get('elog');
        }
    }

    /**
     *  Renders the html based on the route requested.
     *  @return string html to be displayed.
    **/
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
        $results = $this->o_model->create($a_constants);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new configuration could not be saved.');
        }
        return $this->o_view->renderList($a_message);
    }
    /**
     * Updates the constants record and returns the list of constants with a message.
     * @return mixed
     */
    public function update()
    {
        $a_constants = $this->a_post['constant'];
        $results = $this->o_model->update($a_constants);
        if ($results !== false) {
            $a_message = ViewHelper::successMessage();
        }
        else {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The route could not be updated.');
        }
        return $this->o_view->renderList($a_message);
    }
    /**
     *  Returns the html to display a form to verify the delete.
     *  Required by Interface.
     *  @return string
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
