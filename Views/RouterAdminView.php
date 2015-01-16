<?php
/**
 *  @brief View for the Configuration page.
 *  @file RouterAdminView.php
 *  @ingroup blog views
 *  @namespace Ritc/Library/Views
 *  @class RouterAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β2
 *  @date 2014-11-15 15:15:12
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β2 - changed to use DI/IOC - 11/15/2014 wer
 *      v1.0.0β1 - Initial version       - 11/14/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Views;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Models\RouterModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;

class RouterAdminView extends Base
{
    private $o_model;
    private $o_tpl;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $this->o_tpl   = $o_di->get('tpl');
        $o_db          = $o_di->get('db');
        $this->o_model = new RouterModel($o_db);
    }
    /**
     *  Returns the list of routes in html.
     *  @param array $a_message
     *  @return string
     */
    public function renderList(array $a_message = array())
    {
        $a_values = array(
            'public_dir' => '',
            'description' => 'Admin page for the router configuration.',
            'a_message' => array(),
            'a_routes' => array(
                [
                    'route_id',
                    'route_path',
                    'route_class',
                    'route_method',
                    'route_action',
                    'route_args'
                ]
            ),
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => ''
        );
        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $a_values['a_message'] = ViewHelper::messageProperties(
                array(
                    'message'       => 'Changing router values can result in unexpected results. If you are not sure, do not do it.',
                    'type'          => 'warning'
                )
            );
        }
        $a_routes = $this->o_model->read(array(), ['order_by' => 'route_default, route_path']);
        $this->logIt(
            'a_configs: ' . var_export($a_routes, TRUE),
            LOG_OFF,
            __METHOD__ . '.' . __LINE__
        );
        if ($a_routes !== false && count($a_routes) > 0) {
            $a_values['a_routes'] = $a_routes;
        }
        return $this->o_tpl->render('@pages/routes_admin.twig', $a_values);
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
            $a_values['description'] = 'Form to verify the action to delete the route.';
        }
        return $this->o_tpl->render('@pages/verify_delete_route.twig', $a_values);
    }
}
