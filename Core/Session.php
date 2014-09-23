<?php
/**
 *  @brief For managing sessions.
 *  @file Session.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Session
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.1.2
 *  @date 2014-09-23 12:02:53
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.1.2 - changed to implment the changes in Base class - 09/23/2014 wer
 *      v1.1.1 - Bug fixes 12/31/2013 wer
 *      v1.1.0 - 06/14/2011 wer
 *  </pre>
**/
namespace Ritc\Library\Core;

class Session extends Base
{
    protected $current_page;
    private static $instance;
    protected $o_elog;
    protected $private_properties;
    private $session_id;
    private $session_name;
    private $session_started = false;
    private function __construct($session_id = '', $session_name = '')
    {
        $this->setPrivateProperties();
        if ($session_id != '') {
            session_id($session_id);
        }
        if ($session_name != '') {
            session_name($session_name);
        }
        else {
            session_name('RITCSESSID');
        }
        $this->session_started = session_start();
        if ($this->session_started) {
            $this->logIt("Session ID in construct: " . session_id(), LOG_OFF, __METHOD__ . '.' . __LINE__);
            $this->session_id   = session_id();
            $this->session_name = session_name();
            $this->logIt("Session Name in construct: " . session_name(), LOG_OFF, __METHOD__ . '.' . __LINE__);
        }
        else {
            $this->logIt("Session Not Started", LOG_OFF, __METHOD__ . '.' . __LINE__);
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
        $this->logIt("Var Name: " . $var_name, LOG_OFF, __METHOD__ . '.' . __LINE__);
        $this->logIt("Session Var Value: " . $_SESSION[$var_name], LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $var_name == "" ? NULL : isset($_SESSION[$var_name]) ? $_SESSION[$var_name] : "" ;
    }
    public function setVar($var_name = "", $var_value = "")
    {
        if ($var_name == "") { return false; }
        $_SESSION[$var_name] = $var_value;
        return true;
    }

    /**
     *  Sets basic session vars for form validation.
     *
     *  @param array $a_values valid array()
     *  @return bool
    **/
    public function setSessionVars(array $a_values = array())
    {
        if (count($a_values) === 0) {
            $this->setToken();
            $this->setIdleTime();
        }
        else {
            $token   = '';
            $form_ts = '';
            if (isset($a_values['tolken']) && $a_values['tolken'] != '') {
                $token = $a_values['tolken'];
            }
            if (isset($a_values['form_ts']) && $a_values['form_ts'] != '') {
                $form_ts = $a_values['form_ts'];
            }
            if (!isset($_SESSION['token']) || $_SESSION['token'] != $token) {
                $this->setToken();
            }
            if (!isset($_SESSION['idle_timestamp']) || $_SESSION['idle_timestamp'] != $form_ts) {
                if (!isset($_SESSION['idle_time'])) {
                    $this->setIdleTime();
                }
                else {
                    $this->updateIdleTimestamp();
                }
            }
        }
    }

    /**
     *  Sets $_SESSION vars specified in the array.
     *  Any number session vars can be set/created with this function.
     *
     *  @pre     If the $a_vars array is from a POST or GET it is assumed in this
     *           method that the values have been put through some sort of data
     *           cleaner. In other words, you should not put a raw $_POST or $_GET
     *           through this.
     *
     *  @param array $a_vars
     *  @param array $a_allowed_keys
     *
     *  @return void
    **/
    public function setVarsFromArray(array $a_vars = array(), array $a_allowed_keys = array())
    {
        foreach ($a_vars as $name=>$value) {
            if (count($a_allowed_keys) > 0) {
                if (in_array($name, $a_allowed_keys)) {
                    $_SESSION[$name] = $value;
                }
            }
            else {
                $_SESSION[$name] = $value;
            }
        }
    }

    /**
     * @param int $time
     * @param bool $add
     */
    public function setIdleTime($time = 1800, $add = false)
    {
        if ($add && isset($_SESSION["idle_time"])) {
            $_SESSION["idle_time"] += $time;
        }
        else {
            $_SESSION["idle_time"] = $time;
        }
        if (!isset($_SESSION["idle_timestamp"])) {
            $_SESSION["idle_timestamp"] = time();
        }
    }

    /**
     * @param bool $use_cookies
     * @return bool|string
     */
    public function useCookies($use_cookies = true)
    {
        switch ($use_cookies) {
            case false:
                return ini_set("session.use_cookies", 0) && ini_set('session.use_only_cookies', 0);
            case true:
            default:
                return ini_set('session.use_cookies', 1) && ini_set("session.use_only_cookies", 1);
        }
    }

    /**
     * @param bool $use_trans
     * @return bool|string
     */
    public function useTransSid($use_trans = false)
    {
        $old_use_trans = ini_get('session.use_trans_sid') ? true : false;
        if ($old_use_trans !== $use_trans) {
            return ini_set('session.use_trans_sid', $use_trans ? 1 : 0);
        }
        return true;
    }

    /**
     *  Sets the session variable 'token' which is used in a bunch of places.
     */
    public function setToken()
    {
        $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    }
    public function getToken()
    {
        return $_SESSION['token'];
    }
    /**
     *  Verifies the session is valid.
     *  This is a simple and probably easily spoofed validation but it seems
     *  to fool many XSS things.
     *  @pre session variables token and idle_timestamp
     *      as well as the session id matches cookie name
     *  @param array $a_values  array(
     *      'token'   => SESSION['token'],
     *      'tolken'  => SESSION['token'], // optional, if set and token isn't, token = tolken
     *      'form_ts' => SESSION['idle_timestamp'],
     *      'hobbit   => '' // optional anti-spam measure, if not blank, invalidate session
     *      )
     *  @param bool $use_form_values specifies if to use form data for validation
     *  @return bool, true or false if valid
    **/
    public function isValidSession($a_values = array(), $use_form_values = true)
    {
        if (isset($_SESSION['token']) === false
            || isset($_SESSION['idle_timestamp']) === false
            || isset($_SESSION['idle_time']) === false)
        {
            return false;
        }
        if (isset($a_values['tolken']) && !isset($a_values['token'])) {
            $a_values['token'] = $a_values['tolken'];
        }
        if (isset($a_values['hobbit']) && $a_values['hobbit'] != '') {
            return false;
        }
        if ($use_form_values === true && $a_values == array()) {
            return false;
        }
        elseif ($use_form_values === false) {
            if (
                (ini_get('session.use_cookies') == 0)
                && (ini_get('session.use_only_cookies') == 0)
                && ($_SESSION["idle_timestamp"] + $_SESSION["idle_time"]) >= time()
            ) {
                return true;
            }
            elseif (
                ($_SESSION["idle_timestamp"] + $_SESSION["idle_time"]) >= time()
                && $_COOKIE[$this->session_name] == session_id()
            ) {
                return true;
            }
        }
        elseif ((ini_get('session.use_cookies') == 0) && (ini_get('session.use_only_cookies') == 0)) {
            if (
                ($_SESSION["idle_timestamp"] + $_SESSION["idle_time"]) >= time()
                && $a_values['token']   == $_SESSION['token']
                && $a_values['form_ts'] == $_SESSION['idle_timestamp']
            ) {
                return true;
            }
        }
        elseif (
            ($_SESSION["idle_timestamp"] + $_SESSION["idle_time"]) >= time()
            && $a_values['token']   == $_SESSION['token']
            && $a_values['form_ts'] == $_SESSION['idle_timestamp']
            && $_COOKIE[$this->session_name] == session_id()
        ) {
            return true;
        }
        return false;
    }
    /**
     *  Tells you if it is not a valid session.
     *  returns the opposite of isValidSession method. See its comments for more info
     *  @param $a_values (array), array(
     *      'token'=>_SESSION['token'],
            'form_ts'=>_SESSION['idle_timestamp'])
     *  @param bool $use_form_values specifies if to use form data for validation
     *  @return bool true or false
    **/
    public function isNotValidSession($a_values = array(), $use_form_values = true)
    {
        if ($this->isValidSession($a_values, $use_form_values)) {
            return false;
        }
        return true;
    }
    public function __clone()
    {
        trigger_error("Clone is not allowed.", E_USER_ERROR);
    }
}
