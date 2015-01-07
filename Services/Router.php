<?php
/**
 *  @brief Determines the controller and method to use based on URI.
 *  @file Router.php
 *  @ingroup ritc_library services
 *  @namespace Ritc/Library/Services
 *  @class Router
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.5ß
 *  @date 2015-01-06 09:43:49
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.5ß - changed several properties to be static (just in case)                    - 01/06/2015 wer
 *      v1.0.4ß - changed to use Di class for DI/IOC.                                       - 12/10/2014 wer
 *      v1.0.3ß - added form_action class property.                                         - 12/05/2014 wer
 *                Added setter and getters for form_action class property.
 *      v1.0.2ß - moved to Services namespace                                               - 11/15/2014 wer
 *      v1.0.1ß - bug fixes                                                                 - 11/14/2014 wer
 *      v1.0.0ß - initial attempt to make this                                              - 09/25/2014 wer
**/
namespace Ritc\Library\Services;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Models\RouterModel;

class Router extends Base
{
    private $a_get;
    private $a_post;
    private $a_route_parts;
    private $o_model;
    public static $form_action;
    public static $route_action;
    public static $route_method;
    public static $route_path;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $o_db = $o_di->get('db');
        if (defined('DEVELOPER_MODE')) {
            if (DEVELOPER_MODE) {
                $this->o_elog = $o_di->get('elog');
            }
        }
        $this->o_arrays = new Arrays();
        $this->o_model  = new RouterModel($o_db);
        $this->setRoutePath();
        $this->setGet();
        $this->setPost();
        $this->setFormAction();
        $this->setRouteParts();
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
                'get'          => $this->a_get,
                'post'         => $this->a_post,
                'form_action'  => self::$form_action
            ];
        }
        self::$route_action = $this->a_route_parts['route_action'];
        self::$route_method = $this->a_route_parts['route_method'];
    }

    ### GETters and SETters ###
    public function getFormAction()
    {
        return self::$form_action;
    }
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
    public function getRoutePath()
    {
        return self::$route_path;
    }
    public function getRouteParts()
    {
        return $this->a_route_parts;
    }
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
    
    public function setGet(array $a_allowed_keys = array())
    {
        $this->a_get = $this->o_arrays->cleanArrayValues($_GET, $a_allowed_keys, true);
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
    public function setPost(array $a_allowed_keys = array())
    {
        $this->a_post = $this->o_arrays->cleanArrayValues($_POST, $a_allowed_keys, true);
    }
    /* From Base Abstract
        protected function getElog
        protected function logIt
        protected function setElog
    */
}
