<?php
/**
 *  @brief Controller for the Configuration page.
 *  @file PeopleAdminController.php
 *  @ingroup ritc_library controllers
 *  @namespace Ritc/Library/Controllers
 *  @class PeopleAdminController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β4
 *  @date 2015-01-06 12:14:23
 *  @note A file in Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β4 - Realized this is nowhere near done            - 01/06/2015 wer
 *               This code was copied from somewhere else and
 *               not modified to fit the need.
 *      v1.0.0β3 - refactoring of namespaces                     - 12/05/2014 wer
 *      v1.0.0β2 - Adjusted to match file name change            - 11/13/2014 wer
 *      v1.0.0β1 - Initial version                               - 04/02/2014 wer
 *  </pre>
 *  @TODO write the save method
 *  @TODO write the update method
 *  @TODO write the verifyDelete method
 *  @TODO write the delete method
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\MangerControllerInterface;
use Ritc\Library\Models\PeopleGroupMapModel;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\PeopleAdminView;

class PeopleAdminController implements MangerControllerInterface
{
    use LogitTraits;

    private $a_route_parts;
    private $a_post_values;
    private $o_di;
    private $o_model;
    private $o_router;
    private $o_session;
    private $o_view;

    public function __construct(Di $o_di)
    {
        $this->o_di          = $o_di;
        $o_db                = $o_di->get('db');
        $this->o_view        = new PeopleAdminView($o_di);
        $this->o_session     = $o_di->get('session');
        $this->o_router      = $o_di->get('router');
        $this->o_model       = new PeopleModel($o_db);
        $this->a_route_parts = $this->o_router->getRouterParts();
        $this->a_post_values = $this->a_route_parts['post'];
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
            $this->o_view->setElog($this->o_elog);
        }
    }
    /**
     *  Routes the code to the appropriate methods and classes. Returns a string.
     *  @return string html to be displayed.
    **/
    public function render()
    {
        $a_route_parts = $this->a_route_parts;
        $a_post        = $this->a_post_values;
        $main_action   = $a_route_parts['route_action'];
        $form_action   = $a_route_parts['form_action'];
        $url_action    = isset($a_route_parts['url_actions'][0])
            ? $a_route_parts['url_actions'][0]
            : '';
        if ($main_action == '' && $url_action != '') {
            $main_action = $url_action;
        }
        if ($main_action == 'save' || $main_action == 'update' || $main_action == 'delete') {
            if ($this->o_session->isNotValidSession($this->a_post_values, true)) {
                header("Location: " . SITE_URL . '/manager/login/');
            }
        }
        switch ($main_action) {
            case 'new':
                return $this->o_view->renderNew();
            case 'save':
                $a_message = $this->save();
                break;
            case 'modify':
                if ($form_action == 'verify') {
                    return $this->verifyDelete();
                }
                elseif ($form_action == 'modify') {
                    $people_id = $a_post['people_id'];
                    return $this->o_view->renderModify($people_id);
                }
                else {
                    $a_message = $this->failureMessage('A problem has occured. Could not determine action');
                }
                break;
            case 'update':
                $a_message = $this->update();
                break;
            case 'delete':
                $a_message = $this->delete();
                break;
            case '':
            default:
                $a_message = array();
        }
        return $this->o_view->renderList($a_message);
    }
    /**
     *  Saves the person mapped to group(s).
     *  Returns array that specifies succsss or failure.
     *  @return array
     */
    public function save()
    {
        $a_person = $this->a_post_values['person'];
        $a_person['groups'] = $this->a_post_values['groups'];
        if ($this->o_model->savePerson($a_person) !== false) {
            return ViewHelper::successMessage("Success! The person was saved.");
        }
        return ViewHelper::failureMessage("Opps, the person was not saved.");
    }
    /**
     * Updates the user record and then displays the list of people.
     * @return array
     */
    public function update()
    {
        $a_person = $this->a_post_values['person'];
        $a_person['groups'] = $this->a_post_values['groups'];
        if ($this->o_model->savePerson($a_person) !== false) {
            return ViewHelper::successMessage("Success! The person was updated.");
        }
        return ViewHelper::failureMessage("Opps, the person was not updated.");
    }
    /**
     * Display the form to verify delete.
     * @return array
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerifyDelete($this->a_post_values);
    }
    /**
     * Deletes the user record and displays the list of people.
     * @return array
     */
    public function delete()
    {
        return ViewHelper::failureMessage();
    }

}
