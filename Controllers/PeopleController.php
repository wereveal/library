<?php
/**
 * @brief     Controller for the Configuration page.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/PeopleController.phpnamespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.1.1
 * @date      2017-12-05 10:33:59
 * @note <b>Change Log</b>
 * - v1.1.1   - bug fix                                          - 2017-12-05 wer
 * - v1.1.0   - updated to use ConfigControllerTraits            - 2017-11-28 wer
 * - v1.0.2   - bug fix                                          - 2016-03-08 wer
 * - v1.0.1   - bug fixes                                        - 11/27/2015 wer
 * - v1.0.0   - initial working version                          - 11/12/2015 wer
 * - v1.0.0β4 - Realized this is nowhere near done               - 01/06/2015 wer
 *                This code was copied from somewhere else and
 *                not modified to fit the need.
 * - v1.0.0β3 - refactoring of namespaces                        - 12/05/2014 wer
 * - v1.0.0β2 - Adjusted to match file name change               - 11/13/2014 wer
 * - v1.0.0β1 - Initial version                                  - 04/02/2014 wer
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\PeopleComplexModel;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\PeopleView;

/**
 * Class PeopleController.
 * @class   PeopleController
 * @package Ritc\Library\Controllers
 */
class PeopleController implements ManagerControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var \Ritc\Library\Models\PeopleComplexModel */
    private $o_complex;
    /** @var PeopleModel */
    private $o_people;
    /** @var PeopleView */
    private $o_view;

    /**
     * PeopleController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupManagerController($o_di);
        $this->o_view        = new PeopleView($o_di);
        $this->o_people      = new PeopleModel($this->o_db);
        $this->o_complex     = new PeopleComplexModel($this->o_db);
        if (DEVELOPER_MODE) {
            $this->o_complex->setElog($this->o_elog);
            $this->o_people->setElog($this->o_elog);
            $this->o_view->setElog($this->o_elog);
        }
    }

    /**
     * Routes the code to the appropriate methods and classes. Returns a string.
     * @return string html to be displayed.
     */
    public function route()
    {
        $a_post        = $this->a_post;
        $form_action   = $this->form_action;
        $this->logIt('Post: ' . var_export($a_post, TRUE), LOG_OFF, __METHOD__);
        switch ($form_action) {
            case 'new':
                return $this->o_view->renderNew();
            case 'modify':
                if (!empty($a_post['people_id'])) {
                    return $this->o_view->renderModify($a_post['people_id']);
                }
                $a_message = ViewHelper::failureMessage('An error occurred and the person could not be modified.');
                break;
            case 'verify':
                return $this->verifyDelete();
            case 'save':
                $a_message = $this->save();
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
     * Saves the person mapped to group(s).
     * Returns array that specifies succsss or failure.
     * @return array a message regarding outcome.
     */
    public function save()
    {
        $a_person = $this->o_complex->createNewPersonArray($this->a_post);
        $error_message = "Opps, the person was not saved.";
        switch ($a_person) {
            case 'login-missing':
                return ViewHelper::failureMessage($error_message . " -- missing Login ID.");
            case 'name-missing':
                return ViewHelper::failureMessage($error_message . " -- missing Name.");
            case 'password-missing':
                return ViewHelper::failureMessage($error_message . " -- missing password.");
            case 'login-exists':
                return ViewHelper::failureMessage($error_message . " -- the login id already exists.");
            case 'short-exists':
                return ViewHelper::failureMessage($error_message . " -- the short name already exists.");
            case 'group-missing':
                return ViewHelper::failureMessage($error_message . " -- missing at least one group.");
            case true:
            default:
                try {
                    $results = $this->o_complex->savePerson($a_person);
                    if ($results === false) {
                        return ViewHelper::failureMessage($error_message);
                    }
                    return ViewHelper::successMessage("Success, the person was saved.");
                }
                catch (ModelException $e) {
                    if (DEVELOPER_MODE) {
                        $error_message .= " " . $e->errorMessage();
                    }
                    return ViewHelper::failureMessage($error_message);
                }
        }
    }

    /**
     * Updates the user record.
     * @return array a message regarding outcome.
     */
    public function update()
    {
        $meth = __METHOD__ . '.';
        $a_person = $this->a_post['person'];
        $a_person = $this->o_people->setPersonValues($a_person);
        $addendum = '';
        if (empty($a_person['people_id'])) {
            return ViewHelper::errorMessage('An unexpected error occurred, please try again.');
        }
        try {
            $a_previous_values = $this->o_people->read(['people_id' => $a_person['people_id']]);
        }
        catch (ModelException $e) {
            return ViewHelper::failureMessage("The person could not be found in the database.");
        }
        if (!empty($a_person['login_id']) && $a_previous_values[0]['login_id'] !== $a_person['login_id']) {
            if ($this->o_people->isExistingLoginId($a_person['login_id'])) {
                $a_person['login_id'] = $a_previous_values[0]['login_id'];
                $addendum .= '<br>The login id was not changed because the new value aleady existed for someone else.';
            }
        }
        if (!empty($a_person['short_name']) && $a_previous_values[0]['short_name'] !== $a_person['short_name']) {
            if ($this->o_people->isExistingShortName($a_person['short_name'])) {
                $a_person['short_name'] = $a_previous_values[0]['short_name'];
                $addendum .= '<br>The alias was not changed because the new value aleady existed for someone else.';
            }
        }
        if (!isset($this->a_post['groups']) || count($this->a_post['groups']) < 1) {
            return ViewHelper::failureMessage("Opps, the person was not saved. The person must be assigned to at least one group.");
        }
        $a_person['groups'] = $this->a_post['groups'];
        $this->logIt('Person values: ' . var_export($a_person, TRUE), LOG_ON, $meth . __LINE__);
        try {
            $results = $this->o_complex->savePerson($a_person);
            if ($results !== false) {
                if ($addendum != '') {
                    $addendum = '<br><b class="red">However' . $addendum . '</b>';
                }
                return ViewHelper::successMessage("Success! The person was updated." . $addendum);
            }
            return ViewHelper::failureMessage("Opps, the person was not updated.");
        }
        catch (ModelException $e) {
            return ViewHelper::failureMessage("Opps, the person was not updated.");
        }
    }

    /**
     * Display the form to verify delete.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerifyDelete($this->a_post, $this->a_router_parts['request_uri']);
    }

    /**
     * Deletes the user record.
     * @return array a message regarding outcome.
     */
    public function delete()
    {
        try {
            if ($this->o_complex->deletePerson($this->a_post['people_id'])) {
                return ViewHelper::successMessage();
            }
            return ViewHelper::failureMessage($this->o_people->getErrorMessage());
        }
        catch (ModelException $e) {
            return ViewHelper::failureMessage($e->errorMessage());
        }
    }
}
