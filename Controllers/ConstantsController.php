<?php
/**
 * @brief     Controller for the Configuration page.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/ConstantsController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.4.0-beta.3
 * @date      2017-06-20 12:28:03
 * @note <b>Change Log</b>
 * - v1.4.0-beta.3 - Name change of Trait.                           - 2017-06-20 wer
 * - v1.4.0-beta.2 - Refactoring of model reflected here             - 2017-06-19 wer
 * - v1.4.0-beta.1 - Refactoring elsewhere reflected here.           - 2017-06-07 wer
 * - v1.3.2        - bug fix                                         - 2016-04-11 wer
 * - v1.3.1        - bug fix                                         - 2016-03-08 wer
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
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Models\ConstantsModel;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Views\ConstantsView;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class ConstantsController
 * @class ConstantsController
 * @package Ritc\Library\Controllers
 */
class ConstantsController implements ManagerControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var ConstantsModel */
    private $o_model;
    /** @var ConstantsView */
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
            $a_constants['const_immutable'] = 0;
        }
        try {
            $results = $this->o_model->create($a_constants);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelExceptions $e) {
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
            $a_constants['const_immutable'] = 0;
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
        return $this->o_view->renderVerify($this->a_post);
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
            $a_results = $this->o_model->delete($const_id);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::failureMessage('The record was not deleted.');
        }
        return $this->o_view->renderList($a_message);
    }
}
