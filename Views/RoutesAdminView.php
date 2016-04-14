<?php
/**
 * @brief     View for the Router Admin page.
 * @ingroup   lib_views
 * @file      RoutesAdminView.php
 * @namespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.1.0
 * @date      2016-04-13 11:38:50
 * @note <b>Change Log</b>
 * - v1.1.0   - Refactoring elsewhere forced changes here                               - 2016-04-13 wer
 * - v1.0.4   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - v1.0.3   - Change in implementation of LIB_TWIG_PREFIX                             - 2016-04-10 wer
 * - v1.0.2   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - v1.0.1   - change in database structure forced change here                         - 09/03/2015 wer
 * - v1.0.0   - first working version                                                   - 01/28/2015 wer
 * - v1.0.0β2 - changed to use DI/IOC                                                   - 11/15/2014 wer
 * - v1.0.0β1 - Initial version                                                         - 11/14/2014 wer
 */
namespace Ritc\Library\Views;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Models\RoutesModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ViewTraits;

/**
 * Class RoutesAdminView
 * @class   RoutesAdminView
 * @package Ritc\Library\Views
 */
class RoutesAdminView
{
    use ViewTraits;

    /** @var \Ritc\Library\Models\RoutesModel */
    private $o_model;

    /**
     * RoutesAdminView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $this->o_model = new RoutesModel($this->o_db);
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }

    }

    /**
     * Returns the list of routes in html.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = array())
    {
        $meth = __METHOD__ . '.';
        $a_page_values = $this->getPageValues();
        $a_values = array(
            'a_message' => array(),
            'a_routes' => array(
                [
                    'route_id'        => '',
                    'route_path'      => '',
                    'route_class'     => '',
                    'route_method'    => '',
                    'route_action'    => '',
                    'route_immutable' => 1
                ]
            ),
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
            'a_menus' => $this->retrieveNav('ManagerLinks'),
            'adm_lvl' => $this->adm_level,
            'twig_prefix' => LIB_TWIG_PREFIX
        );
        $a_values = array_merge($a_page_values, $a_values);
        $log_message = 'a_values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::messageProperties($a_message);
        }
        else {
            $message = 'Changing router values can result in unexpected results. If you are not sure, do not do it.';
            $a_values['a_message'] = ViewHelper::messageProperties(
                ViewHelper::warningMessage($message)
            );
        }

        $a_routes = $this->o_model->readAllWithUrl();
        $log_message = 'a_routes: ' . var_export($a_routes, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $o_urls = new UrlsModel($this->o_db);
        $a_urls = $o_urls->read();
        $log_message = 'URLs:  ' . var_export($a_urls, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        if ($a_urls === false || $a_routes === false) {
            $error_message = 'A problem has occured. Please try reloading the page.';
            $a_values['a_message'] = ViewHelper::failureMessage($error_message);
        }
        else {
            $a_options = [];
            $a_options[] = [
                'value'       => 0,
                'label'       => '--Select URL--',
                'other_stuph' => ' selected'
            ];
            foreach ($a_urls as $a_url) {
                // if url_id is not assigned to a route and not part of the library make it an option
                $results = Arrays::inAssocArrayRecursive('url_id', $a_url['url_id'], $a_routes);
                if (!$results) {
                    if (strpos($a_url['url_text'], 'library') === false) {
                        $a_options[] = [
                            'value' => $a_url['url_id'],
                            'label' => $a_url['url_text']
                        ];
                    }
                }
            }
            $a_values['select'] = [
                'name'    => 'route[url_id]',
                'class'   => 'form-control w200',
                'options' => $a_options
            ];
            foreach ($a_routes as $key => $a_route) {
                $a_option = [];
                foreach ($a_urls as $a_url) {
                    if ($a_route['url_id'] == $a_url['url_id']) { // there should only be one
                        $a_option[] = [
                            'value'       => $a_url['url_id'],
                            'label'       => $a_url['url_text'],
                            'other_stuph' => ' selected'
                        ];
                    }
                }
                $a_option = array_merge($a_options, $a_option);
                foreach ($a_option as $the_key => $a_opt) {
                    if ($a_opt['value'] == '0') {
                        unset($a_option[$the_key]);
                    }
                }
                $a_routes[$key]['select'] = [
                    'name'    => 'route[url_id]',
                    'class'   => 'form-control w200',
                    'options' => $a_option
                ];
                $a_routes[$key]['twig_prefix'] = LIB_TWIG_PREFIX;
            }

        }
        $a_values['a_routes'] = $a_routes;
        $log_message = 'Twig Values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/routes_admin.twig';
        return $this->o_twig->render($tpl, $a_values);
    }

    /**
     * Returns HTML verify form to delete.
     * @param array $a_values
     * @return string
     */
    public function renderVerify(array $a_values = array())
    {
        $meth = __METHOD__ . '.';
        $log_message = 'Posted Values: ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);

        if ($a_values === array()) {
            return $this->renderList(['message' => 'An Error Has Occurred. Please Try Again.', 'type' => 'failure']);
        }
        $a_twig_values = $this->getPageValues();
        $a_more_values = [
            'a_menus'      => $this->retrieveNav('ManagerGroup'),
            'what'         => 'Route',
            'name'         => $a_values['route']['route_id'] . ' for ' . $a_values['route']['route_class'],
            'where'        => 'routes',
            'btn_value'    => 'Route',
            'hidden_name'  => 'route_id',
            'hidden_value' => $a_values['route']['route_id'],
            'tolken'       => $a_values['tolken'],
            'form_ts'      => $a_values['form_ts'],
            'twig_prefix'  => LIB_TWIG_PREFIX
        ];
        $a_twig_values = array_merge($a_twig_values, $a_more_values);
        $log_message = 'Twig Values: ' . var_export($a_twig_values, TRUE);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);

        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/verify_delete.twig';
        return $this->o_twig->render($tpl, $a_twig_values);
    }
}
