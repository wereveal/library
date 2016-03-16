<?php
/**
 * @brief     Determines the controller and method to use based on URI.
 * @ingroup   lib_services
 * @file      Router.php
 * @namespace Ritc\Library\Services
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.1
 * @date      2016-02-22 15:40:33
 * @note <b>Change Log</b>
 * - v1.0.1    - clean up of code.                                           - 02/22/2016 wer
 * - v1.0.0    - take out of beta                                            - 11/27/2015 wer
 * - v1.0.0β10 - Bug fix to fix logic error with route path                  - 11/24/2015 wer
 * - v1.0.0β9  - Bug fixes to fix logic error in actionable data             - 09/22/2015 wer
 * - v1.0.0β8  - Changed to allow route path to include additional           - 09/14/2015 wer
 *                 actionable data.
 * - v1.0.0β7  - Added Allowed Groups to the class.                          - 09/03/2015 wer
 *                 Groups can now be mapped to the route.
 * - v1.0.0β6  - Removed abstract class Base, added LogitTraits              - 09/01/2015 wer
 * - v1.0.0β5  - changed several properties to be static (just in case)      - 01/06/2015 wer
 * - v1.0.0β4  - changed to use Di class for DI/IOC.                         - 12/10/2014 wer
 * - v1.0.0β3  - added form_action class property.                           - 12/05/2014 wer
 *                 Added setter and getters for form_action class property.
 * - v1.0.0β2  - moved to Services namespace                                 - 11/15/2014 wer
 * - v1.0.0β1  - bug fixes                                                   - 11/14/2014 wer
 * - v1.0.0β0  - initial attempt to make this                                - 09/25/2014 wer
 */
namespace Ritc\Library\Services;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\RoutesHelper;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class Router figures out the routes and route parts.
 * @class Router
 * @package Ritc\Library\Services
 */
class Router
{
    use LogitTraits;

    /** @var array */
    private $a_get;
    /** @var array */
    private $a_post;
    /** @var array */
    private $a_router_parts;
    /** @var \Ritc\Library\Helper\RoutesHelper */
    private $o_routes_helper;
    /** @var string the action specified by a form */
    private $form_action;
    /** @var string the asked for route */
    private $request_uri;
    /** @var string the action associated with the route_path */
    private $route_action;
    /** @var string the class associated with the route_path */
    private $route_class;
    /** @var string the method associated with the route_path */
    private $route_method;
    /** @var string the path being routed (may be different from the request uri) */
    private $route_path;

    /**
     * Router constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
        $this->o_routes_helper = new RoutesHelper($o_di);
        $this->setRouteParts();
    }

    /**
     * Sets the router parts from the routes helper plus other information.
     *  - Sets the router parts from the routes helper plus other information.
     *  - Sets additional url actionable parts, GET parts and Post parts.
     *  - Addional url actional parts is like /fred/barney/wilma/ which could
     *    only be found as /fred/barney/ by the routerModel class.
     *      - wilma represents a variable that the controller understands.
     *      - For example wilma could be an blog id or blog name 'blog_id'
     *      - for which a blog controller would search in the blog db by blog_id.
     * @return array
     */
    public function setRouteParts()
    {
        $meth = __METHOD__ . '.';
        $this->setRequestUri();
        $this->setGet();
        $this->setPost();
        $this->setFormAction();
        $this->logIt("Request URI: " . $this->request_uri, LOG_OFF, $meth . __LINE__);
        $a_router_parts = $this->o_routes_helper->createRouteParts($this->request_uri);
        $log_message = 'a_router_parts ' . var_export($a_router_parts, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);
        $a_router_parts['get']         = $this->a_get;
        $a_router_parts['post']        = $this->a_post;
        $a_router_parts['form_action'] = $this->form_action;
        $this->a_router_parts = $a_router_parts;
        $this->request_uri    = $a_router_parts['request_uri'];
        $this->route_path     = $a_router_parts['route_path'];
        $this->route_action   = $a_router_parts['route_action'];
        $this->route_class    = $a_router_parts['route_class'];
        $this->route_method   = $a_router_parts['route_method'];
    }

    ### GETters and SETters ###
    /**
     * Gets the groups that are allowed to access this route.
     * @return array
     */
    public function getAllowedGroups()
    {
        return $this->a_router_parts['groups'];
    }

    /**
     * Returns the property form_action.
     * @return mixed
     */
    public function getFormAction()
    {
        return $this->form_action;
    }

    /**
     * Returns the property a_get or one of the array values based on name.
     * @param string $value
     * @return array|string
     */
    public function getGet($value = '')
    {
        if ($value == '') {
            return $this->a_get;
        } else {
            if (isset($this->a_get[$value])) {
                return $this->a_get[$value];
            } else {
                $this->logIt(var_export($this->a_get, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return '';
            }
        }
    }

    /**
     * Returns the property route_action value.
     * @return mixed
     */
    public function getRequestUri()
    {
        return $this->request_uri;
    }

    /**
     * @return mixed
     */
    public function getRouteAction()
    {
        return $this->route_action;
    }

    /**
     * Returns the property route_class value.
     * @return mixed
     */
    public function getRouteClass()
    {
        return $this->route_class;
    }

    /**
     * Returns the property route_path value.
     * @return mixed
     */
    public function getRouteParts()
    {
        return $this->a_router_parts;
    }

    /**
     * Returns the property a_router_parts value.
     * @return array
     */
    public function getRoutePath()
    {
        return $this->route_path;
    }

    /**
     * Returns the property a_post or one of the property array values.
     * @param string $value
     * @return bool
     */
    public function getPost($value = '')
    {
        $meth = __METHOD__ . '.';
        if ($value == '') {
            return $this->a_post;
        }
        else {
            $this->logIt("Value is: {$value}", LOG_OFF, $meth . __LINE__);
            if (isset($this->a_post[$value])) {
                return $this->a_post[$value];
            }
            else {
                $this->logIt("The Value Doesn't Exist. " . var_export($this->a_post, true), LOG_OFF, $meth . __LINE__);
                return false;
            }
        }
    }

    /**
     * Sets the class property $form_action.
     * Assumes the array passed in is from a form posted after it was sanitized.
     * @param array $a_post an associate array put through filter_var($var, FILTER_SANITIZE_ENCODED)
     * @return bool
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
        $this->form_action = $action;
        return true;
    }

    /**
     * Sets the property a_get with semi-sanitized values from $_GET.
     * @param array $a_allowed_keys
     */
    public function setGet(array $a_allowed_keys = array())
    {
        $this->a_get = Arrays::cleanArrayValues($_GET, $a_allowed_keys, true);
    }

    /**
     * Sets the property $request_uri.
     * @param string $request_uri optional, defaults to $_SERVER['REQUEST_URI']
     * @return null
     */
    public function setRequestUri($request_uri = '')
    {
        error_log(var_export($_SERVER, true));
        if ($request_uri == '') {
            $request_uri = $_SERVER["REQUEST_URI"];
        }
        if (strpos($request_uri, "?") !== false) {
            $this->request_uri = substr($request_uri, 0, strpos($request_uri, "?"));
        }
        else {
            $this->request_uri = $request_uri;
        }
    }

    /**
     * Sets the property route_path.
     * @param string $value
     */
    public function setRoutePath($value = '')
    {
        if ($value != '') {
            $this->route_path = $value;
        }
    }

    /**
     * Sets the property a_post.
     * @param array $a_allowed_keys
     */
    public function setPost(array $a_allowed_keys = array())
    {
        $this->a_post = Arrays::cleanArrayValues($_POST, $a_allowed_keys, true);
    }

    /* From LogItTraits
        protected function getElog
        protected function logIt
        protected function setElog
   */
}
