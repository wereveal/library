<?php
/**
 *  @brief Something simple to help me debug my websites.
 *  @details A singleton pattern because that is what I want. pfffttttt!
 *  @file Elog.php
 *  @ingroup ritc_library services
 *  @namespace Ritc/Library/Services
 *  @class Elog
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version:  3.0.0
 *  @date 2015-11-19 22:4522:45
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v3.0.0 - added new logging methods, changed default to custom log        - 11/19/2015 wer
 *      v2.7.1 - moved to Services namespace                                     - 11/15/2014 wer
 *      v2.7.0 - added method to ignore LOG_OFF settings to allow global logging - 11/11/2014 wer
 *      v2.6.2 - clean up, removed extend to Base class, not needed/wanted       - 09/23/2014 wer
 *      v2.6.1 - package change required minor update                            - 12/19/2013 wer
 *      v2.6.0 - Namespace changes                                               - 07/30/2013 wer
 *      v2.5.2 - added some sanity code to setElogConstants to prevent errors    - 04/23/2013 wer
 *      v2.5.1 - renamed main method from do_it to write (not so silly)
 *      v2.5.0 - FIG standars (mostly)
 *  </pre>
**/
namespace Ritc\Library\Services;

class Elog
{
    protected $current_page;
    private $custom_log_used = false;
    private $debug_text;
    private $display_last_message = false;
    private $elog_file = 'elog.log';
    private $error_email_address = 'wer@qca.net';
    private $html_used = false;
    private $ignore_log_off = false;
    private $from_class = '';
    private $from_function = '';
    private $from_method = '';
    private $from_file = '';
    private $from_line = '';
    private static $instance;
    private $json_file = 'json.log';
    private $json_log_used = false;
    private $last_message = '';
    private $php_log_used = false;

    private function __construct()
    {
        $this->setElogConstants();
        $this->debug_text = "<!-- Start of Debug Text -->\n";
    }
    public function __destruct()
    {
        if ($this->php_log_used && $this->display_last_message) {
            error_log("Last last_message:\n" . $this->last_message . "\n");
        }
        if ($this->php_log_used) {
            error_log("\n==== End of Elog ====\n\n");
        }
        if ($this->custom_log_used) {
            error_log("\n==== End of Elog ====\n\n", 3, BASE_PATH . '/tmp/' . $this->elog_file);
        }
    }
    /**
     *  Create/use the instance.
     *  Elog is a singleton and uses the start method to create/use the instance.
     *  @return object - the instance
    **/
    public static function start()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }
    public function errorHandler($error_number, $error_string, $error_file, $error_line, $error_context)
    {
        if (!(error_reporting() & $error_number)) { // Error code not valid
            return;
        }
        switch ($error_number) {
            case E_ALL:
                $error_type = 'E_ALL';
                break;
            case E_ERROR:
                $error_type = 'E_ERROR';
                break;
            case E_NOTICE:
                $error_type = 'E_NOTICE';
                break;
            case E_RECOVERABLE_ERROR:
                $error_type = 'E_RECOVERABLE_ERROR';
                break;
            case E_STRICT:
                $error_type = 'E_STRICT';
                break;
            case E_USER_ERROR:
                $error_type = 'E_USER_ERROR';
                break;
            case E_USER_NOTICE:
                $error_type = 'E_USER_NOTICE';
                break;
            case E_USER_WARNING:
                $error_type = 'E_USER_WARNING';
                break;
            case E_WARNING:
                $error_type = 'E_WARNING';
                break;
            default:
                $error_type = 'Unknown';
        }
        $a_context = $this->fixContext($error_context);
        $error_string = str_replace("\n", '', $error_string);
        $string = stripslashes(json_encode ([
            'error_type' => $error_type,
            'message'    => $error_string,
            'file'       => $error_file,
            'line'       => $error_line,
            'variables'  => stripslashes(json_encode($a_context))
        ]));
        $string = str_replace('"variables":"{', ':variables":{', $string); 
        $string = str_replace('}"}', '}}', $string);
        $string .= "\n";
        return file_put_contents(BASE_PATH . '/tmp/' . $this->json_file, $string, FILE_APPEND);
    }
    public function exceptionHandler($exception)
    {
        return;
    }
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
     *  Getter for property debug_text.
     *  @return string - the value of $debug_text
    **/
    public function getText()
    {
        return $this->debug_text;
    }
    /**
     *  Getter for property last_message.
     *  @return string - the value of $last_message
    **/
    public function getLastMessage()
    {
        return $this->last_message;
    }
    /**
     *  Sets Constants for use whenever Elog is used.
     *  @return null
    **/
    private function setElogConstants()
    {
        if (!defined('LOG_OFF'))    { define('LOG_OFF',    0); }
        if (!defined('LOG_PHP'))    { define('LOG_PHP',    1); }
        if (!defined('LOG_BOTH'))   { define('LOG_BOTH',   2); }
        if (!defined('LOG_EMAIL'))  { define('LOG_EMAIL',  3); }
        if (!defined('LOG_ALWAYS')) { define('LOG_ALWAYS', 4); }
        if (!defined('LOG_ON'))     { define('LOG_ON',     5); }
        if (!defined('LOG_CUSTOM')) { define('LOG_CUSTOM', 5); }
        if (!defined('LOG_DB'))     { define('LOG_DB',     6); }
        if (!defined('LOG_HTML'))   { define('LOG_HTML',   7); }
        if (!defined('LOG_JSON'))   { define('LOG_JSON',   8); }
    }
    /**
     *  A combo setter for 5 properties.
     *  Setter for properties from_class, from_function, from_method, from_file, from_line
     *  @param string $file file name
     *  @param string $method method name
     *  @param string $line line number
     *  @param string $class class name
     *  @param string $function function name
     *  @return NULL
    **/
    public function setFrom($file = '', $method = '', $line = '', $class = '', $function = '')
    {
        $this->setFromFile($file);
        $this->setFromClass($class);
        $this->setFromMethod($method);
        $this->setFromFunction($function);
        $this->setFromLine($line);
    }
    public function setFromClass($class = '')
    {
        $this->from_class = $class;
    }
    public function setFromLine($line = '')
    {
        $this->from_line = $line;
    }
    public function setFromFunction($function = '')
    {
        $this->from_function = $function;
    }
    public function setFromMethod($method = '')
    {
        $this->from_method = $method;
    }
    public function setFromFile($file = '')
    {
            $this->from_file = basename($file);
    }
    /**
     *  Setter for the private property ignore_log_off.
     *  Basically turns logging on globally.
     *  @param bool $boolean
     *  @return null
     */
    public function setIgnoreLogOff($boolean = false)
    {
        $this->ignore_log_off = $boolean;
    }
    /**
     *  Setter for the private property use_custom_log.
     *  use_custom_log specifies logging done to a custom log file.
     *  @param bool   $boolean  defaults to true.
     *  @param string $elog_file name of log file.
     *  @return null
    **/
    public function useCustomLog($boolean = true, $elog_file = 'elog.log')
    {
        $this->use_custom_log = $boolean;
        $this->elog_file = $elog_file;
    }
    /**
     *  Setter for the private property use_db_log.
     *  use_db_log specifies logging done to a database table.
     *  @param bool $boolean defaults to true;
     *  @return NULL
    **/
    public function useDbLog($boolean = true)
    {
        $this->use_custom_log = $boolean;
    }
    /**
     *  Setter for the private property use_php_log.
     *  use_php_log specifies logging done to the php log file.
     *  @param bool $boolean defaults to true;
     *  @return NULL
    **/
    public function usePhpLog($boolean = true)
    {
        $this->use_php_log = $boolean;
    }
    /**
     *  Setter for private property use_text_log.
     *  debug_text creates a piece of text that is placed in the html, either as
     *  an HTML comment or visible textContent.
     *  @param bool $boolean defaults to true;
     *  @return NULL
    **/
    public function useTextLog($boolean = true)
    {
        $this->use_text_log = $boolean;
    }
    /**
     * Logs a message somewhere.
     * Provides several methods to log a message.
     *
     * @param string $the_string the message to be logged
     * @param int    $log_method the method to log message - Defaults to 1<pre>
     *                           log_methods:
     *                           0 = only set last_message - no other logging done
     *                           1 = logs based on the two properties use_text_log and
     *                           use_php_log
     *                           2 = email the error and fall through to log_method 1/Default
     *                           3 = email the error only
     *                           4 = logs the message always
     *                           default = log_method 1</pre>
     * @param string $manual_from
     *
     * @return bool - success or failure of logging
     */
    public function write($the_string = '', $log_method = 1, $manual_from = '')
    {
        if ($the_string == '') {
            return true;
        }
        $this->last_message = $the_string;
        if ($manual_from != '') {
            $from = $log_method != LOG_JSON
                ? ' (From: ' . $manual_from. ")\n"
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
        switch ($log_method) {
            case LOG_OFF:
                return true;
            case LOG_ALWAYS:
                if ($this->php_log_used === false) {
                    $the_string = "\n=== Start Elog " . date('Y/m/d H:i:s') . " ===\n\n" . $the_string;
                    $this->php_log_used    = true;
                }
                return error_log($the_string, 0);
            case LOG_EMAIL:
                return error_log($the_string, 1, $this->error_email_address,
                                  "From: error_" . $this->error_email_address
                                  . "\r\nX-Mailer: PHP/" . phpversion());
            case LOG_JSON: 
                return trigger_error($the_string, E_USER_NOTICE);
            /** @noinspection PhpMissingBreakStatementInspection */
            case LOG_BOTH:
                error_log($the_string, 1, $this->error_email_address,
                                 "From: error_" . $this->error_email_address
                                 . "\r\nX-Mailer: PHP/" . phpversion());
            case LOG_ON:
            case LOG_CUSTOM:
                if ($this->custom_log_used === false) {
                    $the_string = "\n=== Start Elog " . date('Y/m/d H:i:s') . " ===\n\n" . $the_string;
                    $this->custom_log_used    = true;
                }
                return error_log($the_string . "\n", 3, BASE_PATH . '/tmp/' . $this->elog_file);
            case LOG_HTML:
                $this->debug_text .= $this->makeComment($the_string);
                return true;
            case LOG_DB: // not implemented at this time.
                return true;
            case LOG_PHP:
            default:
                if ($this->php_log_used === false) {
                    $the_string = "\n=== Start Elog " . date('Y/m/d H:i:s') . " ===\n\n" . $the_string;
                    $this->php_log_used    = true;
                }
                return error_log($the_string, 0);
        }
        return false;
    }
    /**
     *  Formats a string for display in html.
     *  Can be either an html comment or a string that starts with COMMENT:.
     *  @param string $the_string  the string to be formated
     *  @param bool   $not_visible should it formated as an HTML comment
     *  @return string the formated string
    **/
    public function makeComment($the_string, $not_visible = true)
    {
        return $not_visible ? "<!-- {$the_string} -->\n"
                            : "COMMENT: {$the_string}<br />\n";
    }
    private function fixContext(array $values = array())
    {
        foreach ($values as $key => $value) {
            $values[$key] = stripslashes($value);
        }
        return $values;
    }
}
