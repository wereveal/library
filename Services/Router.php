<?php
/**
 *  @brief Determines the controller and method to use based on URI.
 *  @file Router.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Services
 *  @class Router
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1ß
 *  @date 2014-11-14 07:29:27
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
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
    private $route_path;

    public function __construct(DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->o_arrays = new Arrays();
        $this->setRoutePath();
        $this->setGet();
        $this->setPost();
        $this->setRouteParts();
        $this->o_model = new RouterModel($o_db);
    }

    /**
     *  Sets the route parts from the database plus any GET parts and Post parts.
     *  @param string $route_path
     *  @return array
     */
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
            $a_route_parts         = $a_results[0];
            $a_route_parts['get']  = $this->a_get;
            $a_route_parts['post'] = $this->a_post;
            $this->a_route_parts   = $a_route_parts;
        }
        else {
            $this->a_route_parts = [
                'route_id'     => 0,
                'route_path'   => $route_path,
                'route_class'  => 'MainController',
                'route_method' => '',
                'route_action' => '',
                'get'          => $this->a_get,
                'post'         => $this->a_post
            ];
        }
    }

    ### GETters and SETters ###
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
        return $this->route_path;
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
            $this->route_path = substr($request_uri, 0, strpos($request_uri, "?"));
        }
        else {
            $this->route_path = $request_uri;
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
