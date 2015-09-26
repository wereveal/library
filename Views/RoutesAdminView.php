<?php
/**
 *  @brief View for the Router Admin page.
 *  @file RoutesAdminView.php
 *  @ingroup ritc_library views
 *  @namespace Ritc/Library/Views
 *  @class RouterAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-01-28 14:45:02
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1   - change in database structure forced change here  - 09/03/2015 wer
 *      v1.0.0   - first working version                            - 01/28/2015 wer
 *      v1.0.0β2 - changed to use DI/IOC                            - 11/15/2014 wer
 *      v1.0.0β1 - Initial version                                  - 11/14/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Views;

use Ritc\Library\Models\RoutesModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

class RouterAdminView
{
    use LogitTraits;

    private $o_model;
    private $o_twig;

    public function __construct(Di $o_di)
    {
        $this->o_twig  = $o_di->get('twig');
        $o_db          = $o_di->get('db');
        $this->o_model = new RoutesModel($o_db);
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
        $a_values = array(
            'public_dir' => '',
            'description' => 'Admin page for the router configuration.',
            'a_message' => array(),
            'a_routes' => array(
                [
                    'route_id'       => '',
                    'route_path'     => '',
                    'route_class'    => '',
                    'route_method'   => '',
                    'route_action'   => '',
                    'route_can_edit' => 0
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
        $a_routes = $this->o_model->read(array(), ['order_by' => 'route_can_edit DESC, route_path']);
        $this->logIt(
            'a_routes: ' . var_export($a_routes, TRUE),
            LOG_OFF,
            __METHOD__ . '.' . __LINE__
        );
        if ($a_routes !== false && count($a_routes) > 0) {
            $a_values['a_routes'] = $a_routes;
        }
        return $this->o_twig->render('@pages/routes_admin.twig', $a_values);
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
        return $this->o_twig->render('@pages/verify_delete_route.twig', $a_values);
    }
}
