<?php
/**
 * @brief     Various helper functions for routes.
 * @ingroup   lib_helper
 * @file      Ritc/Library/Helper/RoutesHelper.php
 * @namespace Ritc\Library\Helper
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2015-11-27 14:55:02
 * @note Change Log
 * - v1.1.0   - added method for quick min auth level for a route.   - 02/26/2016 wer
 * - v1.0.0   - took out of beta                                     - 11/27/2015 wer
 * - v1.0.0β3 - bug fix                                              - 11/24/2015 wer
 * - v1.0.0β2 - logic change                                         - 10/30/2015 wer
 * - v1.0.0β1 - intial file                                          - 09/26/2015 wer
 */
namespace Ritc\Library\Helper;

use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RoutesGroupMapModel;
use Ritc\Library\Models\RoutesModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class RoutesHelper
 * @class   RoutesHelper
 * @package Ritc\Library\Helper
 */
class RoutesHelper
{
    use LogitTraits;

    /** @var array */
    private $a_route_parts;
    /** @var \Ritc\Library\Models\GroupsModel */
    private $o_group;
    /** @var \Ritc\Library\Models\RoutesModel */
    private $o_model;
    /** @var \Ritc\Library\Models\RoutesGroupMapModel */
    private $o_rgm;
    /** @var string */
    private $route_path;
    /** @var string */
    private $request_uri;

    /**
     * RoutesHelper constructor.
     * @param \Ritc\Library\Services\Di $o_di
     * @param string                    $route_path
     */
    public function __construct(Di $o_di, $request_uri = '')
    {
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }

        $o_db = $o_di->get('db');
        $this->o_model = new RoutesModel($o_db);
        $this->o_group = new GroupsModel($o_db);
        $this->o_rgm   = new RoutesGroupMapModel($o_db);
        $this->route_path = $request_uri;

        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_model->setElog($this->o_elog);
            $this->o_group->setElog($this->o_elog);
        }
    }

    /**
     * @param string $request_uri
     * @return null
     */
    public function setRouteParts($request_uri = '')
    {
        $meth = __METHOD__ . '.';
        $a_route_parts = [
            'route_id'       => 0,
            'route_path'     => $request_uri,
            'request_uri'    => $request_uri,
            'route_class'    => 'MainController',
            'route_method'   => '',
            'route_action'   => '',
            'url_actions'    => [],
            'groups'         => [],
            'min_auth_level' => 0
        ];
        if ($request_uri == '') {
            if ($this->request_uri != '') {
                $request_uri = $this->request_uri;
            }
            else {
                $request_uri = $_SERVER["REQUEST_URI"];
            }
        }

        $a_route = $this->findValidRoute($request_uri);
        if ($a_route !== false) {
            $a_route_parts = $a_route;
        }

        if ($a_route_parts['route_id'] !== 0) {
            $this->request_uri               = $request_uri;
            $this->route_path                = $a_route_parts['url_text'];
            $a_route_parts['request_uri']    = $request_uri;
            $a_route_parts['route_path']     = $a_route_parts['url_text'];

            $a_url_actions = [];
            if ($this->request_uri != $this->route_path) {
                $uri_actions = str_replace($this->route_path, '', $this->request_uri);
                if (strrpos($uri_actions, '/') !== false) {
                    $uri_actions = substr($uri_actions, 0, strlen($uri_actions) - 1);
                }
                $a_url_actions = explode('/', $uri_actions);
            }

            $a_route_parts['url_actions']    = $a_url_actions;
            $a_route_parts['groups']         = $this->getGroups($a_route_parts['route_id']);
            $a_route_parts['min_auth_level'] = $this->getMinAuthLevel($a_route_parts['groups']);
        }

        $log_message = 'Route parts:  ' . var_export($a_route_parts, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $this->a_route_parts = $a_route_parts;
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
     * Returns the routes database record based on the request uri.
     * Note that this is a recursive method so it can find a route which is
     * a subset of the request uri, e.g. /fred/flinstone/barney/rubble/
     * could return the route for /fred/flinstone/ if there is no
     * /fred/flinstone/barney/rubble/ request_uri based route.
     * @param string $request_uri
     * @return array|bool
     */
    public function findValidRoute($request_uri = '')
    {
        $meth = __METHOD__ . '.';
        $a_results = $this->o_model->readWithRequestUri($request_uri);
        $log_message = 'For the request uri: ' .
            $request_uri .
            ' the readWidthRequestUri results:  '
            . var_export($a_results, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        if ($a_results === false) {
            $error_message = $this->o_model->getErrorMessage();
            $this->logIt("Error Message: " . var_export($error_message, true), LOG_OFF, $meth . __LINE__);
        }
        if ($a_results !== false && count($a_results) === 1) {
            return $a_results[0];
        }
        else {
            if ($request_uri == '/') {
                return false;
            }
            if (strpos($request_uri, '/') == 0) {
                $uri = substr($request_uri, 1);
            }
            else {
                $uri = $request_uri;
            }
            $uri_length = strlen($uri);
            if (strrpos($uri, '/') == $uri_length - 1) {
                $uri = substr($uri, 0, $uri_length - 1);
            }

            $a_uri_parts = explode('/', $uri);
            $new_request_uri = str_replace($a_uri_parts[count($a_uri_parts) - 1], '', $request_uri);
            if (strrpos($new_request_uri, '//') !== false) {
                $new_request_uri = substr($new_request_uri, 0, strlen($new_request_uri) - 1);
            }
            return $this->findValidRoute($new_request_uri);
        }
    }

    /**
     * @param int $route_id
     * @return array|mixed
     */
    public function getGroups($route_id = -1)
    {
        if ($route_id == -1) { return []; }
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
     * Gets the min auth level needed for the route.
     * The groups for the route are passed in
     *    and iterated searching the group db for the auth level.
     * @param array $a_groups
     * @return int
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
     * Returns minimum auth level for a route.
     * @param int $route_id if not supplied an auth level of 10 is returned.
     * @return int
     */
    public function getMinAuthLevelForRoute($route_id = -1)
    {
        if ($route_id == -1) { return 10; }
        $a_groups = $this->getGroups($route_id);
        return $this->getMinAuthLevel($a_groups);
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

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->request_uri;
    }
}
