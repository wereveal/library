<?php
/**
 * @brief     Controller for the Configuration page.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/PeopleController.phpnamespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.2+2
 * @date      2016-04-11 08:06:39
 * @note <b>Change Log</b>
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

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\PeopleComplexModel;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\PeopleView;

/**
 * Class PeopleController.
 * @class   PeopleController
 * @package Ritc\Library\Controllers
 */
class PeopleController implements ManagerControllerInterface
{
    use LogitTraits;

    /** @var array */
    private $a_route_parts;
    /** @var array */
    private $a_post_values;
    /** @var \Ritc\Library\Models\PeopleComplexModel */
    private $o_complex;
    /** @var Di */
    private $o_di;
    /** @var PeopleModel */
    private $o_people;
    /** @var Router */
    private $o_router;
    /** @var Session */
    private $o_session;
    /** @var PeopleView */
    private $o_view;

    /**
     * PeopleController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di          = $o_di;
        $o_db                = $o_di->get('db');
        $this->o_view        = new PeopleView($o_di);
        $this->o_session     = $o_di->get('session');
        $this->o_router      = $o_di->get('router');
        $this->o_people      = new PeopleModel($o_db);
        $this->o_complex     = new PeopleComplexModel($o_db);
        $this->a_route_parts = $this->o_router->getRouteParts();
        $this->a_post_values = $this->a_route_parts['post'];
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
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
        $meth = __METHOD__ . '.';
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
        $this->logIt('Post: ' . var_export($a_post, TRUE), LOG_OFF, $meth . __LINE__);
        switch ($main_action) {
            case 'new':
                return $this->o_view->renderNew();
            case 'save':
                $a_message = $this->save();
                break;
            case 'modify':
                $people_id = $a_post['people_id'];
                return $this->o_view->renderModify($people_id);
            case 'update':
                if ($form_action == 'verify') {
                    return $this->verifyDelete();
                }
                elseif ($form_action == 'update') {
                    $a_message = $this->update();
                }
                else {
                    $a_message = ViewHelper::failureMessage('A problem has occured. Could not determine action');
                }
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
        $meth = __METHOD__ . '.';
        $a_person = $this->a_post_values['person'];
        $a_person = $this->setPersonValues($a_person);
        if ($a_person === false) {
            return ViewHelper::failureMessage("Opps, the person was not saved -- missing information, either Login ID or Name.");
        }
        if ($this->o_people->isExistingLoginId($a_person['login_id'])) {
            return ViewHelper::failureMessage("Opps, the Login ID already exists.");
        }
        if ($this->o_people->isExistingShortName($a_person['short_name'])) {
            $a_person['short_name'] = $this->createShortName($a_person['short_name']);
        }
        if (!isset($this->a_post_values['groups']) || count($this->a_post_values['groups']) < 1) {
            return ViewHelper::failureMessage("Opps, the person was not saved. The person must be assigned to at least one group.");
        }
        $a_person['groups'] = $this->a_post_values['groups'];
        $this->logIt('Person values: ' . var_export($a_person, TRUE), LOG_OFF, $meth . __LINE__);
        if ($this->o_complex->savePerson($a_person) !== false) {
            return ViewHelper::successMessage("Success! The person was saved.");
        }
        return ViewHelper::failureMessage("Opps, the person was not saved.");
    }

    /**
     * Updates the user record.
     * @return array a message regarding outcome.
     */
    public function update()
    {
        $meth = __METHOD__ . '.';
        $a_person = $this->a_post_values['person'];
        $a_person = $this->setPersonValues($a_person);
        $addendum = '';
        if ($a_person === false) {
            return ViewHelper::failureMessage("Opps, the person was not updated.");
        }
        $a_previous_values = $this->o_people->read(['people_id' => $a_person['people_id']]);
        if ($a_previous_values[0]['login_id'] !== $a_person['login_id']) {
            if ($this->o_people->isExistingLoginId($a_person['login_id'])) {
                $a_person['login_id'] = $a_previous_values[0]['login_id'];
                $addendum .= '<br>The login id was not changed because the new value aleady existed for someone else.';
            }
        }
        if ($a_previous_values[0]['short_name'] !== $a_person['short_name']) {
            if ($this->o_people->isExistingShortName($a_person['short_name'])) {
                $a_person['short_name'] = $a_previous_values[0]['short_name'];
                $addendum .= '<br>The alias was not changed because the new value aleady existed for someone else.';
            }
        }
        if (!isset($this->a_post_values['groups']) || count($this->a_post_values['groups']) < 1) {
            return ViewHelper::failureMessage("Opps, the person was not saved. The person must be assigned to at least one group.");
        }
        $a_person['groups'] = $this->a_post_values['groups'];
        $this->logIt('Person values: ' . var_export($a_person, TRUE), LOG_OFF, $meth . __LINE__);
        if ($this->o_complex->savePerson($a_person) !== false) {
            if ($addendum != '') {
                $addendum = '<br><b class="red">However' . $addendum . '</b>';
            }
            return ViewHelper::successMessage("Success! The person was updated." . $addendum);
        }
        return ViewHelper::failureMessage("Opps, the person was not updated.");
    }

    /**
     * Display the form to verify delete.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerifyDelete($this->a_post_values);
    }

    /**
     * Deletes the user record.
     * @return array a message regarding outcome.
     */
    public function delete()
    {
        if ($this->o_complex->deletePerson($this->a_post_values['people_id'])) {
            return ViewHelper::successMessage();
        }
        return ViewHelper::failureMessage($this->o_people->getErrorMessage());
    }

    ### Utility Methods ###
    /**
     * Creates a short name/alias if none is provided
     * @param  string $long_name
     * @return string the short name.
     */
    private function createShortName($long_name = '')
    {
        if (strpos($long_name, ' ') !== false) {
            $a_real_name = explode(' ', $long_name);
            $short_name = '';
            foreach($a_real_name as $name) {
                $short_name .= strtoupper(substr($name, 0, 1));
            }
        }
        else {
            $short_name = strtoupper(substr($long_name, 0, 8));
        }
        if ($this->o_people->isExistingShortName($short_name)) {
            $short_name = $this->createShortName(substr($short_name, 0, 6) . rand(0,99));
        }
        return $short_name;
    }

    /**
     * Returns an array to be used to create or update a people record.
     * @param array $a_person
     * @return array|bool
     */
    private function setPersonValues(array $a_person = array())
    {
        $a_required_keys = array(
            'login_id',
            'password'
        );
        if (Arrays::hasBlankValues($a_person, $a_required_keys)) {
            return false;
        }
        $a_fix_these = ['login_id', 'real_name', 'short_name', 'description'];
        foreach ($a_fix_these as $key) {
            if (isset($a_person[$key])) {
                $a_person[$key] = Strings::removeTagsWithDecode($a_person[$key], ENT_QUOTES);
                if ($key == 'short_name') {
                    $a_person[$key] = Strings::makeAlphanumeric($a_person[$key]);
                }
            }
        }
        $a_person['login_id'] = Strings::makeAlphanumericPlus($a_person['login_id']);

        $a_allowed_keys   = $a_required_keys;
        $a_allowed_keys[] = 'real_name';
        $a_allowed_keys[] = 'people_id';
        $a_allowed_keys[] = 'short_name';
        $a_allowed_keys[] = 'description';
        $a_allowed_keys[] = 'is_logged_in';
        $a_allowed_keys[] = 'is_active';
        $a_allowed_keys[] = 'is_immutable';
        $a_person = Arrays::createRequiredPairs($a_person, $a_allowed_keys, true);
        if ($a_person['real_name'] == '') {
            $a_person['real_name'] = $a_person['login_id'];
        }
        if ($a_person['short_name'] == '' || !isset($a_person['short_name'])) {
            $a_person['short_name'] = $this->createShortName($a_person['real_name']);
        }
        $a_person['is_logged_in'] = isset($a_person['is_logged_in']) && $a_person['is_logged_in'] == 'true'
            ? 1
            : 0;
        $a_person['is_active'] = isset($a_person['is_active']) && $a_person['is_active'] == 'true'
            ? 1
            : 0;
        $a_person['is_immutable'] = isset($a_person['is_immutable']) && $a_person['is_immutable'] == 'true'
            ? 1
            : 0;
        if ($a_person['people_id'] == '') {
            unset($a_person['people_id']); // this must be a new person.
        }
        if (!isset($a_person['people_id']) && $a_person['login_id'] == '') {
            return false;
        }
        return $a_person;
    }
}