<?php
/**
 * Class Router
 * @package Ritc_Library
 */
namespace Ritc\Library\Services;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\RoutesHelper;

/**
 * Figures out the routes and route parts.
 * Determines the controller and method to use based on URI.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-12-01 13:48:23
 * @change_log
 * - 2.0.0    - updated for php 8 standards                                     - 2021-12-01 wer
 * - 1.2.1    - bug fix, comment change                                         - 2018-03-30 wer
 * - 1.2.0    - Changed Router::setPost() and Router::setGet to use the         - 2016-09-19 wer
 *               new Arrays::cleanValues() method. By default, they do
 *               no create entities in the strings now.
 * - 1.1.0    - Added property and methods for url_id                           - 2016-04-01 wer
 * - 1.0.0    - take out of beta                                                - 11/27/2015 wer
 * - 1.0.0β10 - Bug fix to fix logic error with route path                      - 11/24/2015 wer
 * - 1.0.0β0  - initial attempt to make this                                    - 09/25/2014 wer
 */
class Router
{
    /** @var array values from a $_GET */
    private array $a_get;
    /** @var array values from a $_POST */
    private array $a_post;
    /** @var array $a_router_parts */
    private array $a_router_parts;
    /** @var RoutesHelper $o_routes_helper */
    private RoutesHelper $o_routes_helper;
    /** @var string the action specified by a form */
    private string $form_action;
    /** @var string the asked for route */
    private string $request_uri;
    /** @var string the action associated with the route_path */
    private string $route_action;
    /** @var string the class associated with the route_path */
    private string $route_class;
    /** @var string the method associated with the route_path */
    private string $route_method;
    /** @var string the path being routed (may be different from the request uri) */
    private string $route_path;
    /** @var  int the url id for the page */
    private int $url_id;

    /**
     * Router constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
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
     *
     * @param string $request_uri Optional, normally NOT used.
     *                            Defaults to '' which turns into $_SERVER['REQUEST_URI']
     *                            in the setRequestUri method.
     */
    public function setRouteParts(string $request_uri = ''):void
    {
        $this->setRequestUri($request_uri);
        $this->setGet();
        $this->setPost();
        $this->setFormAction();
        $a_router_parts = $this->o_routes_helper->createRouteParts($this->request_uri);
        $a_router_parts['get']         = $this->a_get;
        $a_router_parts['post']        = $this->a_post;
        $a_router_parts['form_action'] = $this->form_action;
        $this->a_router_parts = $a_router_parts;
        $this->request_uri    = $a_router_parts['request_uri'];
        $this->route_path     = $a_router_parts['route_path'];
        $this->route_action   = $a_router_parts['route_action'];
        $this->route_class    = $a_router_parts['route_class'];
        $this->route_method   = $a_router_parts['route_method'];
        $this->url_id         = $a_router_parts['url_id'];
    }

    ### GETters and SETters ###
    /**
     * Gets the groups that are allowed to access this route.
     *
     * @return array
     */
    public function getAllowedGroups():array
    {
        return $this->a_router_parts['groups'];
    }

    /**
     * Returns the property form_action.
     *
     * @return string
     */
    public function getFormAction(): string
    {
        return $this->form_action;
    }

    /**
     * Returns the property a_get or one of the array values based on name.
     *
     * @param string $value
     * @return array|string
     */
    public function getGet(string $value = ''): array|string
    {
        if ($value === '') {
            return $this->a_get;
        }
        return $this->a_get[$value] ?? '';
    }

    /**
     * Returns the property a_post or one of the property array values.
     *
     * @param string $value
     * @return array|string
     */
    public function getPost(string $value = ''): array|string
    {
        if ($value === '') {
            return $this->a_post;
        }
        return $this->a_post[$value] ?? '';
    }

    /**
     * Returns the class property route_action value.
     *
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->request_uri;
    }

    /**
     * Returns the class property route_action.
     *
     * @return string
     */
    public function getRouteAction(): string
    {
        return $this->route_action;
    }

    /**
     * Returns the property route_class value.
     *
     * @return string
     */
    public function getRouteClass(): string
    {
        return $this->route_class;
    }

    /**
     * Returns the property route_path value.
     *
     * @return array
     */
    public function getRouteParts(): array
    {
        return $this->a_router_parts;
    }

    /**
     * Returns the property a_router_parts value.
     *
     * @return string
     */
    public function getRoutePath():string
    {
        return $this->route_path;
    }

    /**
     * Returns the property url_id.
     *
     * @return int
     */
    public function getUrlId():int
    {
        return $this->url_id;
    }

    /**
     * Sets the class property $form_action.
     * Assumes the array passed in is from a form posted after it was sanitized.
     *
     * @param array $a_post an associate array put through filter_var($var, FILTER_SANITIZE_ENCODED)
     * @return bool
     * @noinspection DuplicatedCode
     */
    public function setFormAction(array $a_post = []):bool
    {
        if ($a_post === []) {
            $a_post = $this->a_post;
        }
        $x_action = '';
        $y_action = '';
        foreach ($a_post as $key=>$value) {
            if (str_ends_with($key, '_x')) {
                $x_action = substr($key, 0, strpos($key, '_x'));
            }
            elseif (str_ends_with($key, '_y')) {
                $y_action = substr($key, 0, strpos($key, '_y'));
            }
        }
        if (isset($a_post['action']) && ($a_post['action'] !== '')) {
            $action = $a_post['action'];
        }
        elseif (isset($a_post['step']) && ($a_post['step'] !== '')) {
            $action = $a_post['step'];
        }
        elseif (isset($a_post['submit']) && ($a_post['submit'] !== '')) {
            $action = $a_post['submit'];
        }
        elseif (($x_action !== '') && ($x_action === $y_action)) {
            $action = $x_action;
        }
        else {
            $action = '';
        }
        $this->form_action = $action;
        return true;
    }

    /**
     * Sets the property a_get with semi-sanitized values from $_GET.
     *
     * @param array $a_allowed_keys
     * @param array $a_allowed_commands
     * @param int   $filter_flags
     */
    public function setGet(array $a_allowed_keys = [], array $a_allowed_commands = [], int $filter_flags = 0):void
    {
        $this->a_get = Arrays::cleanValues($_GET, $a_allowed_keys, $a_allowed_commands, $filter_flags);
    }

    /**
     * Sets the property $request_uri.
     *
     * @param string $request_uri optional, defaults to $_SERVER['REQUEST_URI']
     */
    public function setRequestUri(string $request_uri = ''):void
    {
        if ($request_uri === '') {
            $request_uri = empty($_SERVER['REQUEST_URI'])
                ? '/'
                : $_SERVER['REQUEST_URI'];
        }
        if (str_contains($request_uri, '?')) {
            $this->request_uri = substr($request_uri, 0, strpos($request_uri, '?'));
        }
        else {
            $this->request_uri = $request_uri;
        }
    }

    /**
     * Sets the property route_path.
     *
     * @param string $value
     */
    public function setRoutePath(string $value = ''):void
    {
        if ($value !== '') {
            $this->route_path = $value;
        }
    }

    /**
     * Sets the property a_post.
     *
     * @param array $a_allowed_keys
     * @param array $a_allowed_commands
     * @param int   $filter_flags
     */
    public function setPost(array $a_allowed_keys = [], array $a_allowed_commands = [], int $filter_flags = 0):void
    {
        $this->a_post = Arrays::cleanValues($_POST, $a_allowed_keys, $a_allowed_commands, $filter_flags);
    }

    /**
     * @return string
     */
    public function getRouteMethod(): string
    {
        return $this->route_method;
    }

    /**
     * @param string $route_method
     */
    public function setRouteMethod(string $route_method): void
    {
        $this->route_method = $route_method;
    }

}
