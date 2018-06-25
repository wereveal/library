<?php
/**
 * Class RoutesView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\FormHelper;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RoutesComplexModel;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\RoutesGroupMapModel;
use Ritc\Library\Models\UrlsModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * View for the Router Admin page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.2.0
 * @date    2018-05-16 17:55:29
 * @change_log
 * - v2.2.0   - Switching code to use more of the ViewTraits (bug fixes)                - 2018-05-16 wer
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
    use LogitTraits, ConfigViewTraits;

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
        $this->o_model->setupElog($o_di);
    }

    /**
     * Returns the list of routes in html.
     * @param array $a_message
     * @return string
     */
    public function renderList(array $a_message = array())
    {
        $meth = __METHOD__ . '.';
        $message = 'Changing router values can result in unexpected results. If you are not sure, do not do it.';
        $o_urls = new UrlsModel($this->o_db);
        $o_rgm = new RoutesGroupMapModel($this->o_db);
        $o_groups = new GroupsModel($this->o_db);
        $o_urls->setupElog($this->o_di);
        $o_rgm->setupElog($this->o_di);
        $o_groups->setupElog($this->o_di);
        if (empty($a_message)) {
            $a_message = ViewHelper::warningMessage($message);
        }
        else {
            $a_message = ViewHelper::addMessage($a_message, $message);
        }
        $error_message = '';
        try {
            $a_routes = $this->o_model->readAllWithUrl();
        }
        catch (ModelException $e) {
            $a_routes = false;
            $error_message .= $e->errorMessage();
        }
        try {
            $a_available_urls = $o_urls->readNoRoute();
        }
        catch (ModelException $e) {
            $a_available_urls = [];
            $error_message .= $e->getMessage();
        }
        $a_available_groups = [];
        try {
            $a_available_groups = $o_groups->read();
        }
        catch (ModelException $e) {
            $error_message .= ' ' . $e->getMessage();
        }
        $a_url_options = [
            [
                'value' => '',
                'label' => '--Select URL--',
                'other_stuph' => ''
            ]
        ];
        foreach ($a_available_urls as $a_url) {
            $a_url_options[] = [
                'value' => $a_url['url_id'],
                'label' => $a_url['url_text'],
                'other_stuph' => ''
            ];
        }
        $a_urls_select = [
            'label_class' => 'd-md-none',
            'label_text'  => 'URL',
            'name'        => 'route[url_id]',
            'id'          => 'route0[url_id]',
            'class'       => 'form-control colorful',
            'other_stuph' => '',
            'options'     => $a_url_options
        ];
        $a_urls_select_bottom = $a_urls_select;
        $a_urls_select_bottom['id'] = 'route999[url_id]';

        $a_groups = [];
        $a_groups_bottom = [];
        foreach ($a_available_groups as $key => $a_group) {
            $id_bottom = 'group00[' . $a_group['group_id'] .']';
            $a_g = FormHelper::checkbox([
                'id'    => 'group0[' . $a_group['group_id'] .']',
                'name'  => 'group[' . $a_group['group_id'] .']',
                'label' => $a_group['group_name']
            ]);
            $a_groups[] = $a_g;
            $a_g['id'] = $id_bottom;
            $a_groups_bottom[] = $a_g;
        }
        $x = 1;
        foreach ($a_routes as $key => $a_route)  {
            $this_route_options = $a_url_options;
            $this_route_options[] = [
                'value' => $a_route['url_id'],
                'label' => $a_route['url_text'],
                'other_stuph' => ' selected'
            ];
            $a_select = [
                'label_class' => 'd-md-none',
                'label_text'  => 'URL',
                'name'        => 'route[url_id]',
                'id'          => 'route' . $x . '[url_id]',
                'class'       => 'form-control colorful',
                'other_stuph' => '',
                'options'     => $this_route_options
            ];
            $a_routes[$key]['a_urls'] = $a_select;
            $a_rgm_results = [];
            try {
                $a_rgm_results = $o_rgm->readByRouteId($a_route['route_id']);
            }
            catch (ModelException $e) {
                $error_message .= $e->getMessage();
            }
            $a_routes[$key]['groups'] = $a_groups;
            foreach ($a_routes[$key]['groups'] as $g_key => $a_group) {
                $a_routes[$key]['groups'][$g_key]['id'] = 'group' . $x . $a_group['id'];
                $replace_this = '/group\[(.*)\]/i';
                $with_this = '$1';
                $group_id = preg_replace($replace_this, $with_this, $a_group['name']);
                foreach ($a_rgm_results as $r_key => $a_rgm) {
                    if ($group_id == $a_rgm['group_id']) {
                        $a_routes[$key]['groups'][$g_key]['checked'] = ' checked';
                    }
                }
            }
            $x++;
        }
        if (!empty($error_message)) {
            $a_message = ViewHelper::addMessage($a_message, $error_message, 'error');
        }

        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_routes'] = $a_routes;
        $a_twig_values['groups'] = $a_groups;
        $a_twig_values['groups_bottom'] = $a_groups_bottom;
        $a_twig_values['a_urls_select'] = $a_urls_select;
        $a_twig_values['a_urls_select_bottom'] = $a_urls_select_bottom;
          $log_message = 'a_twig_values ' . var_export($a_twig_values, TRUE);
          $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $tpl = $this->createTplString($a_twig_values);
          $this->logIt("tpl: " . $tpl, LOG_OFF, $meth . __LINE__);
        return $this->renderIt($tpl, $a_twig_values);
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
        $a_twig_values = $this->createDefaultTwigValues();
        $a_more_values = [
            'what'         => 'Route',
            'name'         => $a_values['route']['route_id'] . ' for ' . $a_values['route']['route_class'],
            'where'        => 'routes',
            'btn_value'    => 'Route',
            'hidden_name'  => 'route_id',
            'hidden_value' => $a_values['route']['route_id']
        ];
        $a_twig_values = array_merge($a_twig_values, $a_more_values);
        $log_message = 'Twig Values: ' . var_export($a_twig_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
