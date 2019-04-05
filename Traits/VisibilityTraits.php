<?php
/**
 * Trait VisibilityTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * A set of visibility methods to keep things protected and private.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2015-08-19 12:18:25
 * @note    This was a part of the Base abstract class. Spun it out so it could
 *          be used independently, only in classes where it is important.
 * @change_log
 * - v1.0.0 - initial version - 08/19/2015 wer
 */
trait VisibilityTraits {
    /** @var */
    protected $current_page;
    /** @var array */
    protected $private_properties = array();

    /**
     * Creates an array that lists the protected and private properties.
     * Used by the magic methods __set, __get, __isset, __unset to keep those
     * properties protected and private
     */
    protected function setPrivateProperties():void
    {
        try {
            $o_class = new ReflectionClass(__CLASS__);
        }
        catch (ReflectionException $e) {
            return;
        }
        $this->current_page = $o_class->getFileName();
        $class_properties = get_class_vars(__CLASS__);
        foreach ($class_properties as $property_name => $property_value) {
            try {
                $o_prop = new ReflectionProperty(__CLASS__, $property_name);
            }
            catch (ReflectionException $e) {
                return;
            }
            if ($o_prop->isPrivate() || $o_prop->isProtected()) {
                $this->private_properties[$o_prop->getName()] = $o_prop->isPrivate() ? 'private' : 'protected';
            }
        }
    }

    /**
     * Prevent direct access to protected and private properties.
     * $this->private_properties has to be set via setPrivateProperties()
     * for this to be effective.
     *
     * @param string $var name of property being set
     * @param string $val value of the property to be set
     */
    public function __set($var, $val)
    {
        $a_backtrace = debug_backtrace();
        if (null === $this->private_properties || $this->private_properties === []) {
            $this->$var = $val;
        }
        if (!array_key_exists($var, $this->private_properties)
            || $a_backtrace[0]['file'] !== $this->current_page
        ) {
            $this->$var = $val;
        }
    }

    /**
     * Prevent direct access to protected and private properties.
     * $this->private_properties has to be set via setPrivateProperties()
     * for this to be effective.
     *
     * @param string $var name of property being get
     * @return mixed - value of the property being get
     */
    public function __get($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (array_key_exists($var, $this->private_properties)
                && $a_backtrace[0]['file'] !== $this->current_page
            ) {
                return '';
            }
            return $this->$var;
        }
        return '';
    }

    /**
     * Prevent direct access to protected and private properties.
     * $this->private_properties has to be set via setPrivateProperties()
     * for this to be effective.
     *
     * @param string $var name of property being evaluated
     * @return bool
     */
    public function __isset($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            return $a_backtrace[0]['file'] === $this->current_page
                || !array_key_exists($var, $this->private_properties);
        }
        return false;
    }

    /**
     * Prevent direct access to protected and private properties.
     * $this->private_properties has to be set via setPrivateProperties()
     * for this to be effective.
     *
     * @param string $var name of property being unset
     */
    public function __unset($var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (!array_key_exists($var, $this->private_properties) || $a_backtrace[0]['file'] !== $this->current_page) {
                unset($this->$var);
            }
        }
    }
}
