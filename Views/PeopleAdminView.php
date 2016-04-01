<?php
/**
 * @brief     View for the User Admin page.
 * @ingroup   lib_views
 * @file      PeopleAdminView.php
 * @namespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.1
 * @date      2015-12-12 16:20:24
 * @note <b>Change Log</b>
 * - v1.0.1   - Implement LIB_TWIG_PREFIX                       - 12/12/2015 wer
 * - v1.0.0   - Initial non-beta version                        - 11/12/2015 wer
 * - v1.0.0β2 - Changed to use DI/IOC                           - 11/15/2014 wer
 * - v1.0.0β1 - Initial version                                 - 11/13/2014 wer
 */
namespace Ritc\Library\Views;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\PeopleGroupMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * Class PeopleAdminView
 * @class   PeopleAdminView
 * @package Ritc\Library\Views
 */
class PeopleAdminView
{
    use ViewTraits;

    /** @var \Ritc\Library\Models\PeopleModel */
    private $o_people_model;
    /** @var \Ritc\Library\Models\GroupsModel */
    private $o_group_model;
    /** @var \Ritc\Library\Models\PeopleGroupMapModel */
    private $o_pgm_model;

    /**
     * PeopleAdminView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_people_model = new PeopleModel($this->o_db);
        $this->o_group_model  = new GroupsModel($this->o_db);
        $this->o_pgm_model    = new PeopleGroupMapModel($this->o_db);
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_people_model->setElog($this->o_elog);
            $this->o_group_model->setElog($this->o_elog);
            $this->o_pgm_model->setElog($this->o_elog);
        }
    }

    /**
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = array())
    {
        $meth = __METHOD__ . '.';
        $a_page_values = $this->getPageValues();
        $a_values = [
            'a_message' => array(),
            'a_people'  => array(
                [
                    'people_id'  => '',
                    'login_id'   => '',
                    'real_name'  => '',
                    'auth_level' => 0
                ]
            ),
            'tolken'    => $_SESSION['token'],
            'form_ts'   => $_SESSION['idle_timestamp'],
            'hobbit'    => '',
            'menus'     => $this->a_nav,
            'adm_lvl'   => $this->adm_level
        ];
        $a_values = array_merge($a_page_values, $a_values);

        $log_message = 'a_values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_people = $this->o_people_model->read();
        if ($a_people !== false) {
            foreach($a_people as $key => $a_person) {
                $highest_auth_level = $this->o_auth->getHighestAuthLevel($a_person['people_id']);
                $a_people[$key]['auth_level'] = $highest_auth_level;
            }
            $a_values['a_people'] = $a_people;
        }
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = '';
        }
        $tpl = LIB_TWIG_PREFIX . 'pages/people_admin.twig';
        $html = $this->o_twig->render($tpl, $a_values);
        return $html;
    }

    /**
     * @return string
     */
    public function renderNew()
    {
        $meth = __METHOD__ . '.';

        $a_route_values = $this->o_router->getRouteParts();
        $log_message = 'router parts ' . var_export($a_route_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_page_values = $this->getPageValues();
        $log_message = 'page values ' . var_export($a_page_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $a_values = [
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
                    'highest_role' => 0
                ]
            ),
            'action'  => 'save',
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
            'adm_lvl' => $this->adm_level,
            'menus'   => $this->a_nav
        ];
        $a_values = array_merge($a_page_values, $a_values);
        $log_message = 'a_values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_groups = $this->o_group_model->read();
        $log_message = 'A group values: ' . var_export($a_groups, true);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        foreach ($a_groups as $key => $a_group) {
            $a_groups[$key]['checked'] = '';
        }
        $a_values['person']['groups'] = $a_groups;
        $log_message = 'A person values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $tpl = LIB_TWIG_PREFIX . 'pages/person_form.twig';
        return $this->o_twig->render($tpl, $a_values);
    }

    /**
     * @param int $people_id
     * @return string
     */
    public function renderModify($people_id = -1)
    {
        $meth = __METHOD__ . '.';
        if ($people_id == -1) {
            return $this->renderList(['message' => 'A Problem Has Occured. Please Try Again.', 'type' => 'error']);
        }
        $a_page_values = $this->getPageValues();
        $a_values = [
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
                    'highest_role' => 0
                ]
            ),
            'action'  => 'update',
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
            'adm_lvl' => $this->adm_level,
            'menus'   => $this->a_nav
        ];
        $a_values = array_merge($a_page_values, $a_values);

        $log_message = 'a_values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $a_person = $this->o_people_model->readInfo($people_id);
        if ($a_person == array()) {
            return $this->renderList(['message' => 'The person was not found. Please Try Again.', 'type' => 'error']);
        }
        $a_default_groups = $this->o_group_model->read();
        foreach ($a_default_groups as $key => $a_group) {
            $a_default_groups[$key]['checked'] = '';
        }
        $a_person_groups = $a_person['groups'];

        foreach ($a_person_groups as $a_group) {
            $found_location = Arrays::inArrayRecursive($a_group['group_id'], $a_default_groups);
            if ($found_location) {
                $a_found_location = explode('.', $found_location);
                $main_key = $a_found_location[0];
                $a_default_groups[$main_key]['checked'] = ' checked';
            }
        }
        $a_person['groups'] = $a_default_groups;
        $a_person['password'] = '************';
        $this->logIt("Person: " . var_export($a_person, true), LOG_OFF, __METHOD__);
        $a_values['person'] = $a_person;
        $this->logIt('twig values' . var_export($a_values, TRUE), LOG_OFF, $meth . __LINE__);
        $tpl = LIB_TWIG_PREFIX . 'pages/person_form.twig';
        return $this->o_twig->render($tpl, $a_values);
    }

    /**
     * @param array $a_posted_values
     * @return string
     */
    public function renderVerifyDelete(array $a_posted_values = array())
    {
        $meth = __METHOD__ . '.';
        $this->logIt('Posted Values: ' . var_export($a_posted_values, TRUE), LOG_OFF, $meth . __LINE__);
        if ($a_posted_values == array()) {
            return $this->renderList(['message' => 'Sorry, a problem has occured, please try again.', 'type' => 'failure']);
        }
        $a_person = $this->o_people_model->read(['people_id' => $a_posted_values['person']['people_id']]);
        $this->logIt('Person found: ' . var_export($a_person, TRUE), LOG_OFF, $meth . __LINE__);
        if ($a_person[0]['is_immutable'] == 1) {
            return $this->renderList(['message' => 'Sorry, that user can not be deleted.', 'type' => 'failure']);
        }
        $a_page_values = $this->getPageValues();
        $a_values = [
            'what'         => 'Person',
            'name'         => $a_posted_values['person']['real_name'],
            'public_dir'   => PUBLIC_DIR,
            'where'        => 'people',
            'btn_value'    => 'Person',
            'hidden_name'  => 'people_id',
            'hidden_value' => $a_posted_values['person']['people_id'],
            'tolken'       => $a_posted_values['tolken'],
            'form_ts'      => $a_posted_values['form_ts']
        ];
        $a_twig_values = array_merge($a_page_values, $a_values);
        $a_twig_values['menus'] = $this->a_nav;
        $this->logIt('twig values' . var_export($a_twig_values, TRUE), LOG_OFF, $meth . __LINE__);
        $tpl = LIB_TWIG_PREFIX . 'pages/verify_delete.twig';
        return $this->o_twig->render($tpl, $a_twig_values);
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
