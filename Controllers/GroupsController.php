<?php
/**
 * Class GroupsController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use JsonException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ConfigControllerInterface;
use Ritc\Library\Models\GroupsComplexModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\GroupsView;

/**
 * Controller for the Groups Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.1.0
 * @date    2021-11-26 14:59:27
 * @change_log
 * - v3.1.0  - refactored due to change in models              - 2021-11-30 wer
 *              GroupsModel was split into GroupsModel and
 *              GroupsModelComplex
 * - v3.0.0   - updated for php8                                - 2021-11-26 wer
 * - v2.1.0   - updated to use ModelException                   - 2018-05-19 wer
 *              Updated to use ConfigControllerTraits
 * - v2.0.0   - name refactoring                                - 2017-05-14 wer
 * - v1.0.0   - First working version                           - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                                 - 01/28/2015 wer
 */
class GroupsController implements ConfigControllerInterface
{
    use LogitTraits;
    use ConfigControllerTraits;

    /** @var GroupsModel $o_model */
    private GroupsModel $o_model;
    /** @var GroupsView  $o_view */
    private GroupsView         $o_view;
    /** @var GroupsComplexModel $o_gc_model */
    private GroupsComplexModel $o_gc_model;

    /**
     * GroupsController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_model        = new GroupsModel($this->o_db);
        $this->o_gc_model     = new GroupsComplexModel($this->o_db);
        $this->o_view         = new GroupsView($o_di);
        $this->a_object_names = ['o_model', 'o_gc_model'];
        $this->setupElog($o_di);
    }

    /**
     * Standard routing method as required by interface.
     *
     * @return string
     */
    public function route():string
    {
        return match ($this->form_action) {
            'save_new' => $this->save(),
            'update'   => $this->update(),
            'verify'   => $this->verifyDelete(),
            'delete'   => $this->delete(),
            default    => $this->o_view->renderList(),
        };
    }

    ### Required by Interface ###
    /**
     * Deletes the record.
     *
     * @return string
     */
    public function delete():string
    {
        $group_id = $this->a_post['group_id'];
        if ($group_id === -1) {
            $a_message = ViewHelper::errorMessage('An Error Has Occured. The group id was not provided.');
            return $this->o_view->renderList($a_message);
        }
        try {
            $this->o_gc_model->deleteWithRelated($group_id);
            if ($this->use_cache) {
                $this->o_cache->clearTag('groups');
            }
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Saves a new record.
     *
     * @return string
     */
    public function save():string
    {
        $meth = __METHOD__ . '.';
        $a_group = $this->a_post['groups'];
        $a_group['group_name'] = Strings::makeCamelCase($a_group['group_name'], false);
        $this->logIt(var_export($a_group, true), LOG_OFF, $meth . __LINE__);
        try {
            $this->o_model->create($a_group);
            if ($this->use_cache) {
                $this->o_cache->clearTag('groups');
            }
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException | JsonException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Updates a record.
     *
     * @return string
     */
    public function update():string
    {
        $meth = __METHOD__ . '.';
        $a_group = $this->a_post['groups'];
        $a_group['group_name'] = Strings::makeCamelCase($a_group['group_name'], false);
        $a_group['group_immutable'] = empty($a_group['group_immutable'])
            ? 'false'
            : 'true';
        $this->logIt('Update vars: ' . var_export($a_group, true), LOG_OFF, $meth . __LINE__);
        try {
            $this->o_model->update($a_group, ['group_name', 'group_auth_level']);
            if ($this->use_cache) {
                $this->o_cache->clearTag('groups');
            }
            $a_message = ViewHelper::successMessage();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage($e->getMessage());
        }
        return $this->o_view->renderList($a_message);
    }

    /**
     * Verifies the deletion of a record.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        return $this->o_view->renderVerify($this->a_post['groups']);
    }
}
