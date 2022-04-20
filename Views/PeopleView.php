<?php
/**
 * Class PeopleView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\CustomException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\FormHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\PeopleComplexModel;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;

/**
 * View for the People Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 4.0.0
 * @date    2021-12-01 13:23:28
 * @change_log
 * - 4.0.0   - updated to php 8 standards                                               - 2021-12-01 wer
 * - 3.0.0   - major changes to utilize the ConfigViewTraits                            - 2017-12-02 wer
 *              This should make the view more portable.
 * - 2.1.0   - method name changed elsewhere forced change here.                        - 2017-06-20 wer
 *              ModelException handling added.
 * - 2.0.0   - Name refactoring                                                         - 2017-05-14 wer
 * - 1.3.0   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here     - 2016-04-11 wer
 * - 1.2.0   - Bug fix for implementation of LIB_TWIG_PREFIX                            - 2016-04-10 wer
 * - 1.1.0   - Implement LIB_TWIG_PREFIX                                                - 12/12/2015 wer
 * - 1.0.0   - Initial non-beta version                                                 - 11/12/2015 wer
 * - 1.0.0β2 - Changed to use DI/IOC                                                    - 11/15/2014 wer
 * - 1.0.0β1 - Initial version                                                          - 11/13/2014 wer
 */
class PeopleView
{
    use ConfigViewTraits;

    /** @var PeopleComplexModel */
    private PeopleComplexModel $o_people_complex;
    /** @var PeopleModel */
    private PeopleModel $o_people_model;
    /** @var GroupsModel */
    private GroupsModel $o_group_model;

    /**
     * PeopleView constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_people_model   = new PeopleModel($this->o_db);
        $this->o_group_model    = new GroupsModel($this->o_db);
        try {
            $this->o_people_complex = new PeopleComplexModel($o_di);
        }
        catch (CustomException $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log('A fatal problem has occurred: ' . $e->getMessage());
            header('Location: ' . SITE_URL);
        }
    }

    /**
     * Renders the list of people.
     *
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = []):string
    {
        try {
            $a_people = $this->o_people_model->read();
        }
        catch (ModelException) {
            $a_people = [];
            $message = 'A problem occurred and the list could not be retrieved.';
            if (empty($a_message)) {
                $a_message = ViewHelper::errorMessage($message);
            }
            else {
                $a_message['message'] .= ' ' . $message;
                $a_message['type'] = 'error';
            }
        }
        if (!empty($a_people)) {
            $a_edit_btn = [
                'form_action' => '/manager/config/people/',
                'btn_value'   => 'edit',
                'btn_label'   => 'Edit',
                'btn_size'    => 'btn-xs',
                'hidden_name' => 'people_id'
            ];
            $a_delete_btn = [
                'form_action' => '/manager/config/people/',
                'btn_value'   => 'verify',
                'btn_label'   => 'Delete',
                'btn_size'    => 'btn-xs',
                'btn_color'   => 'btn-outline-danger',
                'hidden_name' => 'people_id'
            ];
            foreach($a_people as $key => $a_person) {
                $highest_auth_level = $this->o_auth->getHighestAuthLevel($a_person['people_id']);
                $a_people[$key]['auth_level'] = $highest_auth_level;
                $a_edit_btn['hidden_value'] = $a_person['people_id'];
                $a_delete_btn['hidden_value'] = $a_person['people_id'];
                $a_people[$key]['edit_btn'] = FormHelper::singleBtnForm($a_edit_btn);
                $a_people[$key]['delete_btn'] = FormHelper::singleBtnForm($a_delete_btn);
            }
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_new_btn = [
            'form_action' => '/manager/config/people/',
            'btn_value'   => 'new',
            'btn_label'   => 'Create New Person',
            'btn_size'    => 'btn-xs'
        ];
        $a_twig_values['new_btn'] = FormHelper::singleBtnForm($a_new_btn);
        $a_twig_values['a_people'] = $a_people;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Renders the new person form.
     *
     * @return string
     */
    public function renderNew():string
    {
        $a_message = [];
        $a_groups = [];
        try {
            $a_groups = $this->o_group_model->read();
            if (empty($a_groups)) {
                $a_message = ViewHelper::errorMessage('A problem occurred. Please try again.');
            }
            else {
                foreach ($a_groups as $key => $a_group) {
                    $a_groups_cbx = [
                        'id'    => 'groups' . $key,
                        'name'  => 'groups[]',
                        'label' => $a_group['group_name'],
                        'value' => $a_group['group_id']
                    ];
                    $a_groups[$key]['group_cbx'] = FormHelper::checkbox($a_groups_cbx);
                }
            }
        }
        catch (ModelException) {
            $a_message = ViewHelper::errorMessage('A problem occurred. Please try again.');
        }
        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_active_cbx = [
            'id'    => 'is_active',
            'name'  => 'person[is_active]',
            'label' => 'Active'
        ];
        $a_immutable_cbx = [
            'id'    => 'is_immutable',
            'name'  => 'person[is_immutable]',
            'label' => 'Immutable'
        ];
        $a_twig_values['person'] = [
            'people_id'      => '',
            'login_id'       => '',
            'real_name'      => '',
            'short_name'     => '',
            'description'    => '',
            'password'       => '',
            'is_active'      => 'false',
            'active_cbx'     => FormHelper::checkbox($a_active_cbx),
            'immutable_cbx'  => FormHelper::checkbox($a_immutable_cbx),
            'is_immutable'   => 'false',
            'is_logged_in'   => 'false',
            'last_logged_in' => '1000-01-01',
            'created_on'     => date('Y-m-d H:i:s'),
            'groups'         => $a_groups,
            'highest_role'   => 0
        ];
        $a_twig_values['action'] = 'create';
        $a_twig_values['tpl'] = 'person_form';
        $log_message = 'A twig values: ' . var_export($a_twig_values, TRUE);
        print $log_message;
        $tpl = $this->createTplString($a_twig_values);
        print 'Template: ' . $tpl . "\n";
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Renders the modify people form.
     *
     * @param int $people_id
     * @return string
     */
    public function renderModify(int $people_id = -1):string
    {
        if ($people_id === -1) {
            $a_message = ViewHelper::errorMessage('A Problem Has Occured. Please Try Again.');
            return $this->renderList($a_message);
        }
        try {
            $a_person = $this->o_people_complex->readInfo($people_id);
            if (empty($a_person)) {
                $a_message = ViewHelper::errorMessage('The person was not found. Please Try Again.');
                return $this->renderList($a_message);
            }
        }
        catch (ModelException) {
            $a_message = ViewHelper::errorMessage('The person was not found. Please Try Again.');
            return $this->renderList($a_message);
        }
        try {
            $a_groups = $this->o_group_model->read([], ['order_by' => 'group_auth_level DESC']);
            if (empty($a_groups)) {
                $a_message = ViewHelper::errorMessage('An error occurred, please try again.');
                return $this->renderList($a_message);
            }
        }
        catch (ModelException) {
            $a_message = ViewHelper::errorMessage('An error occurred, please try again.');
            return $this->renderList($a_message);
        }
        foreach ($a_groups as $key => $a_group) {
            $a_groups_cbx = [
                'id'    => 'groups' . $key,
                'name'  => 'groups[]',
                'label' => $a_group['group_name'],
                'value' => $a_group['group_id']
            ];
            foreach ($a_person['groups'] as $a_person_group) {
                if ($a_person_group['group_id'] === $a_group['group_id']) {
                    $a_groups_cbx['checked'] = ' checked';
                }
            }
            $a_groups[$key]['group_cbx'] = FormHelper::checkbox($a_groups_cbx);
        }
        $a_person['groups'] = $a_groups;
        $a_active_cbx = [
            'id'    => 'is_active',
            'name'  => 'person[is_active]',
            'label' => 'Active'
        ];
        $a_immutable_cbx = [
            'id'    => 'is_immutable',
            'name'  => 'person[is_immutable]',
            'label' => 'Immutable'
        ];
        if ($a_person['is_active'] === 'true') {
            $a_active_cbx['checked'] = ' checked';
        }
        if ($a_person['is_immutable'] === 'true') {
            $a_immutable_cbx['checked'] = ' checked';
        }
        $a_person['active_cbx'] = $a_active_cbx;
        $a_person['immutable_cbx'] = $a_immutable_cbx;
        $a_person['password'] = '************';
        $a_twig_values = $this->createDefaultTwigValues();
        $a_twig_values['person'] = $a_person;
        $a_twig_values['action'] = 'update';
        $a_twig_values['tpl'] = 'person_form';
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
