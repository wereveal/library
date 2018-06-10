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
use Ritc\Library\Models\PeopleGroupMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View for the People Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.0.0
 * @date    2017-12-02 09:15:58
 * @change_log
 * - v3.0.0   - major changes to utilize the ConfigViewTraits                           - 2017-12-02 wer
 *              This should make the view more portable.
 * - v2.1.0   - method name changed elsewhere forced change here.                       - 2017-06-20 wer
 *              ModelException handling added.
 * - v2.0.0   - Name refactoring                                                        - 2017-05-14 wer
 * - v1.3.0   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - v1.2.0   - Bug fix for implementation of LIB_TWIG_PREFIX                           - 2016-04-10 wer
 * - v1.1.0   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - v1.0.0   - Initial non-beta version                                                - 11/12/2015 wer
 * - v1.0.0β2 - Changed to use DI/IOC                                                   - 11/15/2014 wer
 * - v1.0.0β1 - Initial version                                                         - 11/13/2014 wer
 */
class PeopleView
{
    use ConfigViewTraits, LogitTraits;

    /** @var PeopleComplexModel */
    private $o_people_complex;
    /** @var \Ritc\Library\Models\PeopleModel */
    private $o_people_model;
    /** @var \Ritc\Library\Models\GroupsModel */
    private $o_group_model;
    /** @var \Ritc\Library\Models\PeopleGroupMapModel */
    private $o_pgm_model;

    /**
     * PeopleView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->setupElog($o_di);
        $this->o_people_model   = new PeopleModel($this->o_db);
        $this->o_group_model    = new GroupsModel($this->o_db);
        $this->o_pgm_model      = new PeopleGroupMapModel($this->o_db);
        try {
            $this->o_people_complex = new PeopleComplexModel($o_di);
        }
        catch (CustomException $e) {
            error_log('A fatal problem has occurred: ' . $e->getMessage());
            header("Location: " . SITE_URL);
        }
        if (DEVELOPER_MODE) {
            $this->o_people_model->setElog($this->o_elog);
            $this->o_group_model->setElog($this->o_elog);
            $this->o_pgm_model->setElog($this->o_elog);
            $this->o_people_complex->setElog($this->o_elog);
        }
    }

    /**
     * Renders the list of people.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = [])
    {
        try {
            $a_people = $this->o_people_model->read();
        }
        catch (ModelException $e) {
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
     * @return string
     */
    public function renderNew()
    {
        $meth = __METHOD__ . '.';
        $a_message = [];
        $a_groups = [];
        try {
            $a_groups = $this->o_group_model->read();
            if (empty($a_groups)) {
                $a_message = ViewHelper::errorMessage('A problem occurred. Please try again.');
            }
            else {
                $log_message = 'A group values: ' . var_export($a_groups, true);
                $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
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
        catch (ModelException $e) {
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
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        print $log_message;
        $tpl = $this->createTplString($a_twig_values);
        print 'Template: ' . $tpl . "\n";
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Renders the modify people form.
     * @param int $people_id
     * @return string
     */
    public function renderModify($people_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($people_id == -1) {
            $a_message = ViewHelper::errorMessage('A Problem Has Occured. Please Try Again.');
            return $this->renderList($a_message);
        }
        try {
            $a_person = $this->o_people_complex->readInfo($people_id);
              $log_message = 'person ' . var_export($a_person, TRUE);
              $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
            if (empty($a_person)) {
                $a_message = ViewHelper::errorMessage('The person was not found. Please Try Again.');
                return $this->renderList($a_message);
            }
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage('The person was not found. Please Try Again.');
            $this->logIt("Exception Error: " . $e->errorMessage(), LOG_OFF, $meth . __LINE__);
            return $this->renderList($a_message);
        }
        try {
            $a_groups = $this->o_group_model->read([], ['order_by' => 'group_auth_level DESC']);
              $log_message = 'Groups:  ' . var_export($a_groups, TRUE);
              $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
            if (empty($a_groups)) {
                $a_message = ViewHelper::errorMessage('An error occurred, please try again.');
                return $this->renderList($a_message);
            }
        }
        catch (ModelException $e) {
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
                if ($a_person_group['group_id'] === $a_groups[$key]['group_id']) {
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
        if ($a_person['is_active'] == 'true') {
            $a_active_cbx['checked'] = ' checked';
        }
        if ($a_person['is_immutable'] == 'true') {
            $a_immutable_cbx['checked'] = ' checked';
        }
        $a_person['active_cbx'] = $a_active_cbx;
        $a_person['immutable_cbx'] = $a_immutable_cbx;
        $a_person['password'] = '************';
          $this->logIt("Person: " . var_export($a_person, true), LOG_OFF, $meth . __LINE__);
        $a_twig_values = $this->createDefaultTwigValues();
        $a_twig_values['person'] = $a_person;
        $a_twig_values['action'] = 'update';
        $a_twig_values['tpl'] = 'person_form';
          $this->logIt('twig values' . var_export($a_twig_values, TRUE), LOG_OFF, $meth . __LINE__);
        $tpl = $this->createTplString($a_twig_values);
        $this->logIt('tpl: ' . $tpl, LOG_OFF, $meth . __LINE__);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
