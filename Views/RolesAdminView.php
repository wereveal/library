<?php
/**
 *  @brief View for the Router Admin page.
 *  @file RolesAdminView.php
 *  @ingroup ritc_library views
 *  @namespace Ritc/Library/Views
 *  @class RolesAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.1.0
 *  @date 2015-10-07 14:34:30
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.1.0   - Immutable code added  - 10/07/2015 wer
 *      v1.0.0   - First working version - 01/28/2015 wer
 *      v1.0.0β1 - Initial version       - 01/20/2015 wer
 *  </pre>
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ManagerViewTraits;

class RolesAdminView
{
    use LogitTraits, ManagerViewTraits;

    private $o_model;

    public function __construct(Di $o_di)
    {
        $o_db          = $o_di->get('db');
        $this->o_model = new RolesModel($o_db);
        $this->setupView($o_di);
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
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
            'hobbit'  => '',
            'menus'   => $this->a_links,
            'adm_lvl' => $this->auth_level
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
        return $this->o_twig->render('@pages/roles_admin.twig', $a_values);
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
        $a_values['menus'] = $this->a_links;
        return $this->o_twig->render('@pages/verify_delete_role.twig', $a_values);
    }

}
