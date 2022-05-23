<?php
/**
 * Class PeopleController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\CustomException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\PeopleComplexModel;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Views\PeopleView;

/**
 * Class PeopleController - Controller for the Configuration page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-11-26 15:12:16
 * @change_log
 * - v2.0.0   - updated for php8                                - 2021-11-26 wer
 * - v1.1.2   - trait change reflected here                     - 2018-04-21 wer
 * - v1.1.0   - updated to use ConfigControllerTraits           - 2017-11-28 wer
 * - v1.0.0   - initial working version                         - 11/12/2015 wer
 * - v1.0.0β4 - Realized this is nowhere near done              - 01/06/2015 wer
 *              This code was copied from somewhere else and
 *              not modified to fit the need.
 * - v1.0.0β3 - refactoring of namespaces                       - 12/05/2014 wer
 * - v1.0.0β2 - Adjusted to match file name change              - 11/13/2014 wer
 * - v1.0.0β1 - Initial version                                 - 04/02/2014 wer
 */
class PeopleController implements ManagerControllerInterface
{
    use ConfigControllerTraits;

    /** @var PeopleComplexModel object */
    private PeopleComplexModel $o_complex;
    /** @var PeopleModel model object */
    private PeopleModel $o_people;
    /** @var PeopleView view object */
    private PeopleView $o_view;

    /**
     * PeopleController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view        = new PeopleView($o_di);
        $this->o_people      = new PeopleModel($this->o_db);
        try {
            $this->o_complex = new PeopleComplexModel($o_di);
        }
        catch (CustomException $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log('A fatal problem has occurred: ' . $e->getMessage());
            header('Location: ' . SITE_URL);
        }
    }

    /**
     * Routes the code to the appropriate methods and classes. Returns a string.
     *
     * @return string html to be displayed.
     */
    public function route():string
    {
        $a_post        = $this->a_post;
        $form_action   = $this->form_action;
        switch ($form_action) {
            case 'new':
                return $this->o_view->renderNew();
            case 'edit':
                if (!empty($a_post['people_id'])) {
                    return $this->o_view->renderModify($a_post['people_id']);
                }
                $a_message = ViewHelper::failureMessage('An error occurred and the person could not be modified.');
                break;
            case 'verify':
                return $this->verifyDelete();
            case 'save':
                return $this->save();
            case 'update':
                return $this->update();
            case 'delete':
                return $this->delete();
            case '':
            default:
                $a_message = array();
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Saves a new person mapped to group(s).
     * Returns array that specifies succsss or failure.
     *
     * @return string
     */
    public function save():string
    {
        $a_person = $this->o_complex->createNewPersonArray($this->a_post);
        $error_message = 'Opps, the person was not saved.';
        switch ($a_person) {
            case 'login-missing':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- missing Login ID.');
                break;
            case 'name-missing':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- missing Name.');
                break;
            case 'password-missing':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- missing password.');
                break;
            case 'login-exists':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- the login id already exists.');
                break;
            case 'short-exists':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- the short name already exists.');
                break;
            case 'group-missing':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- missing at least one group.');
                break;
            case true:
            default:
                try {
                    $this->o_complex->savePerson($a_person);
                    if ($this->use_cache) {
                        $this->o_cache->clearByKeyPrefix('people');
                    }
                    $a_msg = ViewHelper::successMessage('Success, the person was saved.');
                }
                catch (ModelException $e) {
                    if (DEVELOPER_MODE) {
                        $error_message .= ' ' . $e->errorMessage();
                    }
                    $a_msg = ViewHelper::failureMessage($error_message);
                }
        }
        return $this->o_view->renderList($a_msg);
    }

    /**
     * Updates the user record.
     *
     * @return string
     */
    public function update():string
    {
        $error_message = 'Opps, the person was not updated.';
        $a_person = $this->a_post['person'];
        if (empty($this->a_post['groups'])) {
            $a_msg = ViewHelper::failureMessage($error_message . ' The person must be assigned to at least one group.');
            return $this->o_view->renderList($a_msg);
        }

        $a_person = $this->o_people->setPersonValues($a_person);
        $addendum = '';
        $a_msg    = [];
        switch ($a_person) {
            case 'people_id-missing':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- record id missing.');
                break;
            case 'people_id-invalid':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- record id invalid.');
                break;
            case 'password-missing':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- missing password.');
                break;
            case 'login-exists':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- the new login id already exists.');
                break;
            case 'short_name-exists':
                $a_msg = ViewHelper::failureMessage($error_message . ' -- the new short name already exists.');
                break;
            case 'nothing-to-update':
                $a_msg = ViewHelper::successMessage('No values changed.');
                break;
            case true:
            default:
                // try to save the record.
        }
        if (!empty($a_msg)) {
            return $this->o_view->renderList($a_msg);
        }
        $a_person['groups'] = $this->a_post['groups'];
        try {
            $this->o_complex->savePerson($a_person);
            if ($this->use_cache) {
                $this->o_cache->clearByKeyPrefix('people');
            }
            $message = 'Success, the person was saved.' . $addendum;
            $a_msg = ViewHelper::successMessage($message);
        }
        catch (ModelException $e) {
            if (DEVELOPER_MODE) {
                $error_message .= ' ' . $e->errorMessage();
            }
            $a_msg = ViewHelper::failureMessage($error_message);
        }
        return $this->o_view->renderList($a_msg);
    }

    /**
     * Display the form to verify delete.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        if (isset($this->a_post['people_id'])) {
            $people_id = $this->a_post['people_id'];
        }
        elseif (isset($this->a_post['person']['people_id'])) {
            $people_id = $this->a_post['person']['people_id'];
        }
        else {
            $a_message = ViewHelper::errorMessage('An error has occurred and the person was not deleted. Please try again.');
            return $this->o_view->renderList($a_message);
        }
        $cache_key = 'people.by.id.' . $people_id;
        $cache_value = $this->o_cache->get($cache_key);
        $a_person = json_decode($cache_value, true);
        if (empty($a_person)) {
            try {
                $a_person = $this->o_people->readById($people_id);
                if ($this->use_cache) {
                    $cache_value = Strings::arrayToJsonString($a_person);
                    $this->o_cache->set($cache_key,  $cache_value);
                }
            }
            catch (ModelException) {
                $a_message = ViewHelper::errorMessage('An error occurred and the person was not deleted.');
                return $this->o_view->renderList($a_message);
            }
        }
        if (empty($a_person['is_immutable']) || $a_person['is_immutable'] === 'true') {
            $a_message = ViewHelper::errorMessage('The person is immutable and cannot be deleted.');
            return $this->o_view->renderList($a_message);
        }
        $real_name = !empty($a_person['real_name'])
            ? $a_person['real_name']
            : 'Problem Child';
        $login_id = !empty($a_person['login_id'])
            ? $a_person['login_id']
            : 'Problem Child';
        $a_values = [
            'what'          => 'Person',
            'name'          => $real_name,
            'extra_message' => '',
            'form_action'   => $this->a_router_parts['request_uri'],
            'btn_value'     => $login_id,
            'hidden_name'   => 'people_id',
            'hidden_value'  => $people_id
        ];
        $a_options = [
            'tpl'       => 'verify_delete',
            'a_message' => [],
            'fallback'  => 'renderList',
            'location'  => $this->a_router_parts['request_uri']
        ];
        return $this->o_view->renderVerifyDelete($a_values, $a_options);
    }

    /**
     * Deletes the user record.
     *
     * @return string
     */
    public function delete():string
    {
        if ($this->o_people->isImmutable($this->a_post['people_id'])) {
            $a_msg = ViewHelper::warningMessage('Unable to delete immutable people.');
            return $this->o_view->renderList($a_msg);
        }
        try {
            if ($this->o_complex->deletePerson($this->a_post['people_id'])) {
                if ($this->use_cache) {
                    $this->o_cache->clearByKeyPrefix('people');
                }
                $a_msg = ViewHelper::successMessage();
            }
            else {
                $a_msg = ViewHelper::failureMessage($this->o_people->getErrorMessage());
            }
        }
        catch (ModelException $e) {
            $a_msg = ViewHelper::failureMessage($e->errorMessage());
        }
        return $this->o_view->renderList($a_msg);
    }
}
