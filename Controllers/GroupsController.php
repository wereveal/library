<?php
/**
 * Class GroupsController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ConfigControllerInterface;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\GroupsView;

/**
 * Controller for the Groups Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2018-05-19 15:07:53
 * @change_log
 * - v2.1.0   - updated to use ModelException           - 2018-05-19 wer
 *              Updated to use ConfigControllerTraits
 * - v2.0.0   - name refactoring                        - 2017-05-14 wer
 * - v1.0.0   - First working version                   - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                         - 01/28/2015 wer
 */
class GroupsController implements ConfigControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var GroupsModel */
    private $o_model;
    /** @var GroupsView */
    private $o_view;

    /**
     * GroupsController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_model = new GroupsModel($this->o_db);
        $this->o_view = new GroupsView($o_di);
        $this->a_object_names = ['o_model'];
        $this->setupElog($o_di);
    }

    /**
     * Standard routing method as required by interface.
     * @return string
     */
    public function route()
    {
        switch ($this->form_action) {
            case 'save_new':
                return $this->save();
            case 'update':
                return $this->update();
            case 'verify':
                return $this->verifyDelete();
            case 'delete':
                return $this->delete();
            default:
                return $this->o_view->renderList();
        }
    }

    ### Required by Interface ###
    /**
     * Deletes the record.
     * @return string
     */
    public function delete()
    {
        $group_id = $this->a_post['group_id'];
        if ($group_id == -1) {
            $a_message = ViewHelper::errorMessage('An Error Has Occured. The group id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        try {
            $this->o_model->deleteWithRelated($group_id);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Saves a new record.
     * @return string
     */
    public function save()
    {
        $meth = __METHOD__ . '.';
        $a_group = $this->a_post['groups'];
        $a_group['group_name'] = Strings::makeCamelCase($a_group['group_name'], false);
        $this->logIt(var_export($a_group, true), LOG_OFF, $meth . __LINE__);
        try {
            $this->o_model->create($a_group);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Updates a record.
     * @return string
     */
    public function update()
    {
        $meth = __METHOD__ . '.';
        $a_group = $this->a_post['groups'];
        $a_group['group_name'] = Strings::makeCamelCase($a_group['group_name'], false);
        $a_group['group_immutable'] = empty($a_group['group_immutable'])
            ? 'false'
            : 'true';
        $this->logIt("Update vars: " . var_export($a_group, true), LOG_ON, $meth . __LINE__);
        try {
            $this->o_model->update($a_group, 'group_immutable', ['group_name, group_auth_level']);
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Verifies the deletion of a record.
     * @return string
     */
    public function verifyDelete()
    {
        return $this->o_view->renderVerify($this->a_post['groups']);
    }
}
