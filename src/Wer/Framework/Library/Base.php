<?php
/**
 *  A Base Class that all other classes use.
 *  Primarily this is used to fix the visibility issue that PHP ignores
 *  @file Base.php
 *  @class Base
 *  @author William Reveal  <wer@wereveal.com>
 *  @version  1.0.3
 *  @date 2013-03-28 11:00:17
 *  @par ChangeLog
 *      v1.0.3 - namespace change, changed to an abstract class
 *  @par Wer Framework v4.0.0
 *  @ingroup wer_framework library
**/
namespace Wer\Framework\Library;

abstract class Base
{
    protected $current_page;
    protected $private_properties;
    protected $o_elog;

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
     *  @param str $var name of property being set
     *  @param str $val value of the property to be set
     *  @return NULL
    **/
    public function __set($var, $val)
    {
        $a_backtrace = debug_backtrace();
        if (is_null($this->private_properties)) {
            return;
        }
        if (($a_backtrace[0]['file'] != $this->current_page) && (array_key_exists($var, $this->private_properties))) {
            $this->o_elog->write("Cannot access {$this->private_properties[$var]} property of " . __CLASS__ . "::{$var} in {$a_backtrace[0]['file']} on line {$a_backtrace[0]['line']}", 4);
        } else {
            $this->$var = $val;
        }
    }
    /**
     *  Prevent direct access to protected and private properties.
     *  @param str $var name of property being get
     *  @return mixed - value of the property being get
    **/
    public function __get($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (($a_backtrace[0]['file'] != $this->current_page) && (array_key_exists($var, $this->private_properties))) {
                $this->o_elog->write("Cannot access {$this->private_properties[$var]} property " . __CLASS__ . "::{$var} in {$a_backtrace[0]['file']} on line {$a_backtrace[0]['line']}", 4);
                return;
            }
            return $this->$var;
        } else {
            $this->o_elog->write("Required property [{$var}] has not been set!", 4);
        }
    }
    /**
     *  Prevent direct access to protected and private properties.
     *  @param str $var name of property being evaluated
     *  @return bool
    **/
    public function __isset($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (($a_backtrace[0]['file'] != $this->current_page) && (array_key_exists($var, $this->private_properties))) {
                $this->o_elog->write("Cannot access {$this->private_properties[$var]} property " . __CLASS__ . "::{$var} in {$a_backtrace[0]['file']} on line {$a_backtrace[0]['line']}", 4);
                return false;
            }
            return true;
        } else {
            $this->o_elog->write("Required property [{$var}] has not been set!", 4);
            return false;
        }
    }
    /**
     *  Prevent direct access to protected and private properties.
     *  @param str $var name of property being unset
     *  @return nothing
    **/
    public function __unset($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (($a_backtrace[0]['file'] != $this->current_page) && (array_key_exists($var, $this->private_properties))) {
                $this->o_elog->write("Cannot access {$this->private_properties[$var]} property " . __CLASS__ . "::{$var} in {$a_backtrace[0]['file']} on line {$a_backtrace[0]['line']}", 4);
            } else {
                unset($this->$var);
            }
        } else {
            $this->o_elog->write("Required property [{$var}] has not been set!", 4);
        }
    }
}
