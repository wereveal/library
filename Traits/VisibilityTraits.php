<?php
/**
 * @brief     A set of visibility methods to keep things protected and private.
 * @details   Sometimes you want to force a class to create getter and setter
 *            methods instead of using the magic methods. Note that
 *            setPrivateProperties() has to be run first or the magic methods
 *            will work as normal.
 * @ingroup   lib_traits
 * @file      VisibilityTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2015-08-19 12:18:25
 * @note      This was a part of the Base abstract class. Spun it out so it could
 *            be used independently, only in classes where it is important.
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.0 - initial version - 08/19/2015 wer
 */
namespace Ritc\Library\Traits;

/**
 * Class Trait VisibilityTraits
 * @class   VisibilityTraits
 * @package Ritc\Library\Traits
 */
trait VisibilityTraits {
    protected $current_page;
    protected $private_properties = array();

    /**
     * Creates an array that lists the protected and private properties.
     * Used by the magic methods __set, __get, __isset, __unset to keep those
     * properties protected and private
     */
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
     * Prevent direct access to protected and private properties.
     * $this->private_properties has to be set via setPrivateProperties()
     * for this to be effective.
     * @param string $var name of property being set
     * @param string $val value of the property to be set
     * @return NULL
     */
    public function __set($var, $val)
    {
        $a_backtrace = debug_backtrace();
        if (is_null($this->private_properties) || $this->private_properties == array()) {
            $this->$var = $val;
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
     * Prevent direct access to protected and private properties.
     * $this->private_properties has to be set via setPrivateProperties()
     * for this to be effective.
     * @param string $var name of property being get
     * @return mixed - value of the property being get
     */
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
     * Prevent direct access to protected and private properties.
     * $this->private_properties has to be set via setPrivateProperties()
     * for this to be effective.
     * @param string $var name of property being evaluated
     * @return bool
     */
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
            error_log("Required property [{$var}] has not been set!", 4);
            return false;
        }
    }

    /**
     * Prevent direct access to protected and private properties.
     * $this->private_properties has to be set via setPrivateProperties()
     * for this to be effective.
     * @param string $var name of property being unset
     */
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
