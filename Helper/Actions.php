<?php
/**
 *  @brief Extracts the action to use for the page.
 *  @details Can get the action from the URL - htaccess required
 *  or from a form element with a few select id names
 *
 *  @file Actions.php
 *  @ingroup ritc_library helper
 *  @namespace Ritc/Library/Helper
 *  @class Actions
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 2.2.7
 *  @date 2015-01-27 15:15:06
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v2.2.7 - match refactoring of Strings and Arrays to     - 01/27/2015 wer
 *               static methods
 *      v2.2.6 - moved file into Helper area                    - 11/15/2014 wer
 *      v2.2.5 - changed to implment the changes in Base class  - 09/23/2014 wer
 *      v2.2.4 - changed to match namespace change              - 12/19/2013 wer
 *      v2.2.3 - Changed to namespace reorge
 *      v2.2.2 - changed to new namespace                       - 03/27/2013 wer
 *      v2.2.1 - added a bit more sanitation to uri actions,
 *               renamed action to form_action to be clearer what it was
 *      v2.2.0 - refactored to be closer to FIG standards
 * @TODO Need to decide if this needs to be repurposed with the event of the Router class
**/
namespace Ritc\Library\Helper;

use Ritc\Library\Abstracts\Base;

class Actions extends Base
{
    protected $a_clean_post;
    protected $a_clean_get;
    protected $a_uri_actions;
    protected $form_action;
    protected $uri_no_get;
    protected $url_path;
    public function __construct()
    {
        $this->logIt("Starting __construct", LOG_OFF, __FILE__);
        $this->setPrivateProperties();
        $this->setCleanPost($_POST);
        $this->setCleanGet($_GET);
        $this->setUriNoGet($_SERVER["REQUEST_URI"]);
        $this->setUriActions();
        $this->setFormAction(array_merge($this->a_clean_get, $this->a_clean_post));
        $this->setUrlPath($this->uri_no_get);
        $this->logIt("ending __construct", LOG_OFF, __FILE__);
    }
    public function getFormAction()
    {
        return $this->form_action;
    }
    public function getCleanPost($value = '')
    {
        if ($value == '') {
            return $this->a_clean_post;
        }
        else {
            $this->logIt("Value is: {$value}", LOG_OFF, __METHOD__ . '.' . __LINE__);
            if (isset($this->a_clean_post[$value])) {
                return $this->a_clean_post[$value];
            }
            else {
                $this->logIt("The Value Doesn't Exist. " . var_export($this->a_clean_post, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return false;
            }
        }
    }
    public function getCleanGet($value = '')
    {
        if ($value == '') {
            return $this->a_clean_get;
        } else {
            if (isset($this->a_clean_get[$value])) {
                return $this->a_clean_get[$value];
            } else {
                $this->logIt(var_export($this->a_clean_post, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
                return '';
            }
        }
    }
    public function getFilePath()
    {
        /*  returns the full path where a file is located **/
        $a_file_path = explode("/", __FILE__);
        $the_count = count($a_file_path);
        $the_output = "/";
        for ($i = 0; $i<$the_count-1; $i++) {
            if ($a_file_path[$i] != '') {
                $the_output .= $a_file_path[$i] . "/";
            }
        }
        return $the_output;
    }
    public function getImageSubmit($array = array())
    {
        if ((count($array) > 0) && (is_array($array))) {
            foreach ($array as $key=>$value) {
                $x = strlen($key)-2;
                if (substr($key, $x, 2) == "_x") {
                    return substr($key, 0, $x);
                }
            }
            return '';
        }
        else {
            return '';
        }
    }
    public function getUriActions()
    {
        return $this->a_uri_actions;
    }
    public function getUrlPath()
    {
        return $this->url_path;
    }
    public function setFormAction($a_clean_post)
    {     // used $a_clean_post as a not so subtle hint to use a safe array and not raw $_POST/$_GET/$_REQUEST etc
        $this->logIt("Starting with: " . var_export($a_clean_post, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $x_action = '';
        $y_action = '';
        foreach ($a_clean_post as $key=>$value) {
            if (substr($key, strlen($key) - 2) == "_x") {
                $x_action = substr($key, 0, strpos($key, "_x"));
            }
            elseif (substr($key, strlen($key) - 2) == "_y") {
                $y_action = substr($key, 0, strpos($key, "_y"));
            }
        }
        if (isset($a_clean_post["action"]) && ($a_clean_post["action"] != '')) {
            $action = Strings::makeInternetUsable($a_clean_post["action"]);
        }
        elseif (isset($a_clean_post["step"]) && ($a_clean_post["step"] != '')) {
            $action = Strings::makeInternetUsable($a_clean_post["step"]);
        }
        elseif (isset($a_clean_post["submit"]) && ($a_clean_post["submit"] != '')) {
            $action = Strings::makeInternetUsable($a_clean_post["submit"]);
        }
        elseif (($x_action != '') && ($x_action == $y_action)) {
            $action = Strings::makeInternetUsable($x_action);
        }
        else {
            $action = '';
        }
        $this->logIt("Action is: {$action}", LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->form_action = $action;
        return true;
    }
    public function setCleanPost(array $array = array(), array $a_allowed_keys = array())
    {
        if (count($array) == 0) {
            $this->a_clean_post = array();
            return true;
        }
        $this->a_clean_post = Arrays::cleanArrayValues($array, $a_allowed_keys);
        return true;
    }
    public function setCleanGet($array, $a_allowed_keys = array())
    {
        if ($array == array() || $array == '') {
            $this->a_clean_get = array();
            return true;
        }
        $this->a_clean_get = Arrays::cleanArrayValues($array, $a_allowed_keys);
        return true;
    }
    public function setUriNoGet($request_uri = '')
    {
        if ($request_uri == '') {
            $request_uri = $_SERVER["REQUEST_URI"];
        }
        $this->logIt("The pre-clean uri is: " . $request_uri, LOG_OFF, __METHOD__);
        if (strpos($request_uri, "?") !== false) {
            $this->uri_no_get = substr($request_uri, 0, strpos($request_uri, "?"));
        }
        else {
            $this->uri_no_get = $request_uri;
        }
        $this->logIt("The clean uri is: " . $this->uri_no_get, LOG_OFF, __METHOD__);
        return true;
    }
    public function setUriActions($root_dir = '')
    {
        /* $root_dir defines what isn't an action in the URI**/
        $log_from = __METHOD__ . '.' . __LINE__;
        $this->logIt("Root Dir is: " . $root_dir, LOG_OFF, $log_from);
        $this->logIt("Request URI is: " . $this->uri_no_get, LOG_OFF, $log_from);
        $a_actions   = array();
        $uri_actions = str_replace($root_dir, '', $this->uri_no_get);
        $uri_parts   = explode("/", $uri_actions);
        foreach ($uri_parts as $key=>$value) {
            if (($value=='') || (strpos($value, ".php") !== false)) {
                unset($uri_parts[$key]);
            }
        }
        $uri_parts = array_merge($uri_parts);
        $uri_count = count($uri_parts);
        if ($uri_count > 0) {
            $array_part = 0;
            for ($x=1; $x<=$uri_count; $x++) {
                $a_actions["action".$x] = $uri_parts[$array_part];
                $array_part++;
            }
        }
        else {
            $this->a_uri_actions = array();
            return false;
        }
        $log_from = __METHOD__ . '.' . __LINE__;
        $this->logIt("URI parts is: " . var_export($uri_parts, true), LOG_OFF, $log_from);
        $this->logIt("a_actions is: " . var_export($a_actions, true), LOG_OFF, $log_from);
        $this->a_uri_actions = $this->filterUriActions($a_actions);
        return true;
    }
    public function setUrlPath($uri = '')
    {
        $uri = ($uri == '') ? $this->uri_no_get : $uri;
        $a_file_path = explode("/", $uri);
        $the_count = count($a_file_path);
        $the_output = "/";
        for ($i=0; $i<$the_count-1; $i++) {
            if ($a_file_path[$i] == '')  {
                // skip
            }
            elseif (strpos($a_file_path[$i], ".php") !== false) {
                // skip
            }
            else {
                $the_output .= $a_file_path[$i] . "/";
            }
        }
        $this->url_path = $the_output;
        return true;
    }

    ### Utility ###
    private function filterUriActions($a_actions)
    {
        $new_array = array();
        foreach ($a_actions as $key => $value ) {
            $new_array[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        }
        return $new_array;
    }
}
