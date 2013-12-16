<?php
/**
 *  Something simple to help me debug my websites.
 *  A singleton pattern.
 *  @file Elog.php
 *  @namespace Ritc\Library\Core
 *  @class Elog
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version:  2.6.0
 *  @date 2013-07-30 11:09:45
 *  @par Change Log
 *      v2.6.0 - Namespace changes
 *      v2.5.2 - added some sanity code to setElogConstants to prevent errors - 04/23/2013
 *      v2.5.1 - renamed main method from do_it to write (not so silly)
 *      v2.5.0 - FIG standars (mostly)
 *  @par RITC Library v4.0.0
 *  @ingroup ritc_library library
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstracts\Base;

class Elog extends Base
{
    protected $current_page;
    private $debug_text;
    private $display_last_message = false;
    private $error_email_address = 'wer@qca.net';
    private $from_class = '';
    private $from_function = '';
    private $from_method = '';
    private $from_file = '';
    private $from_line = '';
    private static $instance;
    private $last_message = '';
    private $php_log_used = false;
    protected $private_properties = array();
    private $use_php_log = true;
    private $use_text_log = false;
    private function __construct()
    {
        $this->setPrivateProperties();
        $this->setElogConstants();
        $this->debug_text = "<!-- Start of Debug Text -->\n";
    }
    public function __destruct()
    {
        if ($this->php_log_used && $this->display_last_message && $this->use_php_log) {
            error_log("Last last_message:\n" . $this->last_message . "\n");
        }
        if ($this->php_log_used && $this->use_php_log) {
            error_log("==== End of Elog ====\n\n");
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
    public function getVar($var_name)
    {
        return $this->$var_name;
    }
    /**
     *  Getter for property debug_text.
     *  @return str - the value of $debug_text
    **/
    public function getText()
    {
        return $this->debug_text;
    }
    /**
     *  Getter for property last_message.
     *  @return str - the value of $last_message
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
        $results = defined('LOG_OFF')    ? true : define('LOG_OFF',    0);
        $results = defined('LOG_ON')     ? true : define('LOG_ON',     1);
        $results = defined('LOG_PHP')    ? true : define('LOG_PHP',    1);
        $results = defined('LOG_BOTH')   ? true : define('LOG_BOTH',   2);
        $results = defined('LOG_EMAIL')  ? true : define('LOG_EMAIL',  3);
        $results = defined('LOG_ALWAYS') ? true : define('LOG_ALWAYS', 4);
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
     *  Setter for the private property use_php_log.
     *  Purpose of use_php_log is to specify if logging
     *  can be done to the php log via do_it
     *  @param $boolean (bool) - defaults to false;
     *  @return NULL
    **/
    public function usePhpLog($boolean = false)
    {
        $this->use_php_log = $boolean;
    }
    /**
     *  Setter for private property use_text_log.
     *  debug_text purpose
     *  is to create a piece of text that is placed in the html, either as
     *  an HTML comment or visible textContent
     *  @param $boolean (bool) - defaults to false;
     *  @return NULL
    **/
    public function useTextLog($boolean = false)
    {
        $this->use_text_log = $boolean;
    }
    /**
     *  Logs a message somewhere.
     *  Provides several methods to log a message.
     *  @param $the_string (str) - the message to be logged
     *  @param $log_method (int) - the method to log message - Defaults to 1<pre>
     *      log_methods:
     *          0 = only set last_message - no other logging done
     *          1 = logs based on the two properties use_text_log and
     *              use_php_log
     *          2 = email the error and fall through to log_method 1/Default
     *          3 = email the error only
     *          4 = logs the message always
     *          default = log_method 1</pre>
     *  @param $extra (str) - additional information to be logged
     *  @return bool - success or failure of logging
    **/
    public function write($the_string = '', $log_method = 1, $manual_from = '')
    {
        if ($the_string == '') {
            return;
        }
        $this->last_message    = $the_string;
        if ($manual_from != '') {
            $from = ' (From: ' . $manual_from. ")\n";
        } elseif ($this->from_file . $this->from_method . $this->from_class . $this->from_function . $this->from_line != '') {
            $from = ' (From: '
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
                . ")\n";
        } else {
            $from = '';
        }
        $the_string    = $the_string . $from;
        switch ($log_method) {
            case 0:
                return true;
            case 4:
                if ($this->php_log_used === false) {
                    $the_string = "\n\n=== Start Elog ===\n\n" . $the_string;
                    $this->php_log_used    = true;
                }
                return error_log($the_string, 0);
            case 3:
                return error_log($the_string, 1, $error_email_address,
                                  "From: error_" . $error_email_address
                                  . "\r\nX-Mailer: PHP/" . phpversion());
            case 2:
                if (!error_log($the_string, 1, $error_email_address,
                                 "From: error_" . $error_email_address
                                 . "\r\nX-Mailer: PHP/" . phpversion())) {
                    return false;
                }
            case 1:
            default:
                if ($this->use_text_log) {
                    $this->debug_text .= $this->makeComment($the_string);
                }
                if ($this->use_php_log) {
                    if ($this->php_log_used === false) {
                        $the_string = "\n\n=== Start Elog ===\n\n" . $the_string;
                        $this->php_log_used    = true;
                    }
                    return error_log($the_string, 0);
                }
        }
        return false;
    }
    /**
     *  Formats a string for display in html.
     *  Can be either an html comment or a string that starts with COMMENT:
     *  @param $the_string (str) - the string to be formated
     *  @param $not_visible (bool) - should it formated as an HTML comment
     *  @return str - the formated string
    **/
    public function makeComment($the_string, $not_visible = true)
    {
        return $not_visible ? "<!-- {$the_string} -->\n"
                            : "COMMENT: {$the_string}<br />\n";
    }
}
