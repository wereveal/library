<?php
/**
 *  @brief View for the User Admin page.
 *  @file PeopleAdminView.php
 *  @ingroup blog views
 *  @namespace Ritc/Library/Views
 *  @class PeopleAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1ß
 *  @date 2014-11-15 15:20:52
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1ß - Changed to use DI/IOC                           - 11/15/2014 wer
 *      v1.0.0ß - Initial version                                 - 11/13/2014 wer
 *  </pre>
 *  @TODO base access to crud people on role access levels
 *  @TODO write the code to display users to allow for CRUD actions
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\PeopleGroupMapModel;
use Ritc\Library\Models\GroupRoleMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

class PeopleAdminView
{
    use LogitTraits;

    private $o_auth;
    private $o_people_model;
    private $o_group_model;
    private $o_role_model;
    private $o_pgm_model;
    private $o_grm_model;
    private $o_twig;

    public function __construct(Di $o_di)
    {
        $o_db                 = $o_di->get('db');
        $this->o_people_model = new PeopleModel($o_db);
        $this->o_group_model  = new GroupsModel($o_db);
        $this->o_role_model   = new RolesModel($o_db);
        $this->o_pgm_model    = new PeopleGroupMapModel($o_db);
        $this->o_grm_model    = new GroupRoleMapModel($o_db);
        $this->o_auth         = new AuthHelper($o_di);
        $this->o_twig         = $o_di->get('twig');
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_people_model->setElog($this->o_elog);
            $this->o_group_model->setElog($this->o_elog);
            $this->o_role_model->setElog($this->o_elog);
            $this->o_pgm_model->setElog($this->o_elog);
            $this->o_grm_model->setElog($this->o_elog);
        }
    }

    public function renderList(array $a_message = array())
    {
        $a_values = [
            'public_dir'  => PUBLIC_DIR,
            'description' => 'Admin page for people.',
            'a_message'   => array(),
            'a_people'    => array(
                [
                    'people_id' => '',
                    'login_id'  => '',
                    'real_name' => ''
                ]
            ),
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
        ];
        if ($_SESSION['login_id'] != 'SuperAdmin') {
            $a_values['a_people'] = $this->o_people_model->read(['is_immutable' => 0]);
        }
        else {
            $a_values['a_people'] = $this->o_people_model->read();
        }
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = '';
        }
        $html = $this->o_twig->render('@pages/people_admin.twig', $a_values);
        return $html;
    }
    public function renderNew()
    {
        return '';
    }
    public function renderModify($people_id = -1)
    {
        if ($people_id == -1) {
            return $this->renderList(['message' => 'A Problem Has Occured. Please Try Again.', 'type' => 'error']);
        }
        $admin_id = $this->o_people_model->getPeopleId($_SESSION['login_id']);
        if ($admin_id === false) {
            $this->o_auth->logout($_SESSION['login_id']);
            header("Location: " . SITE_URL . "/manager/login/");
        }
        $a_values = [
            'public_dir'  => PUBLIC_DIR,
            'description' => 'Modify a Person.',
            'a_message'   => array(),
            'person'      => array(
                [
                    'people_id'    => '',
                    'login_id'     => '',
                    'real_name'    => '',
                    'short_name'   => '',
                    'description'  => '',
                    'password'     => '',
                    'is_active'    => 0,
                    'is_immutable' => 0,
                    'created_on'   => date('Y-m-d H:i:s'),
                    'groups'       => [],
                    'roles'        => []
                ]
            ),
            'action'  => 'update',
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
            'adm'     => $_SESSION['login_id']
        ];
        $a_person = $this->o_people_model->readInfo($people_id);
        if ($a_person == array()) {
            return $this->renderList(['message' => 'The person was not found. Please Try Again.', 'type' => 'error']);
        }
        $a_person['password'] = '************';
        $this->logIt("Person: " . var_export($a_person, true), LOG_ON, __METHOD__);
        foreach($a_person['groups'] as $key => $a_group) {
            $can_change = 'no';
            if ($this->o_auth->hasMinimumRoleLevel($admin_id, $a_group['group_id'])) {
                $can_change = 'yes';
            }
            $a_person['groups'][$key]['can_change'] = $can_change;
        }
        $this->logIt("Person: " . var_export($a_person, true), LOG_ON, __METHOD__);
        $a_values['person'] = $a_person;
        return $this->o_twig->render('@pages/person_form.twig', $a_values);
    }
    public function renderVerifyDelete(array $a_posted_values = array())
    {
        if ($a_posted_values == array()) {
            return $this->renderList(['message' => 'Sorry, a problem has occured, please try again.', 'type' => 'failure']);
        }
        $a_person = $this->o_people_model->read(['people_id' => $a_posted_values['people_id']]);
        if ($a_person[0]['is_immutable'] == 1) {
            return $this->renderList(['message' => 'Sorry, that user can not be deleted.', 'type' => 'failure']);
        }
        return $this->o_twig->render('@pages/verify_delete_person.twig', $a_posted_values);
    }
    /**
     * Something to keep phpStorm from complaining until I use the ViewHelper.
     * @return array
     */
    public function temp()
    {
        $a_stuph = ViewHelper::messageProperties(['stuff'=>'stuff']);
        return $a_stuph;
    }
}
