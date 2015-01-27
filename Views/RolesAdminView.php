<?php
/**
 *  @brief View for the Router Admin page.
 *  @file RolesAdminView.php
 *  @ingroup ritc_library views
 *  @namespace Ritc/Library/Views
 *  @class RolesAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-01-20 05:04:10
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version       - 01/20/2015 wer
 *  </pre>
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Services\Di;

class RolesAdminView extends Base
{
    private $o_model;
    private $o_tpl;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_tpl   = $o_di->get('tpl');
        $o_db          = $o_di->get('db');
        $this->o_model = new RolesModel($o_db);
    }
    /**
     *  Returns the list of routes in html.
     *  @param array $a_message
     *  @return string
     */
    public function renderList(array $a_message = array())
    {
        $a_values = [
            'public_dir' => '',
            'description' => 'Admin page for the roles.',
            'a_message' => array(),
            'a_roles' => array(
                [
                    'role_id',
                    'role_name',
                    'role_description',
                    'role_level'
                ]
            ),
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => ''
        ];
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = '';
        }
        $a_roles = $this->o_model->read(array(), ['order_by' => 'role_level, role_name']);
        if ($a_roles !== false && count($a_roles) > 0) {
            foreach ($a_roles as $key => $a_row) {
                $a_roles[$key]['role_description'] = html_entity_decode($a_row['role_description']);
            }
            $a_values['a_roles'] = $a_roles;
        }
        return $this->o_tpl->render('@pages/roles_admin.twig', $a_values);
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
            $a_values['description'] = 'Form to verify the action to delete the role.';
        }
        return $this->o_tpl->render('@pages/verify_delete_role.twig', $a_values);
    }

}