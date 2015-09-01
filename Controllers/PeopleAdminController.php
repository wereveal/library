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
 *  @todo everything - too many things have changed to not go over every method
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\MangerControllerInterface;
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
        $this->a_route_parts = $this->o_router->getRouteParts();
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
        $main_action = $a_route_parts['route_action'];
        $form_action = $a_route_parts['form_action'];
        if ($main_action == 'save' || $main_action == 'update' || $main_action == 'delete') {
            if ($this->o_session->isNotValidSession($this->a_post, true)) {
                header("Location: " . SITE_URL . '/manager/login/');
            }
        }

        switch ($main_action) {
            case 'save':
                return $this->save();
            case 'update':
                if ($form_action == 'verify') {
                    return $this->verifyDelete($a_route_parts);
                }
                elseif ($form_action == 'update') {
                    return $this->update();
                }
                else {
                    $a_message = [
                        'message' => 'A Problem Has Occured. Please Try Again.',
                        'type'    => 'failure'
                    ];
                    return $this->o_view->renderList($a_message);
                }
            case 'delete':
                return $this->delete();
            case '':
            default:
                return $this->o_view->renderList();
        }
    }

    public function save()
    {
        // save user record
        // save user group map record
        return '';
    }

    /**
     * Updates the user record and then displays the list of people.
     * @return string
     */
    public function update()
    {
        // update user record
        return '';
    }
    /**
     * Display the form to verify delete.
     * @return string
     */
    public function verifyDelete()
    {
        return '';
    }

    /**
     * Deletes the user record and displays the list of people.
     * @return string
     */
    public function delete()
    {
        return '';
    }
}
