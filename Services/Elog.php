<?php
/**
 * @brief     Something simple to help me debug my websites.
 * @details   A singleton pattern because that is what I want. pfffttttt!
 * @ingroup   lib_services
 * @file      Elog.php
 * @namespace Ritc\Library\Services
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version:  3.0.2
 * @date      2016-02-26 12:16:48
 * @note <b>Change Log</b>
 * - v3.0.2 - bug fixes                                                       - 02/26/2016 wer
 * - v3.0.1 - clean up code                                                   - 02/22/2016 wer
 * - v3.0.0 - added new logging methods, changed default to custom log        - 11/19/2015 wer
 * - v2.7.1 - moved to Services namespace                                     - 11/15/2014 wer
 * - v2.7.0 - added method to ignore LOG_OFF settings to allow global logging - 11/11/2014 wer
 * - v2.6.2 - clean up, removed extend to Base class, not needed/wanted       - 09/23/2014 wer
 * - v2.6.1 - package change required minor update                            - 12/19/2013 wer
 * - v2.6.0 - Namespace changes                                               - 07/30/2013 wer
 * - v2.5.2 - added some sanity code to setElogConstants to prevent errors    - 04/23/2013 wer
 * - v2.5.1 - renamed main method from do_it to write (not so silly)
 * - v2.5.0 - FIG standars (mostly)
 */
namespace Ritc\Library\Services;

/**
 * Class Elog does some basic logging.
 * @class Elog
 * @package Ritc\Library\Services
 */
class Elog
{
    /** @var string */
    protected $current_page;
    /** @var bool */
    private $custom_log_used = false;
    /** @var string */
    private $debug_text;
    /** @var bool */
    private $display_last_message = false;
    /** @var string */
    private $elog_file = 'elog.log';
    /** @var string */
    private $error_email_address = 'wer@qca.net';
    /** @var bool */
    private $html_used = false;
    /** @var bool */
    private $ignore_log_off = false;
    /** @var string */
    private $from_class = '';
    /** @var string */
    private $from_function = '';
    /** @var string */
    private $from_location = '';
    /** @var string */
    private $from_method = '';
    /** @var string */
    private $from_file = '';
    /** @var string */
    private $from_line = '';
    /** @var bool */
    private $handler_set = false;
    /** @var Elog */
    private static $instance;
    /** @var string */
    private $json_file = 'json.log';
    /** @var bool */
    private $json_log_used = false;
    /** @var string */
    private $last_message = '';
    /** @var int */
    private $log_method;
    /** @var bool */
    private $php_log_used = false;

    /**
     * Elog constructor.
     */
    private function __construct()
    {
        $this->setElogConstants();
        $this->log_method = LOG_CUSTOM;
        $this->debug_text = "<!-- Start of Debug Text -->\n";
    }

    /**
     * Does some last minute stuff.
     */
    public function __destruct()
    {
        if ($this->php_log_used && $this->display_last_message) {
            error_log("Last last_message:\n" . $this->last_message . "\n");
        }
        if ($this->php_log_used) {
            error_log("\n==== End of Logging Session ====\n\n");
        }
        if ($this->custom_log_used) {
            trigger_error("==== End of Elog ====\n", E_USER_NOTICE);
        }
    }

    /**
     * Create/use the instance.
     * Elog is a singleton and uses the start method to create/use the instance.
     * @return object - the instance
     */
    public static function start()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    /**
     * Logs a message somewhere.
     * Provides several methods to log a message.
     *
     * @param string $the_string the message to be logged
     * @param int    $log_method the method to log message - Defaults to LOG_OFF
     * @param string $manual_from
     * @see setElogConstants() for possible values to $log_method
     *
     * @return bool - success or failure of logging
     */
    public function write($the_string = '', $log_method = LOG_OFF, $manual_from = '')
    {
        if ($the_string == '') {
            return true;
        }
        $this->last_message = $the_string;
        if ($manual_from != '') {
            $from = $log_method != LOG_JSON
                ? ' (From: ' . $manual_from. ")"
                : '';
        }
        elseif ($this->from_file . $this->from_method . $this->from_class . $this->from_function . $this->from_line != '') {
            $from = $log_method != LOG_JSON
                ? ' (From: '
                    . $this->from_file
                    . ($this->from_file != '' ? '  ' : '')
                    . $this->from_method
                    . ($this->from_method != '' ? '  ' : '')
                    . $this->from_class
                    . ($this->from_class != '' ? '  ' : '')
                    . $this->from_function
                    . ($this->from_function != '' ? '  ' : '')
                    . ($this->from_line != '' ? 'Line: ' : '')
                    . $this->from_line
                    . ")\n"
                : '';
        }
        else {
            $from = '';
        }
        $the_string = $the_string . $from;
        if ($this->ignore_log_off && $log_method == LOG_OFF) {
            $log_method = LOG_ON;
        }
        $this->log_method = $log_method;
        switch ($log_method) {
            case LOG_OFF:
                return true;
            case LOG_ALWAYS:
                if ($this->php_log_used === false) {
                    $the_string = "\n=== Start logging session "
                        . date('Y/m/d H:i:s')
                        . " ===\n\n"
                        . $the_string;
                    $this->php_log_used    = true;
                }
                return error_log($the_string, 0);
            case LOG_EMAIL:
                return error_log($the_string, 1, $this->error_email_address,
                                  "From: error_" . $this->error_email_address
                                  . "\r\nX-Mailer: PHP/" . phpversion());
            case LOG_JSON:
                $this->json_log_used = true;
                return trigger_error($the_string, E_USER_NOTICE);
            /** @noinspection PhpMissingBreakStatementInspection */
            case LOG_BOTH:
                error_log($the_string, 1, $this->error_email_address,
                                 "From: error_" . $this->error_email_address
                                 . "\r\nX-Mailer: PHP/" . phpversion());
            case LOG_ON:
            case LOG_CUSTOM:
                return trigger_error($the_string, E_USER_NOTICE);
            case LOG_HTML:
                $this->html_used = true;
                $this->debug_text .= $this->makeComment($the_string);
                return true;
            case LOG_DB: // not implemented at this time.
                return true;
            case LOG_PHP:
            default:
                if ($this->php_log_used === false) {
                    $the_string = "\n=== Start Logging Session "
                        . date('Y/m/d H:i:s')
                        . " ===\n\n"
                        . $the_string;
                    $this->php_log_used    = true;
                }
                return error_log($the_string, 0);
        }
    }

    /**
     * The function that is for custom logging with trigger_error.
     * @param $error_number
     * @param $error_string
     * @return bool|int|void
     */
    public function errorHandler($error_number, $error_string)
    {
        if (!(error_reporting() & $error_number)) { // Error code not valid
            return null;
        }
        switch ($this->log_method) {
            case LOG_ON:
            case LOG_CUSTOM:
                if ($this->custom_log_used === false) {
                    $string = "\n\n\n\n\n\n\n\n\n\n=== Start Elog ===\n" .
                        date("Y-m-d H:i:s") .
                        " - " .
                        $this->from_location .
                        "\n" .
                        $error_string .
                        "\n\n";
                    $this->custom_log_used = true;
                }
                elseif (strpos($error_string, 'End of Elog') !== false) {
                    $string = $error_string . "\n\n";
                }
                else {
                    $string = date("Y-m-d H:i:s") .
                        " - " .
                        $this->from_location .
                        "\n" .
                        $error_string .
                        "\n\n";
                }
		        return file_put_contents(LOG_PATH . '/' . $this->elog_file, $string, FILE_APPEND);
            case LOG_JSON:
                $this->json_log_used = true;
		        $error_string = str_replace("\n", '', $error_string);
		        $string = stripslashes(json_encode ([
                    'date'       => date("Y-m-d H:i:s"),
                    'location'   => $this->from_location,
		            'message'    => $error_string
		        ]));
		        $string .= "\n";
		        return file_put_contents(LOG_PATH . '/' . $this->json_file, $string, FILE_APPEND);
            default:
                return false;
        }
    }

    /**
     * Returns the exception handler - what???
     * @param $exception
     * @return null
     * @todo exceptionHandler - this makes no sense as it stands.
     */
    public function exceptionHandler($exception)
    {
        return $exception;
    }

    /**
     * Returns the private/protected property by name.
     * @param $var_name
     * @return string
     */
    public function getVar($var_name)
    {
        if (isset($this->$var_name)) {
            return $this->$var_name;
        }
        else {
            return '';
        }
    }

    /**
     * Getter for property debug_text.
     * @return string - the value of $debug_text
     */
    public function getText()
    {
        return $this->debug_text;
    }

    /**
     * Getter for property last_message.
     * @return string - the value of $last_message
     */
    public function getLastMessage()
    {
        return $this->last_message;
    }

    /**
     * Sets Constants for use whenever Elog is used.
     * @return null
     */
    private function setElogConstants()
    {
        if (!defined('LOG_OFF'))    { define('LOG_OFF',    0); }
        if (!defined('LOG_PHP'))    { define('LOG_PHP',    1); }
        if (!defined('LOG_BOTH'))   { define('LOG_BOTH',   2); }
        if (!defined('LOG_EMAIL'))  { define('LOG_EMAIL',  3); }
        if (!defined('LOG_ON'))     { define('LOG_ON',     4); }
        if (!defined('LOG_CUSTOM')) { define('LOG_CUSTOM', 4); }
        if (!defined('LOG_JSON'))   { define('LOG_JSON',   5); }
        if (!defined('LOG_DB'))     { define('LOG_DB',     6); }
        if (!defined('LOG_HTML'))   { define('LOG_HTML',   7); }
        if (!defined('LOG_ALWAYS')) { define('LOG_ALWAYS', 8); }
        if (!defined('LOG_PATH'))   { define('LOG_PATH', BASE_PATH . '/tmp'); }
    }

    /**
     * @param int $error_types
     */
    public function setErrorHandler($error_types = -2)
    {
        if ($error_types == -2) {
            $error_types = E_USER_WARNING | E_USER_NOTICE | E_USER_ERROR;
        }
        set_error_handler([self::$instance,'errorHandler'], $error_types);
        $this->handler_set = true;
    }

    /**
     * A combo setter for 5 properties.
     * Setter for properties from_class, from_function, from_method, from_file, from_line
     * @param string $file file name
     * @param string $method method name
     * @param string $line line number
     * @param string $class class name
     * @param string $function function name
     * @return NULL
     */
    public function setFrom($file = '', $method = '', $line = '', $class = '', $function = '')
    {
        $this->setFromFile($file);
        $this->setFromClass($class);
        $this->setFromMethod($method);
        $this->setFromFunction($function);
        $this->setFromLine($line);
    }

    /**
     * Sets the property from_class.
     * @param string $class
     */
    public function setFromClass($class = '')
    {
        $this->from_class = $class;
    }

    /**
     * Sets the property from_line.
     * @param string $line
     */
    public function setFromLine($line = '')
    {
        $this->from_line = $line;
    }

    /**
     * Sets the property from_location.
     * @param string $location
     */
    public function setFromLocation($location = '')
    {
        $this->from_location = $location;
    }

    /**
     * Sets the property from_function.
     * @param string $function
     */
    public function setFromFunction($function = '')
    {
        $this->from_function = $function;
    }

    /**
     * Sets the property from_method.
     * @param string $method
     */
    public function setFromMethod($method = '')
    {
        $this->from_method = $method;
    }

    /**
     * Sets the property from_file.
     * @param string $file
     */
    public function setFromFile($file = '')
    {
            $this->from_file = basename($file);
    }

    /**
     * Sets the property handler_set.
     * @param bool $value
     */
    public function setHandlerSet($value = true)
    {
        $this->handler_set = $value;
    }

    /**
     * Setter for the private property ignore_log_off.
     * Basically turns logging on globally.
     * @param bool $boolean
     * @return null
     */
    public function setIgnoreLogOff($boolean = false)
    {
        $this->ignore_log_off = $boolean;
    }

    /**
     * Sets the property log_method.
     * @param int $log_method
     */
    public function setLogMethod($log_method = LOG_CUSTOM)
    {
        $this->log_method = $log_method;
    }

    /**
     * Formats a string for display in html.
     * Can be either an html comment or a string that starts with COMMENT:.
     * @param string $the_string  the string to be formated
     * @param bool   $not_visible should it formated as an HTML comment
     * @return string the formated string
     */
    public function makeComment($the_string, $not_visible = true)
    {
        return $not_visible ? "<!-- {$the_string} -->\n"
                            : "COMMENT: {$the_string}<br />\n";
    }
}
