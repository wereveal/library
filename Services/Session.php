<?php
/**
 *  @brief     For managing sessions.
 *  @ingroup   ritc_library lib_services
 *  @file      Session.php
 *  @namespace Ritc\Library\Services
 *  @class     Session
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.3.1
 *  @date      2015-10-06 14:42:18
 *  @note <pre><b>Change Log</b>
 *      v1.3.1 - added the ability to use the global constant          - 10/06/2015 wer
 *               SESSION_IDLE_TIME if it is set.
 *      v1.3.0 - removed abstract class Base, added LogitTraits        - 09/01/2015 wer
 *      v1.2.0 - added a couple new method to unset a session var      - 01/16/2015 wer
 *               and a shortcut to reset the session.
 *      v1.1.5 - added phpDoc comments                                 - 01/13/2015 wer
 *      v1.1.4 - changed session validation defaults                   - 01/06/2015 wer
 *      v1.1.3 - moved to Services namespace                           - 11/15/2014 wer
 *      v1.1.2 - changed to implement the changes in Base class        - 09/23/2014 wer
 *      v1.1.1 - Bug fixes                                             - 12/31/2013 wer
 *      v1.1.0 - Unknown Changes                                       - 06/14/2011 wer
 *  </pre>
**/
namespace Ritc\Library\Services;

use Ritc\Library\Traits\LogitTraits;

class Session
{
    use LogitTraits;

    /** @var Session  */
    private static $instance;
    /** @var string  */
    private $session_id;
    /** @var string  */
    private $session_name;
    /** @var bool  */
    private $session_started = false;

    /**
     * Session constructor.
     * @param string $session_id
     * @param string $session_name
     */
    private function __construct($session_id = '', $session_name = '')
    {
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

    /**
     * Returns the instance of the Session class.
     * @param string $session_id
     * @param string $session_name
     * @return Session
     */
    public static function start($session_id = "", $session_name = "")
    {
        if (!isset(self::$instance)) {
            self::$instance = new Session($session_id, $session_name);
        }
        return self::$instance;
    }
    /**
     * Clears the session values.
     * Allows certain ones not to be cleared based on the array $a_not_these.
     * @param array $a_not_these optional.
     */
    public function clear(array $a_not_these = array())
    {
        foreach ($_SESSION as $key=>$value) {
            if (in_array($key, $a_not_these) === false) {
                unset($_SESSION[$key]);
            }
        }
    }
    /**
     * Destroy's the Session.
     */
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
    /**
     * Returns the values from session_get_cookie_params().
     * @return array the session's cookies values
     */
    public function getCookieParams()
    {
        return session_get_cookie_params();
    }
    /**
     * Checks to see if the session has sat there unused for $_SESSION['idle_time'] amount of time.
     * @return bool
     */
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
    /**
     * Returns if the Session has not been idle.
     * @return bool
     */
    public function isNotIdle()
    {
        return $this->isIdle() ? false : true;
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
    public function isValidSession($a_values = array(), $use_form_values = false)
    {
        if (isset($_SESSION['token']) === false
            || isset($_SESSION['idle_timestamp']) === false
            || isset($_SESSION['idle_time']) === false)
        {
            return false;
        }
        if (isset($a_values['token']) && !isset($a_values['tolken'])) {
            $a_values['tolken'] = $a_values['token'];
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
                && $a_values['tolken']  == $_SESSION['token']
                && $a_values['form_ts'] == $_SESSION['idle_timestamp']
            ) {
                return true;
            }
        }
        elseif (
            ($_SESSION["idle_timestamp"] + $_SESSION["idle_time"]) >= time()
            && $a_values['tolken']  == $_SESSION['token']
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
    public function isNotValidSession($a_values = array(), $use_form_values = false)
    {
        if ($this->isValidSession($a_values, $use_form_values)) {
            return false;
        }
        return true;
    }
    /**
     *  Updates the Idle timestamp.
     */
    public function updateIdleTimestamp()
    {
        $_SESSION["idle_timestamp"] = time();
    }
    /**
     * Returns the Session ID.
     * @param string $id
     * @return string
     */
    public function id($id = "")
    {
        if ($id != "") {
            return session_id($id);
        }
        return session_id();
    }
    /**
     * Returns the session name.
     * @param string $name
     * @return string
     */
    public function name($name = "")
    {
        if ($name != "") {
            return session_name($name);
        }
        return session_name();
    }
    /**
     * Regenerates the session id.
     * @param bool $delete_old
     * @return bool
     */
    public function regenerateId($delete_old = false)
    {
        return session_regenerate_id($delete_old);
    }
    /**
     *  Resets the session to default values.
     */
    public function resetSession()
    {
        $this->clear();
        $this->setSessionVars();
    }
    /**
     * Returns the Session variable value for the session variable name.
     * @param string $var_name
     * @return mixed whatever the value is of the session variable.
     */
    public function getVar($var_name = "")
    {
        return $var_name == ""
            ? ''
            : isset($_SESSION[$var_name])
                ? $_SESSION[$var_name]
                : '';
    }
    /**
     * Sets a session variable.
     * @param string $var_name
     * @param string $var_value
     * @return bool
     */
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
     * Sets the amount of time for a page to be allowed idle.
     * Can either be passed in or defaults to the constant SESSION_IDLE_TIME
     * if set or 1800 if not.
     * @param int $time
     * @param bool $add
     */
    public function setIdleTime($time = -1, $add = false)
    {
        if ($time == -1) {
            if (defined('SESSION_IDLE_TIME')) {
                $time = SESSION_IDLE_TIME;
            }
            else {
                $time = 1800;
            }
        }
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
     *  Unsets a session var.
     *  @param string $var_name name of the var to unset
     */
    public function unsetVar($var_name = '')
    {
        if ($var_name != '') {
            if (isset($_SESSION[$var_name])) {
                unset($_SESSION[$var_name]);
            }
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
    /**
     * Gets the token.
     * @return mixed
     */
    public function getToken()
    {
        return $_SESSION['token'];
    }
    /**
     * Cloning is not allowed.
     */
    public function __clone()
    {
        trigger_error("Clone is not allowed.", E_USER_ERROR);
    }
}
