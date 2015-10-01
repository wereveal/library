<?php
/**
 *  @brief Various helper functions for routes.
 *  @file RoutesHelper.php
 *  @namespace Ritc/Library/Helper
 *  @class RoutesHelper
 *  @author William E Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-09-26 02:09:32
 *  @note Change Log
 *      v1.0.0β1 - intial file                   - 09/26/2015 wer
 *  @note RITC Library
 *  @ingroup ritc_library helper
 **/
namespace Ritc\Library\Helper;

use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\RouterGroupMapModel;
use Ritc\Library\Models\RoutesModel;
use Ritc\Library\Models\RouterRolesMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

class RoutesHelper
{
    use LogitTraits;

    private $a_route_parts;
    private $o_model;
    private $o_rgm;
    private $o_role;
    private $o_rrm;
    private $route_path;

    public function __construct(Di $o_di, $route_path = '')
    {
        $o_db = $o_di->get('db');
        $this->o_model = new RoutesModel($o_db);
        $this->o_role  = new RolesModel($o_db);
        $this->o_rgm   = new RouterGroupMapModel($o_db);
        $this->o_rrm   = new RouterRolesMapModel($o_db);
        $this->route_path = $route_path;
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_model->setElog($this->o_elog);
        }
    }

    public function setRouteParts($route_path = '')
    {
        if ($route_path == '') {
            if ($this->route_path != '') {
                $route_path = $this->route_path;
            }
            else {
                $this->route_path = $_SERVER["REQUEST_URI"];
                $route_path = $this->route_path;
            }
        }
        else {
            $this->route_path = $route_path;
        }
        $a_values = ['route_path' => $route_path];
        $a_results = $this->o_model->read($a_values);
        $this->logIt("Actions from DB: " . var_export($a_results, true), LOG_OFF, __METHOD__);
        if ($a_results !== false && count($a_results) === 1) {
            $a_route_parts                   = $a_results[0];
            $a_route_parts['route_path']     = $route_path;
            $a_route_parts['url_actions']    = [];
            $a_route_parts['roles']          = $this->getRoles($a_route_parts['route_id']);
            $a_route_parts['groups']         = $this->getGroups($a_route_parts['route_id']);
            $a_route_parts['min_role_level'] = $this->getMinRoleLevel($a_route_parts['roles'], $a_route_parts['route_id']);
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
                $a_route_parts['route_path']     = $route_path;
                $a_route_parts['url_actions']    = [];
                $a_route_parts['url_actions']    = explode('/', $remainder_path);
                $a_route_parts['roles']          = $this->getRoles($a_route_parts['route_id']);
                $a_route_parts['groups']         = $this->getGroups($a_route_parts['route_id']);
                $a_route_parts['min_role_level'] = $this->getMinRoleLevel($a_route_parts['roles']);
                $this->a_route_parts = $a_route_parts;
            }
            else {
                $this->a_route_parts = [
                    'route_id'       => 0,
                    'route_path'     => $this->route_path,
                    'route_class'    => 'MainController',
                    'route_method'   => '',
                    'route_action'   => '',
                    'url_actions'    => [],
                    'roles'          => [],
                    'groups'         => [],
                    'min_role_level' => 999
                ];
            }
        }
    }
    /**
     * Shortcut for setRouteParts and getRouteParts.
     * @param string $route_path
     * @return array
     */
    public function createRouteParts($route_path = '')
    {
        $this->setRouteParts($route_path);
        return $this->getRouteParts();
    }
    /**
     * @param $route_id
     * @return array|mixed
     */
    public function getRoles($route_id)
    {
        $a_roles = array();
        $a_rrm_results = $this->o_rrm->read(['route_id' => $route_id]);
        if ($a_rrm_results !== false && count($a_rrm_results) > 0) {
            foreach($a_rrm_results as $a_rrm) {
                $a_roles[] = $a_rrm['role_id'];
            }
        }
        return $a_roles;
    }
    /**
     * @param $route_id
     * @return array|mixed
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
     * Gets the min role level needed for the route.
     * The roles allowed are passed in and iterated to determine the role level.
     * Searches the role db for the role level.
     * @param array $a_roles
     * @return int
     */
    public function getMinRoleLevel(array $a_roles = array())
    {
        $min_role_level = 0;
        foreach ($a_roles as $role_id) {
            $results = $this->o_role->readById($role_id);
            if ($results['role_level'] >= $min_role_level) {
                $min_role_level = $results['role_level'];
            }
        }
        return $min_role_level;
    }
    /**
     * @param string $route_path
     */
    public function setRoutePath($route_path = '')
    {
        $this->route_path = $route_path;
    }
    /**
     * @return string
     */
    public function getRoutePath()
    {
        return $this->route_path;
    }
    /**
     * @return array
     */
    public function getRouteParts()
    {
        return $this->a_route_parts;
    }
}