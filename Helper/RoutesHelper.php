<?php
/**
 *  @brief     Various helper functions for routes.
 *  @ingroup   ritc_library helper
 *  @file      RoutesHelper.php
 *  @namespace Ritc\Library\Helper
 *  @class     RoutesHelper
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0
 *  @date      2015-11-27 14:55:02
 *  @note Change Log
 *      v1.0.0   - took out of beta              - 11/27/2015 wer
 *      v1.0.0β3 - bug fix                       - 11/24/2015 wer
 *      v1.0.0β2 - logic change                  - 10/30/2015 wer
 *      v1.0.0β1 - intial file                   - 09/26/2015 wer
 **/
namespace Ritc\Library\Helper;

use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RoutesGroupMapModel;
use Ritc\Library\Models\RoutesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

class RoutesHelper
{
    use LogitTraits;

    private $a_route_parts;
    private $o_group;
    private $o_model;
    private $o_rgm;
    private $route_path;
    private $request_uri;

    public function __construct(Di $o_di, $route_path = '')
    {
        $o_db = $o_di->get('db');
        $this->o_model = new RoutesModel($o_db);
        $this->o_group = new GroupsModel($o_db);
        $this->o_rgm   = new RoutesGroupMapModel($o_db);
        $this->route_path = $route_path;
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
            $this->o_group->setElog($this->o_elog);
        }
    }

    public function setRouteParts($route_path = '')
    {
        if ($route_path == '') {
            if ($this->route_path != '') {
                $route_path = $this->route_path;
            }
            else {
                $route_path = $_SERVER["REQUEST_URI"];
            }
        }
        $a_values = ['route_path' => $route_path];
        $a_results = $this->o_model->read($a_values);
        $this->logIt("Actions from DB: " . var_export($a_results, true), LOG_OFF, __METHOD__);
        if ($a_results !== false && count($a_results) === 1) {
            $a_route_parts                   = $a_results[0];
            $this->route_path                = $a_route_parts['route_path'];
            $a_route_parts['request_uri']    = $route_path;
            $a_route_parts['url_actions']    = [];
            $a_route_parts['groups']         = $this->getGroups($a_route_parts['route_id']);
            $a_route_parts['min_auth_level'] = $this->getMinAuthLevel($a_route_parts['groups']);
            $this->a_route_parts             = $a_route_parts;
        }
        else {
            $a_route_path_parts = explode('/', trim($route_path));
            $a_urls = ['/'];
            $i = 0;
            foreach ($a_route_path_parts as $key => $part) {
                if ($part != '') {
                    $a_urls[$i + 1] = $a_urls[$i++] . $part . '/';
                }
            }
            $a_last_good_results = array();
            $last_url = '';
            foreach ($a_urls as $key => $url) {
                $a_values = ['route_path' => $url];
                $a_results = $this->o_model->read($a_values);
                if ($a_results !== false && isset($a_results[0])) {
                    $a_last_good_results = $a_results[0];
                    $last_url = $url;
                }
            }
            if ($a_last_good_results != array()) {
                $remainder_path = trim(str_replace($last_url,'', $route_path));
                if (substr($remainder_path, -1) == '/') {
                    $remainder_path = substr($remainder_path, 0, -1);
                }
                $a_route_parts                   = $a_last_good_results;
                $this->route_path                = $a_route_parts['route_path'];
                $a_route_parts['request_uri']    = $route_path;
                $a_route_parts['url_actions']    = [];
                $a_route_parts['url_actions']    = explode('/', $remainder_path);
                $a_route_parts['groups']         = $this->getGroups($a_route_parts['route_id']);
                $a_route_parts['min_auth_level'] = $this->getMinAuthLevel($a_route_parts['groups']);
                $this->a_route_parts = $a_route_parts;
            }
            else {
                $this->a_route_parts = [
                    'route_id'       => 0,
                    'route_path'     => $route_path,
                    'request_uri'    => $route_path,
                    'route_class'    => 'MainController',
                    'route_method'   => '',
                    'route_action'   => '',
                    'url_actions'    => [],
                    'groups'         => [],
                    'min_auth_level' => 0
                ];
            }
        }
    }
    /**
     *  Shortcut for setRouteParts and getRouteParts.
     *  @param string $route_path
     *  @return array
     */
    public function createRouteParts($route_path = '')
    {
        $this->setRouteParts($route_path);
        return $this->getRouteParts();
    }
    /**
     *  @param $route_id
     *  @return array|mixed
     */
    public function getGroups($route_id)
    {
        $a_groups = array();
        $a_rgm_results = $this->o_rgm->read(['route_id' => $route_id]);
        if ($a_rgm_results !== false && count($a_rgm_results) > 0) {
            foreach ($a_rgm_results as $a_rgm) {
                $a_groups[] = $a_rgm['group_id'];
            }
        }
        return $a_groups;
    }
    /**
     *  Gets the min auth level needed for the route.
     *  The groups for the route are passed in
     *     and iterated searching the group db for the auth level.
     *  @param array $a_groups
     *  @return int
     */
    public function getMinAuthLevel(array $a_groups = array())
    {
        $min_auth_level = 0;
        foreach ($a_groups as $group_id) {
            $results = $this->o_group->readById($group_id);
            if (!is_null($results) && $results['group_auth_level'] >= $min_auth_level) {
                $min_auth_level = $results['group_auth_level'];
            }
        }
        return $min_auth_level;
    }
    /**
     *  @param string $route_path
     */
    public function setRoutePath($route_path = '')
    {
        $this->route_path = $route_path;
    }
    /**
     *  @return string
     */
    public function getRoutePath()
    {
        return $this->route_path;
    }
    /**
     *  @return array
     */
    public function getRouteParts()
    {
        return $this->a_route_parts;
    }
    /**
     *  @return string
     */
    public function getRequestUri()
    {
        return $this->request_uri;
    }
}
