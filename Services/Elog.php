<?php /** @noinspection ForgottenDebugOutputInspection */
/**
 * Class Elog
 * @package Ritc_Library
 */
namespace Ritc\Library\Services;

use Error;
use Ritc\Library\Exceptions\ServiceException;

/**
 * Something simple to help me debug my websites.
 * A singleton pattern because that is what I want. pfffttttt!
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.2.0
 * @date    2017-07-16 07:57:42
 * @change_log
 * - v3.2.0 - Added additional LOG_x types, rearranged some code              - 2017-07-16 wer
 * - v3.1.0 - Added LOG_WARN and LOG_ERROR                                    - 2017-05-12 wer
 * - v3.0.0 - added new logging methods, changed default to custom log        - 11/19/2015 wer
 * - v2.8.0 - moved to Services namespace                                     - 11/15/2014 wer
 * - v2.7.0 - added method to ignore LOG_OFF settings to allow global logging - 11/11/2014 wer
 * - v2.6.0 - Namespace changes                                               - 07/30/2013 wer
 * - v2.5.0 - renamed main method from do_it to write (not so silly)
 * - v2.4.0 - FIG standards (mostly)
 */
class Elog
{
    /** @var string the current page */
    protected $current_page;
    /** @var bool has logging started */
    private $custom_log_used = false;
    /** @var string test of a debug */
    private $debug_text;
    /** @var bool should the last message be displayed */
    private $display_last_message = false;
    /** @var string the name of the file to be logged to */
    private $elog_file = 'elog.log';
    /** @var string where to send an email log message */
    private $error_email_address = 'wer@qca.net';
    /** @var bool if html is used */
    private $html_used = false;
    /** @var bool if true, all log messages will be done */
    private $ignore_log_off = false;
    /** @var string from which class the error message came */
    private $from_class = '';
    /** @var string from which function the error message came */
    private $from_function = '';
    /** @var string from the location the message came */
    private $from_location = '';
    /** @var string from which method the message came */
    private $from_method = '';
    /** @var string from which file the message came */
    private $from_file = '';
    /** @var string from which line the message came */
    private $from_line = '';
    /** @var bool is the handler set */
    private $handler_set = false;
    /** @var Elog the elog object instance */
    private static $instance;
    /** @var string the name of the json file to log to */
    private $json_file = 'json.log';
    /** @var bool has the json log been used */
    private $json_log_used = false;
    /** @var string what the last message was */
    private $last_message = '';
    /** @var int the method to use to log the messages */
    private $log_method;
    /** @var bool has the php log been used */
    private $php_log_used = false;

    /**
     * Elog constructor.
     */
    private function __construct()
    {
        $this->setElogConstants();
        $this->log_method = LOG_ON;
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
            $string = "==== End of Elog ====\n";
            file_put_contents(LOG_PATH . '/' . $this->elog_file, $string, FILE_APPEND);
        }
    }

    /**
     * Create/use the instance.
     * Elog is a singleton and uses the start method to create/use the instance.
     *
     * @return Elog - the instance
     * @throws ServiceException
     */
    public static function start():Elog
    {
        if (self::$instance === null) {
            $c = __CLASS__;
            try {
                self::$instance = new $c;
            }
            catch (Error $e) {
                throw new ServiceException('Unable to start the service.', 10, $e);
            }
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
    public function write($the_string = '', $log_method = LOG_OFF, $manual_from = ''):?bool
    {
        if ($this->ignore_log_off && $log_method === LOG_OFF) {
            $log_method = LOG_ON;
        }
        if ($the_string === '' || $log_method === LOG_OFF) {
            return true;
        }
        $this->log_method = $log_method;
        if ($manual_from !== '') {
            $this->from_location = $manual_from;
            $from = $log_method !== LOG_JSON
                ? ' (From: ' . $manual_from. ')'
                : '';
        }
        elseif ($this->from_file . $this->from_method . $this->from_class . $this->from_function . $this->from_line !== '') {
            $from = $this->from_file
                  . ($this->from_file !== '' ? '  ' : '')
                  . $this->from_method
                  . ($this->from_method !== '' ? '  ' : '')
                  . $this->from_class
                  . ($this->from_class !== '' ? '  ' : '')
                  . $this->from_function
                  . ($this->from_function !== '' ? '  ' : '')
                  . ($this->from_line !== '' ? 'Line: ' : '')
                  . $this->from_line;
            $this->from_location = $from;
            if ($log_method !== LOG_JSON) {
                $from = ' (From: ' . $from . ")\n";
            }
        }
        else {
            $from = '';
            $this->from_location = '';
        }
        $the_string         .= $from;
        $this->last_message = $the_string;
        switch ($log_method) {
            case LOG_OFF:
                return true;
            case LOG_EMAIL:
                return error_log($the_string, 1, $this->error_email_address,
                                 'From: error_' . $this->error_email_address
                                  . "\r\nX-Mailer: PHP/" . PHP_VERSION
                );
            /** @noinspection PhpMissingBreakStatementInspection */
            case LOG_BOTH:
                error_log($the_string, 1, $this->error_email_address,
                          'From: error_' . $this->error_email_address
                    . "\r\nX-Mailer: PHP/" . PHP_VERSION
                );
            case LOG_JSON:
                return trigger_error($the_string, E_USER_NOTICE);
            case LOG_ON:
            case LOG_CUSTOM:
                return trigger_error($the_string, E_USER_NOTICE);
            case LOG_WARN:
                return trigger_error($the_string, E_USER_WARNING);
            case LOG_ALWAYS:
            case LOG_ERROR:
                return trigger_error($the_string, E_USER_ERROR);
            case LOG_DEPRECATED:
                return trigger_error($the_string, E_USER_DEPRECATED);
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
                    $this->php_log_used = true;
                }
                return error_log($the_string, 0);
        }
    }

    /**
     * The function that is for custom logging with trigger_error and fallback to error_log().
     *
     * @param int    $error_number required
     * @param string $error_string required
     * @return bool|int
     */
    public function errorHandler($error_number, $error_string)
    {
        if (!(error_reporting() & $error_number)) { // Error code not valid
            return false;
        }
        if (empty($error_string)) {
            return error_log('Unspecified error - ' . $this->from_location, 0);
        }
        switch ($this->log_method) {
            case LOG_DB:
            case LOG_HTML:
            case LOG_OFF:
                return true;
            case LOG_ALWAYS:
            case LOG_ERROR:
                return $this->writeToFile($error_string, 'User Error');
            case LOG_CUSTOM:
                return $this->writeToFile($error_string, 'User Custom');
            case LOG_DEPRECATED:
                return $this->writeToFile($error_string, 'User Deprecated');
            case LOG_NOTICE:
                return $this->writeToFile($error_string, 'User Notice');
            case LOG_ON:
                return $this->writeToFile($error_string, 'User Info');
            case LOG_WARN:
                return $this->writeToFile($error_string, 'User Warn');
            case LOG_JSON:
                $this->json_log_used = true;
		        $error_string = str_replace("\n", '', $error_string);
		        $string = stripslashes(json_encode ([
                                                        'date'       => date('Y-m-d H:i:s'),
                                                        'location'   => $this->from_location,
                                                        'message'    => $error_string
		        ]));
		        $string .= "\n";
		        return file_put_contents(LOG_PATH . '/' . $this->json_file, $string, FILE_APPEND);
            default:
                $error_string = $this->from_location . ' - ' . $error_string;
                if ($this->php_log_used === false) {
                    $error_string = "\n=== Start Logging Session "
                        . date('Y/m/d H:i:s')
                        . " ===\n\n"
                        . $error_string;
                    $this->php_log_used = true;
                }
                return error_log($error_string, 0);
        }
    }

    /**
     * Returns the private/protected property by name.
     *
     * @param $var_name
     * @return string
     */
    public function getVar($var_name):string
    {
        return $this->$var_name ?? '';
    }

    /**
     * Getter for property debug_text.
     *
     * @return string - the value of $debug_text
     */
    public function getText():string
    {
        return $this->debug_text;
    }

    /**
     * Getter for property last_message.
     *
     * @return string - the value of $last_message
     */
    public function getLastMessage():string
    {
        return $this->last_message;
    }

    /**
     * Sets Constants for use whenever Elog is used.
     */
    private function setElogConstants():void
    {
        if (!defined('LOG_OFF'))        {
            define('LOG_OFF', 0); }
        if (!defined('LOG_PHP'))        {
            define('LOG_PHP', 1); }
        if (!defined('LOG_BOTH'))       {
            define('LOG_BOTH', 2); }
        if (!defined('LOG_EMAIL'))      {
            define('LOG_EMAIL', 3); }
        if (!defined('LOG_ON'))         {
            define('LOG_ON', 4); }
        if (!defined('LOG_CUSTOM'))     {
            define('LOG_CUSTOM', 4); }
        if (!defined('LOG_JSON'))       {
            define('LOG_JSON', 5); }
        if (!defined('LOG_DB'))         {
            define('LOG_DB', 6); }
        if (!defined('LOG_HTML'))       {
            define('LOG_HTML', 7); }
        if (!defined('LOG_ALWAYS'))     {
            define('LOG_ALWAYS', 8); }
        if (!defined('LOG_WARN'))       {
            define('LOG_WARN', 9); }
        if (!defined('LOG_ERROR'))      {
            define('LOG_ERROR', 10); }
        if (!defined('LOG_NOTICE'))     {
            define('LOG_NOTICE', 11); }
        if (!defined('LOG_DEPRECATED')) {
            define('LOG_DEPRECATED', 12); }
        if (!defined('LOG_PATH'))       {
            define('LOG_PATH', BASE_PATH . '/logs'); }
    }

    /**
     * This sets the error_handler to custom.
     * @param int $error_types optional, defaults to user-generated errors.
     */
    public function setErrorHandler($error_types = -2):void
    {
        if ($error_types === -2) {
            $error_types = E_USER_WARNING | E_USER_NOTICE | E_USER_ERROR;
        }
        set_error_handler([self::$instance,'errorHandler'], $error_types);
        $this->handler_set = true;

    }

    /**
     * A combo setter for 5 properties.
     * Setter for properties from_class, from_function, from_method, from_file, from_line
     *
     * @param string $file file name
     * @param string $method method name
     * @param string $line line number
     * @param string $class class name
     * @param string $function function name
     */
    public function setFrom($file = '', $method = '', $line = '', $class = '', $function = ''):void
    {
        $this->setFromFile($file);
        $this->setFromClass($class);
        $this->setFromMethod($method);
        $this->setFromFunction($function);
        $this->setFromLine($line);
    }

    /**
     * Sets the property from_class.
     *
     * @param string $class
     */
    public function setFromClass($class = ''):void
    {
        $this->from_class = $class;
    }

    /**
     * Sets the property from_line.
     *
     * @param string $line
     */
    public function setFromLine($line = ''):void
    {
        $this->from_line = $line;
    }

    /**
     * Sets the property from_location.
     *
     * @param string $location
     */
    public function setFromLocation($location = ''):void
    {
        $this->from_location = $location;
    }

    /**
     * Sets the property from_function.
     *
     * @param string $function
     */
    public function setFromFunction($function = ''):void
    {
        $this->from_function = $function;
    }

    /**
     * Sets the property from_method.
     *
     * @param string $method
     */
    public function setFromMethod($method = ''):void
    {
        $this->from_method = $method;
    }

    /**
     * Sets the property from_file.
     *
     * @param string $file
     */
    public function setFromFile($file = ''):void
    {
            $this->from_file = basename($file);
    }

    /**
     * Sets the property handler_set.
     *
     * @param bool $value
     */
    public function setHandlerSet($value = true):void
    {
        $this->handler_set = $value;
    }

    /**
     * Setter for the private property ignore_log_off.
     * Basically turns logging on globally.
     *
     * @param bool $boolean
     */
    public function setIgnoreLogOff($boolean = false):void
    {
        $this->ignore_log_off = $boolean;
    }

    /**
     * Sets the property log_method.
     *
     * @param int $log_method
     */
    public function setLogMethod($log_method = LOG_CUSTOM):void
    {
        $this->log_method = $log_method;
    }

    /**
     * Formats a string for display in html.
     * Can be either an html comment or a string that starts with COMMENT:.
     *
     * @param string $the_string  the string to be formated
     * @param bool   $not_visible should it formated as an HTML comment
     * @return string the formated string
     */
    public function makeComment($the_string, $not_visible = true):string
    {
        return $not_visible ? "<!-- {$the_string} -->\n"
                            : "COMMENT: {$the_string}<br />\n";
    }

    /**
     * Writes the error_message to the elog file.
     *
     * @param string $error_string required to log anything.
     * @param string $error_type   optional, defaults to
     * @return bool|int
     */
    private function writeToFile($error_string = '', $error_type = 'Unspecified')
    {
        if (empty($error_string)) {
            $error_string = 'Unspecified Error';
        }
        if ($this->custom_log_used === false) {
            $string = "\n\n\n\n\n\n\n\n\n\n=== Start Elog ===\n" .
                date('Y-m-d H:i:s') . ' - ' .
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
            $string = date('Y-m-d H:i:s') . ' - ' .
                $error_type . ' - ' .
                $this->from_location . "\n" .
                $error_string . "\n\n";
        }
        return file_put_contents(LOG_PATH . '/' . $this->elog_file, $string, FILE_APPEND);
    }
}
