<?php
/**
 * Class ConstantsController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Views\ConstantsView;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class ConstantsController - controller for the Configuration page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.4.0-beta.4
 * @date    2018-04-21 13:26:46
 * @change_log
 * - v1.4.0-beta.4 - Refactoring of Trait reflected here             - 2018-04-21 wer
 * - v1.4.0-beta.3 - Name change of Trait.                           - 2017-06-20 wer
 * - v1.4.0-beta.2 - Refactoring of model reflected here             - 2017-06-19 wer
 * - v1.4.0-beta.1 - Refactoring elsewhere reflected here.           - 2017-06-07 wer
 * - v1.3.0        - added immutable code                            - 10/07/2015 wer
 * - v1.2.1        - code clean up                                   - 09/25/2015 wer
 * - v1.2.0        - No longer extends Base class, uses LogitTraits  - 08/19/2015 wer
 * - v1.1.0        - changed to implement ManagerControllerInterface - 01/16/2015 wer
 *                   This class should only be called from the main
 *                   manager controller which does session validation.
 * - v1.0.2        - changed to use the new Di class                 - 11/17/2014 wer
 * - v1.0.1        - Adjusted to match file name change              - 11/13/2014 wer
 * - v1.0.0        - Initial version                                 - 04/02/2014 wer
 */
class ConstantsController implements ManagerControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var ConstantsModel $o_model */
    private $o_model;
    /** @var ConstantsView $o_view */
    private $o_view;

    /**
     * ConstantsController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->setupElog($o_di);
        $this->o_model = new ConstantsModel($this->o_db);
        $this->o_view  = new ConstantsView($this->o_di);
        $this->o_model->setElog($this->o_elog);
    }

    /**
     * Renders the html based on the route requested.
     * @return string html to be displayed.
     */
    public function route()
    {
        switch ($this->form_action) {
            case 'verify':
                return $this->verifyDelete();
            case 'update':
                return $this->update();
            case 'save_new':
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
     * @return string
     */
    public function save()
    {
        if ($this->a_router_parts['form_action'] != 'save_new') {
            $a_message = ViewHelper::errorMessage('An error occurred. The record could not be saved.');
            return $this->o_view->renderList($a_message);
        }
        $a_constants = $this->a_post['constant'];
        if (!isset($a_constants['const_immutable'])) {
            $a_constants['const_immutable'] = 'false';
        }
        try {
            $results = $this->o_model->create($a_constants);
            if (empty($results)) {
                $a_message = ViewHelper::errorMessage('Create did not return valid new record id(s).');
            }
            else {
                $a_message = ViewHelper::successMessage();
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('A Problem Has Occured. The new constant could not be saved.');
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Updates the constants record and returns the list of constants with a message.
     * @return string
     */
    public function update()
    {
        $a_constants = $this->a_post['constant'];
        if (!isset($a_constants['const_immutable'])) {
            $a_constants['const_immutable'] = 'false';
        }
        try {
            $this->o_model->update($a_constants);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
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
        $a_values = [
            'what'         => 'constant',
            'name'         => $this->a_post['constant']['const_name'],
            'form_action'  => '/manager/config/constants/',
            'btn_value'    => 'Constant',
            'hidden_name'  => 'const_id',
            'hidden_value' => $this->a_post['constant']['const_id'],
        ];
        $a_options = [
            'tpl'      => 'verify_delete',
            'location' => '/manager/config/constants/'
        ];
        return $this->o_view->renderVerifyDelete($a_values, $a_options);
    }

    /**
     * Deletes the constants record and returns the list of constants with a message.
     * @return string
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
        try {
            if ($this->o_model->delete($const_id)) {
                $a_message = ViewHelper::successMessage();
            }
            else {
                $message = 'Unable to delete the record: ' . $this->o_model->getErrorMessage();
                $a_message = ViewHelper::failureMessage($message);
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('The record was not deleted.');
        }
        return $this->o_view->renderList($a_message);
    }
}
