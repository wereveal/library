<?php
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Models\RoutesComplexModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * View for the Router Admin page.
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2017-06-20 16:46:14
 * ## Change Log
 * - v2.1.0   - two changes, switch to using RoutesComplexModel and method in           - 2017-06-20 wer
 *              ViewHelper refactored so updated here.
 * - v2.0.0   - Name refactoring                                                        - 2017-05-14 wer
 * - v1.5.0   - Refactoring elsewhere forced changes here                               - 2016-04-13 wer
 * - v1.4.0   - Refactored the tpls to implement LIB_TWIG_PREFIX pushed changes here    - 2016-04-11 wer
 * - v1.3.0   - Change in implementation of LIB_TWIG_PREFIX                             - 2016-04-10 wer
 * - v1.2.0   - Implement LIB_TWIG_PREFIX                                               - 12/12/2015 wer
 * - v1.1.0   - change in database structure forced change here                         - 09/03/2015 wer
 * - v1.0.0   - first working version                                                   - 01/28/2015 wer
 * - v1.0.0β2 - changed to use DI/IOC                                                   - 11/15/2014 wer
 * - v1.0.0β1 - Initial version                                                         - 11/14/2014 wer
 */
class RoutesView
{
    use LogitTraits, ViewTraits;

    /** @var \Ritc\Library\Models\RoutesComplexModel */
    private $o_model;

    /**
     * RoutesView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
        $this->o_model = new RoutesComplexModel($o_di);
    }

    /**
     * Returns the list of routes in html.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = array())
    {
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
                    'route_immutable' => 'true'
                ]
            ),
            'tolken'  => $_SESSION['token'],
            'form_ts' => $_SESSION['idle_timestamp'],
            'hobbit'  => '',
            'a_menus' => $this->retrieveNav('ManagerLinks'),
            'adm_lvl' => $this->adm_level,
            'page_prefix' => LIB_TWIG_PREFIX
        );
        $a_values = array_merge($a_page_values, $a_values);

        if (count($a_message) != 0) {
            $a_values['a_message'] = ViewHelper::fullMessage($a_message);
        }
        else {
            $message = 'Changing router values can result in unexpected results. If you are not sure, do not do it.';
            $a_values['a_message'] = ViewHelper::fullMessage(
                ViewHelper::warningMessage($message)
            );
        }
        $error_message = '';
        try {
            $a_routes = $this->o_model->readAllWithUrl();
        }
        catch (ModelException $e) {
            $a_routes = false;
            $error_message .= $e->errorMessage();
        }
        $o_urls = new UrlsModel($this->o_db);
        try {
            $a_urls = $o_urls->read();
        }
        catch (ModelException $e) {
            $a_urls = false;
            $error_message .= $e->errorMessage();
        }
        if ($a_urls === false || $a_routes === false) {
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
                $a_routes[$key]['page_prefix'] = LIB_TWIG_PREFIX;
            }

        }
        $a_values['a_routes'] = $a_routes;
        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/routes.twig';
        return $this->renderIt($tpl, $a_values);
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
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

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
            'page_prefix'  => LIB_TWIG_PREFIX
        ];
        $a_twig_values = array_merge($a_twig_values, $a_more_values);
        $log_message = 'Twig Values: ' . var_export($a_twig_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $tpl = '@' . LIB_TWIG_PREFIX . 'pages/verify_delete.twig';
        return $this->renderIt($tpl, $a_twig_values);
    }
}
