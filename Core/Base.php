<?php
/**
 *  @brief A Base Class that all other classes use.
 *  @details Primarily this is used to fix the visibility issue that PHP ignores
 *  @file Base.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Base
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version  1.0.4
 *  @date 2013-12-19 07:22:30
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.4 - changed back to a class. Couldn't figure out why
 *          it should be abstract. Made an interface for it just because. 12/19/2013
 *      v1.0.3 - namespace change, changed to an abstract class
 *  </pre>
**/
namespace Ritc\Library\Core;

class Base
{
    protected $current_page;
    protected $private_properties;

    ### Fixing Visibility
    /**
     *  Creates an array that lists the protected and private properties.
     *  Used by the magic methods __set, __get, __isset, __unset to keep those
     *  properties protected and private
     *      @return NULL
    **/
    protected function setPrivateProperties()
    {
        $o_class = new \ReflectionClass(__CLASS__);
        $this->current_page = $o_class->getFileName();
        $class_properties = get_class_vars(__CLASS__);
        foreach ($class_properties as $property_name => $property_value) {
            $o_prop = new \ReflectionProperty(__CLASS__, $property_name);
            if ($o_prop->isPrivate() || $o_prop->isProtected()) {
                $this->private_properties[$o_prop->getName()] = $o_prop->isPrivate() ? 'private' : 'protected';
            }
        }
    }
    /**
     *  Prevent direct access to protected and private properties.
     *  @param string $var name of property being set
     *  @param string $val value of the property to be set
     *  @return NULL
    **/
    public function __set($var, $val)
    {
        $a_backtrace = debug_backtrace();
        if (is_null($this->private_properties)) {
            return null;
        }
        if (($a_backtrace[0]['file'] != $this->current_page) && (array_key_exists($var, $this->private_properties))) {
            error_log(
                "Cannot access {$this->private_properties[$var]} property of "
                . __CLASS__
                . "::{$var} in {$a_backtrace[0]['file']} on line {$a_backtrace[0]['line']}"
            );
        }
        else {
            $this->$var = $val;
        }
        return null;
    }
    /**
     *  Prevent direct access to protected and private properties.
     *  @param string $var name of property being get
     *  @return mixed - value of the property being get
    **/
    public function __get($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (($a_backtrace[0]['file'] != $this->current_page) && (array_key_exists($var, $this->private_properties))) {
                error_log(
                    "Cannot access {$this->private_properties[$var]} property "
                    . __CLASS__
                    . "::{$var} in {$a_backtrace[0]['file']} on line {$a_backtrace[0]['line']}"
                );
                return '';
            }
            return $this->$var;
        }
        else {
            error_log("Required property [{$var}] has not been set!" . __METHOD__ . '.' . __LINE__);
            return '';
        }
    }
    /**
     *  Prevent direct access to protected and private properties.
     *  @param string $var name of property being evaluated
     *  @return bool
    **/
    public function __isset($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (($a_backtrace[0]['file'] != $this->current_page) && (array_key_exists($var, $this->private_properties))) {
                error_log(
                    "Cannot access {$this->private_properties[$var]} property "
                    . __CLASS__
                    . "::{$var} in {$a_backtrace[0]['file']} on line {$a_backtrace[0]['line']}"
                );
                return false;
            }
            return true;
        }
        else {
            // error_log("Required property [{$var}] has not been set!", 4);
            return false;
        }
    }
    /**
     *  Prevent direct access to protected and private properties.
     *  @param string $var name of property being unset
     *  @return null
    **/
    public function __unset($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (($a_backtrace[0]['file'] != $this->current_page) && (array_key_exists($var, $this->private_properties))) {
                error_log(
                    "Cannot access {$this->private_properties[$var]} property "
                    . __CLASS__
                    . "::{$var} in {$a_backtrace[0]['file']} on line {$a_backtrace[0]['line']}"
                );
            }
            else {
                unset($this->$var);
            }
        }
        else {
            error_log("Required property [{$var}] has not been set! (From __unset)");
        }
    }
}
