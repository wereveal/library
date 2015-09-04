<?php
/**
 *  @brief Determines the controller and method to use based on URI.
 *  @file Router.php
 *  @ingroup ritc_library services
 *  @namespace Ritc/Library/Services
 *  @class Router
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β6
 *  @date 2015-09-03 16:49:31
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β7 - Added Allowed Groups to the class.                       - 09/03/2015 wer
 *                 Groups can now be mapped to the route.
 *      v1.0.0β6 - Removed abstract class Base, added LogitTraits           - 09/01/2015 wer
 *      v1.0.0β5 - changed several properties to be static (just in case)   - 01/06/2015 wer
 *      v1.0.0β4 - changed to use Di class for DI/IOC.                      - 12/10/2014 wer
 *      v1.0.0β3 - added form_action class property.                        - 12/05/2014 wer
 *                 Added setter and getters for form_action class property.
 *      v1.0.0β2 - moved to Services namespace                              - 11/15/2014 wer
 *      v1.0.0β1 - bug fixes                                                - 11/14/2014 wer
 *      v1.0.0β0 - initial attempt to make this                             - 09/25/2014 wer
**/
namespace Ritc\Library\Services;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Models\RouterGroupMapModel;
use Ritc\Library\Models\RouterModel;
use Ritc\Library\Models\RouterRolesMapModel;
use Ritc\Library\Traits\LogitTraits;

class Router
{
    use LogitTraits;

    private $a_get;
    private $a_post;
    private $a_route_parts;
    private $o_model;
    private $o_rgm;
    private $o_rrm;
    public static $form_action;
    public static $route_action;
    public static $route_class;
    public static $route_method;
    public static $route_path;

    public function __construct(Di $o_di)
    {
        $o_db = $o_di->get('db');
        $this->o_model  = new RouterModel($o_db);
        $this->o_rrm = new RouterRolesMapModel($o_db);
        $this->o_rgm = new RouterGroupMapModel($o_db);
        $this->setRoutePath();
        $this->setGet();
        $this->setPost();
        $this->setFormAction();
        $this->setRouteParts();
        if (defined('DEVELOPER_MODE')) {
            if (DEVELOPER_MODE) {
                $this->o_elog = $o_di->get('elog');
                $this->o_model->setElog($this->o_elog);
            }
        }
    }

    /**
     *  Sets the route parts from the database plus any GET parts and Post parts.
     *  @param string $route_path
     *  @return array
     */
    public function setRouteParts($route_path = '')
    {
        if ($route_path == '') {
            if (self::$route_path != '') {
                $route_path = self::$route_path;
            }
            else {
                self::$route_path = $_SERVER["REQUEST_URI"];
                $route_path = self::$route_path;
            }
        }
        $a_values = ['route_path' => $route_path];
        $a_results = $this->o_model->read($a_values);
        $this->logIt("Actions from DB: " . var_export($a_results, true), LOG_OFF, __METHOD__);
        if ($a_results !== false && count($a_results) === 1) {
            $a_route_parts                = $a_results[0];
            $a_rrm_results                = $this->o_rrm->read(['route_id' => $a_route_parts['route_id']]);
            if ($a_rrm_results !== false && count($a_rrm_results) > 0) {
                $a_route_parts['roles']   = $a_rrm_results;
            }
            $a_rgm_results                = $this->o_rgm->read(['route_id' => $a_route_parts['route_id']]);
            if ($a_rgm_results !== false && count($a_rgm_results) > 0) {
                $a_route_parts['groups']  = $a_rgm_results;
            }
            $a_route_parts['get']         = $this->a_get;
            $a_route_parts['post']        = $this->a_post;
            $a_route_parts['form_action'] = self::$form_action;
            $this->a_route_parts          = $a_route_parts;
        }
        else {
            $this->a_route_parts = [
                'route_id'     => 0,
                'route_path'   => $route_path,
                'route_class'  => 'MainController',
                'route_method' => '',
                'route_action' => '',
                'roles'        => array(),
                'groups'       => array(),
                'get'          => $this->a_get,
                'post'         => $this->a_post,
                'form_action'  => self::$form_action
            ];
        }
        self::$route_action = $this->a_route_parts['route_action'];
        self::$route_class  = $this->a_route_parts['route_class'];
        self::$route_method = $this->a_route_parts['route_method'];
    }

    ### GETters and SETters ###
    /**
     * Gets the groups that are allowed to access this route.
     * @return array
     */
    public function getAllowedGroups()
    {
        return $this->a_route_parts['groups'];
    }
    /**
     * Gets the roles that are allowed to access this route.
     * return array $a_allowed_roles
     */
    public function getAllowedRoles()
    {
        return $this->a_route_parts['roles'];
    }
    /**
     * @return mixed
     */
    public function getFormAction()
    {
        return self::$form_action;
    }
    /**
     * @param string $value
     * @return string
     */
    public function getGet($value = '')
    {
        if ($value == '') {
            return $this->a_get;
        } else {
            if (isset($this->a_get[$value])) {
                return $this->a_get[$value];
            } else {
                $this->logIt(var_export($this->a_clean_post, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return '';
            }
        }
    }
    /**
     * @return mixed
     */
    public function getRouteAction()
    {
        return self::$route_action;
    }
    /**
     * @return mixed
     */
    public function getRouteClass()
    {
        return self::$route_class;
    }
    /**
     * @return mixed
     */
    public function getRoutePath()
    {
        return self::$route_path;
    }
    /**
     * @return mixed
     */
    public function getRouteParts()
    {
        return $this->a_route_parts;
    }
    /**
     * @param string $value
     * @return bool
     */
    public function getPost($value = '')
    {
        if ($value == '') {
            return $this->a_post;
        }
        else {
            $this->logIt("Value is: {$value}", LOG_OFF, __METHOD__ . '.' . __LINE__);
            if (isset($this->a_post[$value])) {
                return $this->a_post[$value];
            }
            else {
                $this->logIt("The Value Doesn't Exist. " . var_export($this->a_post, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            }
        }
    }
    /**
     *  Sets the class property $form_action.
     *  Assumes the array passed in is from a form posted after it was sanitized.
     *  @param array $a_post an associate array put through filter_var($var, FILTER_SANITIZE_ENCODED)
     *  @return bool
     */
    public function setFormAction(array $a_post = array())
    {
        if ($a_post == array()) {
            $a_post = $this->a_post;
        }
        $this->logIt("Starting with: " . var_export($a_post, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $x_action = '';
        $y_action = '';
        foreach ($a_post as $key=>$value) {
            if (substr($key, strlen($key) - 2) == "_x") {
                $x_action = substr($key, 0, strpos($key, "_x"));
            }
            elseif (substr($key, strlen($key) - 2) == "_y") {
                $y_action = substr($key, 0, strpos($key, "_y"));
            }
        }
        if (isset($a_post["action"]) && ($a_post["action"] != '')) {
            $action = $a_post["action"];
        }
        elseif (isset($a_post["step"]) && ($a_post["step"] != '')) {
            $action = $a_post["step"];
        }
        elseif (isset($a_post["submit"]) && ($a_post["submit"] != '')) {
            $action = $a_post["submit"];
        }
        elseif (($x_action != '') && ($x_action == $y_action)) {
            $action = $x_action;
        }
        else {
            $action = '';
        }
        $this->logIt("Form Action is: {$action}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        self::$form_action = $action;
        return true;
    }
    /**
     * @param array $a_allowed_keys
     */
    public function setGet(array $a_allowed_keys = array())
    {
        $this->a_get = Arrays::cleanArrayValues($_GET, $a_allowed_keys, true);
    }
    /**
     *  Sets the property $route_path.
     *  @param string $request_uri optional, defaults to $_SERVER['REQUEST_URI']
     *  @return null
     */
    public function setRoutePath($request_uri = '')
    {
        if ($request_uri == '') {
            $request_uri = $_SERVER["REQUEST_URI"];
        }
        if (strpos($request_uri, "?") !== false) {
            self::$route_path = substr($request_uri, 0, strpos($request_uri, "?"));
        }
        else {
            self::$route_path = $request_uri;
        }
    }
    /**
     * @param array $a_allowed_keys
     */
    public function setPost(array $a_allowed_keys = array())
    {
        $this->a_post = Arrays::cleanArrayValues($_POST, $a_allowed_keys, true);
    }
    /* From Base Abstract
        protected function getElog
        protected function logIt
        protected function setElog
    */
}
