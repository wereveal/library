<?php
/**
 *  @brief Determines the controller and method to use based on URI.
 *  @file Router.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Router
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0ß
 *  @date 2014-09-25 18:12:44
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0ß - initial attempt to make this - 09/25/2014 wer
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Models\RouterModel;

class Router extends Base
{
    protected $o_elog;
    protected $private_properties;
    private $a_args;
    private $o_db;
    private $route_path;

    public function __construct(DbModel $o_dbm)
    {
        $this->setPrivateProperties();
        $this->setRoutePath();
        $this->setArgs();
        $this->o_db = new RouterModel($o_dbm);
    }

    /**
     *  Returns the action parts from the database.
     *  @param string $route_path
     *  @return array
     */
    public function action($route_path = '')
    {
        if (isset($this->o_elog) && is_object($this->o_elog)) { $this->o_db->setElog($this->o_elog); }
        if ($route_path == '') {
            $route_path = $this->route_path;
        }
        $a_values = ['route_path' => $route_path];
        $a_results = $this->o_db->read($a_values);
        $this->logIt("Actions from DB: " . var_export($a_results, true), LOG_OFF, __METHOD__);
        if ($a_results !== false && count($a_results) === 1) {
            $a_return_this = $a_results[0];
            $a_return_this['args'] = $this->a_args;
            return $a_return_this;
        }
        else {
            return [
                'route_class'  => 'MainController',
                'route_method' => '',
                'route_action' => '',
                'args'         => array()
            ];
        }
    }

    ### GETters and SETters ###
    public function getArgs()
    {
        return $this->a_args;
    }
    public function getRoutePath()
    {
        return $this->route_path;
    }
    /**
     *  Sets the property $a_args.
     *  By entering a string without a '?' it will set the property to an
     *      empty array.
     *  @param string $request_uri optional, defaults to $_SERVER['REQUEST_URI']
     *  @return null
     */
    public function setArgs($request_uri = '')
    {
        if ($request_uri == '') {
            $request_uri = $_SERVER["REQUEST_URI"];
        }
        if (strpos($request_uri, "?") !== false) {
            $args = substr($request_uri, strpos($request_uri, "?"));
            $a_arg_pairs = explode("&", $args);
            $a_final_args = array();
            foreach ($a_arg_pairs as $arg_pair) {
                $a_final_args[] = explode("=", $arg_pair);
            }
            $this->args = $a_final_args;
        }
        else {
            $this->args = array();
        }
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
            $this->uri = substr($request_uri, 0, strpos($request_uri, "?"));
        }
        else {
            $this->route_path = $request_uri;
        }
    }

    /* From Base Abstract
        protected function getElog
        protected function logIt
        protected function setElog
    */
}
