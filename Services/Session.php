<?php /** @noinspection AdditionOperationOnArraysInspection */
/** @noinspection NotOptimalIfConditionsInspection */
/**
 * Class Session
 * @package Ritc_Library
 */
namespace Ritc\Library\Services;

use Ritc\Library\Exceptions\ServiceException;

/**
 * For basic management of sessions.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-12-01 14:00:00
 * @change_log
 * - 2.0.0 - updated to php 8 standards                             - 2021-12-01 wer
 *           also fixed some potential bugs
 * - 1.6.0 - Needed to make sure a session isn't already started    - 2020-09-20 wer
 * - 1.5.0 - Changed to set the cookie parameters before the        - 2018-11-29 wer
 *            session is started when parameters are provided.
 * - 1.4.1 - bug fix                                                - 2017-07-06 wer
 * - 1.4.0 - added the ability to use the global constant           - 10/06/2015 wer
 *            SESSION_IDLE_TIME if it is set.
 * - 1.3.0 - removed abstract class Base, added LogitTraits         - 09/01/2015 wer
 * - 1.2.0 - added a couple new method to unset a session var       - 01/16/2015 wer
 *            and a shortcut to reset the session.
 * - 1.1.0 - Unknown Changes                                        - 06/14/2011 wer
 */
class Session
{
    /** @var Session */
    private static Session $instance;
    /** @var string */
    private string $session_id;
    /** @var string */
    private string $session_name;
    /** @var bool */
    private bool $session_started;

    /**
     * Session constructor.
     *
     * @param array $a_params
     * @throws ServiceException
     */
    private function __construct(array $a_params = [])
    {
        $session_status = session_status();
        if ($session_status === PHP_SESSION_NONE) {
            if (!empty($a_params['session_id'])) {
                session_id($a_params['session_id']);
            }
            if (!empty($a_params['session_name'])) {
                session_name($a_params['session_name']);
            }
            else {
                session_name('RITCSESSID');
            }
            if (!empty($a_params['cookie'])) {
                if (PHP_VERSION_ID >= 70300) {
                    session_set_cookie_params($a_params['cookie']);
                }
                else {
                    $lifetime = 0;
                    $path     = '/';
                    $domain   = '';
                    foreach ($a_params['cookie'] as $cookie_key => $cookie_value) {
                        $$cookie_key = $cookie_value;
                    }
                    session_set_cookie_params($lifetime, $path, $domain, false, false);
                }
            }
            $this->session_started = session_start();
        }
        elseif ($session_status === PHP_SESSION_DISABLED) {
            $this->session_started = false;
        }
        else {
            $this->session_started = true;
        }
        if ($this->session_started) {
            $this->session_id   = session_id();
            $this->session_name = session_name();
            if (empty($_SESSION['token'])) {
                $this->setToken();
            }
            if (empty($_SESSION['idle_timestamp'])) {
                $this->setIdleTime();
            }
        }
        else {
            throw new ServiceException('Unable to start the session.', 10);
        }
    }

    /**
     * Returns the instance of the Session class.
     *
     * @param array $a_params
     * @return Session
     * @throws ServiceException
     */
    public static function start(array $a_params = []):Session
    {
        if (empty(self::$instance)) {
            try {
                self::$instance = new Session($a_params);
            }
            catch (ServiceException $e) {
                throw new ServiceException($e->errorMessage(), $e->getCode(), $e->getPrevious());
            }
        }
        return self::$instance;
    }

    /**
     * Clears the session values.
     * Allows certain ones not to be cleared based on the array $a_not_these.
     *
     * @param array $a_not_these optional.
     */
    public function clear(array $a_not_these = array()):void
    {
        foreach ($_SESSION as $key=>$value) {
            if (in_array($key, $a_not_these, false) === false) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * Destroy's the Session.
     */
    public function destroy():void
    {
        $_SESSION = array();
        if (ini_get('session.use_cookies') === '1') {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                      $params['path'], $params['domain'],
                      $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /**
     * Returns the values from session_get_cookie_params().
     *
     * @return array the session's cookies values
     */
    public function getCookieParams():array
    {
        return session_get_cookie_params();
    }

    /**
     * Checks to see if the session has sat there unused for $_SESSION['idle_time'] amount of time.
     *
     * @return bool
     */
    public function isIdle():bool
    {
        return isset($_SESSION['idle_time'], $_SESSION['idle_timestamp'])
            && $_SESSION['idle_time'] > 0
            && ($_SESSION['idle_timestamp'] + $_SESSION['idle_time']) <= time();
    }

    /**
     * Returns if the Session has not been idle.
     *
     * @return bool
     */
    public function isNotIdle():bool
    {
        return !$this->isIdle();
    }

    /**
     * Verifies the session is valid.
     * This is a simple and probably easily spoofed validation but it seems
     * to fool many XSS things.
     *
     * @pre session variables token and idle_timestamp
     *      as well as the session id matches cookie name
     * @param array $a_values [
     *     'token'   => SESSION['token'],
     *     'tolken'  => SESSION['token'], // optional, if set and token isn't, token = tolken
     *     'form_ts' => SESSION['idle_timestamp'],
     *     'hobbit   => '' // optional anti-spam measure, if not blank, invalidate session
     * ]
     * @param bool  $use_form_values specifies if to use form data for validation
     * @return bool, true or false if valid
     */
    public function isValidSession(array $a_values, bool $use_form_values):bool
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
        if (isset($a_values['hobbit']) && $a_values['hobbit'] !== '') {
            return false;
        }
        if ($use_form_values === true && empty($a_values)) {
            return false;
        }

        $max_time = $_SESSION['idle_timestamp'] + $_SESSION['idle_time'];
        if ($use_form_values === false) {
            if (
                   ini_get('session.use_cookies') !== '1'
                && ini_get('session.use_only_cookies') !== '1'
                && $max_time >= time()
            ) {
                return true;
            }
            if (
                   $max_time >= time()
                && $_COOKIE[$this->session_name] === session_id()
            ) {
                return true;
            }
        }
        elseif (
               ini_get('session.use_cookies') !== '1'
            && ini_get('session.use_only_cookies') !== '1'
            && $max_time >= time()
            && $a_values['tolken']  === (string)$_SESSION['token']
            && $a_values['form_ts'] === (string)$_SESSION['idle_timestamp']
        ) {
                return true;
        }
        elseif (
               $max_time >= time()
            && $a_values['tolken']  === (string)$_SESSION['token']
            && $a_values['form_ts'] === (string)$_SESSION['idle_timestamp']
            && $_COOKIE[$this->session_name] === session_id()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Tells you if it is not a valid session.
     * returns the opposite of isValidSession method. See its comments for more info
     *
     * @param array $a_values        ['token' => $value, 'form_ts' => $value]
     * @param bool  $use_form_values specifies if to use form data for validation
     * @return bool true or false
     */
    public function isNotValidSession(array $a_values, bool $use_form_values):bool
    {
        if ($this->isValidSession($a_values, $use_form_values)) {
            return false;
        }
        return true;
    }

    /**
     * Updates the Idle timestamp.
     */
    public function updateIdleTimestamp():void
    {
        $_SESSION['idle_timestamp'] = time();
    }

    /**
     * Returns the Session ID.
     *
     * @param string $id
     * @return string
     */
    public function id(string $id = ''):string
    {
        if ($id !== '') {
            return session_id($id);
        }
        return session_id();
    }

    /**
     * Returns the session name.
     *
     * @param string $name
     * @return string
     */
    public function name(string $name = ''):string
    {
        if ($name !== '') {
            return session_name($name);
        }
        return session_name();
    }

    /**
     * Regenerates the session id.
     *
     * @param bool $delete_old
     * @return bool
     */
    public function regenerateId(bool $delete_old):bool
    {
        return session_regenerate_id($delete_old);
    }

    /**
     * Resets the session to default values.
     */
    public function resetSession():void
    {
        $this->clear();
        $this->setSessionVars();
    }

    /**
     * Returns the Session variable value for the session variable name.
     *
     * @param string $var_name
     * @return mixed whatever the value is of the session variable.
     */
    public function getVar(string $var_name = ''): mixed
    {
        return $var_name === ''
            ? ''
            : $_SESSION[$var_name] ?? '';
    }

    /**
     * Sets a session variable.
     *
     * @param string $var_name
     * @param string $var_value
     * @return bool
     */
    public function setVar(string $var_name = '', string $var_value = ''):bool
    {
        if ($var_name === '') { return false; }
        $_SESSION[$var_name] = $var_value;
        return true;
    }

    /**
     * Sets basic session vars for form validation.
     *
     * @param array $a_values valid array()
     */
    public function setSessionVars(array $a_values = array()):void
    {
        if (count($a_values) === 0) {
            $this->setToken();
            $this->setIdleTime();
        }
        else {
            $token   = '';
            $form_ts = '';
            if (isset($a_values['tolken']) && $a_values['tolken'] !== '') {
                $token = $a_values['tolken'];
            }
            if (isset($a_values['form_ts']) && $a_values['form_ts'] !== '') {
                $form_ts = $a_values['form_ts'];
            }
            if (!isset($_SESSION['token']) || $_SESSION['token'] !== $token) {
                $this->setToken();
            }
            if (!isset($_SESSION['idle_timestamp']) || $_SESSION['idle_timestamp'] !== $form_ts) {
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
     * Sets $_SESSION vars specified in the array.
     * Any number session vars can be set/created with this function.
     *
     * @pre If the $a_vars array is from a POST or GET it is assumed in this
     *      method that the values have been put through some sort of data
     *      cleaner. In other words, you should not put a raw $_POST or $_GET
     *      through this.
     * @param array $a_vars
     * @param array $a_allowed_keys
     */
    public function setVarsFromArray(array $a_vars = array(), array $a_allowed_keys = array()):void
    {
        foreach ($a_vars as $name=>$value) {
            if (count($a_allowed_keys) > 0) {
                if (in_array($name, $a_allowed_keys, false)) {
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
     *
     * @param int  $time
     * @param bool $add
     */
    public function setIdleTime(int $time = -1, bool $add = false):void
    {
        if ($time === -1) {
            if (defined('SESSION_IDLE_TIME')) {
                $time = SESSION_IDLE_TIME;
            }
            else {
                $time = 1800;
            }
        }
        if ($add && isset($_SESSION['idle_time'])) {
            $_SESSION['idle_time'] += $time;
        }
        else {
            $_SESSION['idle_time'] = $time;
        }
        if (!isset($_SESSION['idle_timestamp'])) {
            $_SESSION['idle_timestamp'] = time();
        }
    }

    /**
     * Unsets a session var.
     *
     * @param string $var_name name of the var to unset
     */
    public function unsetVar(string $var_name = ''):void
    {
        if ($var_name !== '' && isset($_SESSION[$var_name])) {
            unset($_SESSION[$var_name]);
        }
    }

    /**
     * Sets the php ini session.use_cookies variable.
     *
     * @param bool $use_cookies
     * @return bool
     */
    public function useCookies(bool $use_cookies = true): bool
    {
        return match ($use_cookies) {
            false   => ini_set('session.use_cookies', 0) && ini_set('session.use_only_cookies', 0),
            default => ini_set('session.use_cookies', 1) && ini_set('session.use_only_cookies', 1),
        };
    }

    /**
     * Sets the php ini session.user_trans_sid variable.
     *
     * @param bool $use_trans
     * @return bool|string
     */
    public function useTransSid(bool $use_trans): bool|string
    {
        $old_use_trans = (bool)ini_get('session.use_trans_sid');
        if ($old_use_trans !== $use_trans) {
            return ini_set('session.use_trans_sid', $use_trans ? 1 : 0);
        }
        return true;
    }

    /**
     * GETtter for class property session_id.
     *
     * @return string
     */
    public function getSessionId():string
    {
        return $this->session_id;
    }

    /**
     * SETtter for class property session_id.
     *
     * @param string $session_id
     */
    public function setSessionId(string $session_id):void
    {
        $this->session_id = $session_id;
    }

    /**
     * GETter for class property session_name.
     *
     * @return string
     */
    public function getSessionName():string
    {
        return $this->session_name;
    }

    /**
     * SETter for class propery session_name.
     *
     * @param string $session_name
     */
    public function setSessionName(string $session_name):void
    {
        $this->session_name = $session_name;
    }

    /**
     * GETter for class property session_started.
     *
     * @return bool
     */
    public function isSessionStarted():bool
    {
        return $this->session_started;
    }

    /**
     * SETter for class property session_started.
     *
     * @param bool $session_started
     */
    public function setSessionStarted(bool $session_started):void
    {
        $this->session_started = $session_started;
    }

    /**
     * Sets the session variable 'token' which is used in a bunch of places.
     */
    public function setToken():void
    {
        $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    }

    /**
     * Gets the token.
     *
     * @return mixed
     */
    public function getToken(): mixed
    {
        return $_SESSION['token'];
    }

    /**
     * Cloning is not allowed.
     *
     * @throws ServiceException
     */
    public function __clone()
    {
        throw new ServiceException('Clone is not allowed for this service.', 20);
    }
}
