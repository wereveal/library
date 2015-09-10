<?php
/**
 *  @brief View for the Groups Admin page.
 *  @file GroupsAdminView.php
 *  @ingroup ritc_library views
 *  @namespace Ritc/Library/Views
 *  @class GroupsAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-01-28 14:56:11
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version       - 01/28/2015 wer
 *  </pre>
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\GroupRoleMapModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

class GroupsAdminView
{
    use LogitTraits;

    private $o_db;
    private $o_groups;
    private $o_twig;

    public function __construct(Di $o_di)
    {
        $this->o_twig  = $o_di->get('twig');
        $this->o_db    = $o_di->get('db');
        $this->o_groups = new GroupsModel($this->o_db);
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_groups->setElog($this->o_elog);
        }
    }
    /**
     *  Returns the list of routes in html.
     *  @param array $a_message
     *  @return string
     */
    public function renderList(array $a_message = array())
    {
        $meth    = __METHOD__ . '.';
        $o_grm   = new GroupRoleMapModel($this->o_db);
        $o_roles = new RolesModel($this->o_db);
        $a_roles = $o_roles->read();
        foreach ($a_roles as $key => $a_role) {
            $a_roles[$key]['checked'] = '';
        }
        $this->logIt("Roles: " . var_export($a_roles, true), LOG_OFF, $meth . __LINE__);
        $a_values = [
            'public_dir'  => '',
            'description' => 'Admin page for the groups.',
            'a_message'   => array(),
            'a_groups'    => array(
                [
                    'group_id'          => '',
                    'group_name'        => '',
                    'group_description' => '',
                    'a_roles'           => array()
                ]
            ),
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
            'a_blank_roles' => $a_roles,
        ];
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = '';
        }
        $a_groups = $this->o_groups->read(array(), ['order_by' => 'group_name']);
        if ($a_groups !== false && count($a_groups) > 0) {
            $this->logIt("Groups: " . var_export($a_groups, true), LOG_OFF, $meth . __LINE__);
            foreach ($a_groups as $a_group_key => $a_row) {
                $a_groups[$a_group_key]['group_description'] = html_entity_decode($a_row['group_description'], ENT_QUOTES);
                $a_grm = $o_grm->read(['group_id' => $a_row['group_id']]);
                $a_temp_roles = $a_roles;
                foreach($a_grm as $a_grm_row) {
                    $this_row_role_id = $a_grm_row['role_id'];
                    $role_key = Arrays::inArrayRecursive($this_row_role_id, $a_temp_roles);
                    if ($role_key) {
                        $a_temp_roles[$role_key]['checked'] = ' checked';
                    }
                }
                $a_groups[$a_group_key]['a_roles'] = $a_temp_roles;
            }
            $a_values['a_groups'] = $a_groups;
        }
        $this->logIt(var_export($a_values, true), LOG_OFF, $meth . __LINE__);
        return $this->o_twig->render('@pages/groups_admin.twig', $a_values);
    }
    /**
     *  Returns HTML verify form to delete.
     *  @param array $a_values
     *  @return string
     */
    public function renderVerify(array $a_values = array())
    {
        if ($a_values === array()) {
            return $this->renderList(['message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure']);
        }
        if (!isset($a_values['public_dir'])) {
            $a_values['public_dir'] = '';
        }
        if (!isset($a_values['description'])) {
            $a_values['description'] = 'Form to verify the action to delete the group.';
        }
        return $this->o_twig->render('@pages/verify_delete_group.twig', $a_values);
    }

}
