<?php
/**
 *  For managing sessions.
 *  @file Session.php
 *  @namespace Ritc\Library\Core
 *  @class Session
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.1.0
 *  @date 06/14/2011 15:13:24
 *  @ingroup ritc_library library
 *  @par RITC Library 4.0
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstract\Base;
use Ritc\Library\Core\Elog;

class Session extends Base
{
    protected $current_page;
    private static $instance;
    protected $o_elog;
    protected $private_properties;
    private $session_id;
    private $session_name;
    private $session_started = false;
    private function __construct($session_id, $session_name)
    {
        $this->setPrivateProperties();
        $this->o_elog = Elog::start();
        if ($session_id != '') {
            session_id($session_id);
        }
        if ($session_name != '') {
            session_name($session_name);
        } else {
            session_name('FCRSESSID');
        }
        $this->session_started = session_start();
        if ($this->session_started) {
            $this->o_elog->write("Session ID in construct: " . session_id(), LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->session_id   = session_id();
            $this->session_name = session_name();
            $this->o_elog->write("Session Name in construct: " . session_name(), LOG_OFF, __METHOD__ . '.' . __LINE__);
        } else {
            $this->o_elog->write("Session Not Started", LOG_OFF, __METHOD__ . '.' . __LINE__);
        }

    }
    public static function start($session_id = "", $session_name = "")
    {
        if (!isset(self::$instance)) {
            self::$instance = new Session($session_id, $session_name);
        }
        return self::$instance;
    }
    public function clear($a_not_these = '')
    {
        if (is_array($a_not_these) === false) {
            $a_not_these = array();
        }
        foreach ($_SESSION as $key=>$value) {
            if (in_array($key, $a_not_these) === false) {
                unset($_SESSION[$key]);
            }
        }
    }
    public function destroy()
    {
        $_SESSION = array();
        if (ini_get('session.use_cookies') == '1') {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
    public function getCookieParams()
    {
        return session_get_cookie_params();
    }
    public function isIdle()
    {
        if (isset($_SESSION["idle_time"])
             && $_SESSION["idle_time"] > 0
             && isset($_SESSION["idle_timestamp"])
             && ($_SESSION["idle_timestamp"] + $_SESSION["idle_time"]) <= time()) {
            return true;
        }
        return false;
    }
    public function isNotIdle()
    {
        return $this->isIdle() ? false : true;
    }
    public function updateIdleTimestamp()
    {
        $_SESSION["idle_timestamp"] = time();
    }
    public function id($id = "")
    {
        if ($id != "") {
            return session_id($id);
        }
        return session_id();
    }
    public function name($name = "")
    {
        if ($name != "") {
            return session_name($name);
        }
        return session_name();
    }
    public function regenerateId($delete_old = false)
    {
        return session_regenerate_id($delete_old);
    }
    public function getVar($var_name = "")
    {
        $this->o_elog->write("Var Name: " . $var_name, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->o_elog->write("Session Var Value: " . $_SESSION[$var_name], LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $var_name == "" ? NULL : isset($_SESSION[$var_name]) ? $_SESSION[$var_name] : "" ;
    }
    public function setVar($var_name = "", $var_value = "")
    {
        if ($var_name == "") { return false; }
        $_SESSION[$var_name] = $var_value;
        return true;
    }
    /**
     *  Sets $_SESSION vars specified in the array.
     *  Any number session vars can be set/created with this function.
     *  @pre If the $a_vars array is from a POST or GET it is assumed in this
     *      method that the values have been put through some sort of data
     *      cleaner. In other words, you should put a raw $_POST or $_GET
     *      through this.
     *  @param $a_var_names (array), variable names to be set
     *  @return VOID
    **/
    public function setVarsFromArray($a_vars = '', $a_allowed_keys = '')
    {
        if (is_array($a_vars) === false) {
            $a_vars = array('huh'=>'true');
        }
        foreach ($a_vars as $name=>$value) {
            if (is_array($a_allowed_keys) && count($a_allowed_keys) > 0) {
                if (in_array($name, $a_allowed_keys)) {
                    $_SESSION[$name] = $value;
                }
            } else {
                $_SESSION[$name] = $value;
            }
        }
    }
    public function getProperty($property_name)
    {
        return $this->$property_name;
    }
    public function setIdleTime($time = 1800, $add = false)
    {
        if ($add && isset($_SESSION["idle_time"])) {
            $_SESSION["idle_time"] += $time;
        } else {
            $_SESSION["idle_time"] = $time;
        }
        if (!isset($_SESSION["idle_timestamp"])) {
            $_SESSION["idle_timestamp"] = time();
        }
    }
    public function useCookies($use_cookies = "")
    {
        switch ($use_cookies) {
            case false:
                return ini_set("session.use.cookies", 0);
                break;
            case true:
            case "":
                if (ini_get('session.use_cookies') == '1') {
                    return true;
                }
            default:
                return ini_set("session.use.cookies", 1);
        }
        return false;
    }
    public function useTransSid($use_trans = false)
    {
        $old_use_trans = ini_get('session.use_trans_sid') ? true : false;
        if ($old_use_trans !== $use_trans) {
            return ini_set('session.use_trans_sid', $use_trans ? 1 : 0);
        }
        return true;
    }
    public function setToken()
    {
        $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    }
    /**
     *  Verifies the session is valid.
     *  This is a simple and probably easily spoofed validation but it seems
     *  to fool many XSS things.
     *  @pre session variables token and idle_timestamp
     *      as well as the session id matches cookie name
     *  @param $a_values (array), array(
     *      'token'=>_SESSION['token'],
     *      'form_ts'=>_SESSION['idle_timestamp'])
     *  @param bool $cookie_only specifies if to use cookie data only for validation
     *  @return bool, true or false if valid
    **/
    public function isValidSession($a_values = array(), $cookie_only = false)
    {
        if (  isset($_SESSION['token']) === false
            || isset($_SESSION['idle_timestamp']) === false
            || isset($_SESSION['idle_time']) === false
        ) {
            return false;
        }
        if ($cookie_only === false && $a_values == array()) {
            return false;
        } elseif ($cookie_only !== false) {
            if (($_SESSION["idle_timestamp"] + $_SESSION["idle_time"]) >= time()
                && $_COOKIE['FCRSESSID'] == session_id()) {
                return true;
            } else {
                return false;
            }
        } elseif (($_SESSION["idle_timestamp"] + $_SESSION["idle_time"]) >= time()
                && $a_values['token']    == $_SESSION['token']
                && $a_values['form_ts']  == $_SESSION['idle_timestamp']
                && $_COOKIE['FCRSESSID'] == session_id())
        {
            return true;
        } else {
            return false;
        }
    }
    /**
     *  tells you if it is not a valid session.
     *  returns the opposite of isValidSession method. See its comments for more info
     *  @param $a_values (array), array(
     *      'token'=>_SESSION['token'],
            'form_ts'=>_SESSION['idle_timestamp'])
     *  @param bool $cookie_only specifies if to use cookie data only for validation
     *  @return bool true or false
    **/
    public function isNotValidSession($a_values = array(), $cookie_only = false)
    {
        if ($this->isValidSession($a_values, $cookie_only)) {
            return false;
        }
        return true;
    }
    public function __clone()
    {
        trigger_error("Clone is not allowed.", E_USER_ERROR);
    }
}
