<?php
/**
 * Trait VisibilityTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use Exception;
use ReflectionClass;
use ReflectionProperty;

/**
 * A set of visibility methods to keep things protected and private.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-11-30 18:11:06
 * @note    This was a part of the Base abstract class. Spun it out so it could
 *          be used independently, only in classes where it is important.
 * @change_log
 * - 2.0.0 - updated to php 8                                   - 2021-11-30 wer
 * - 1.0.0 - initial version                                    - 08/19/2015 wer
 */
trait VisibilityTraits {
    /** @var string */
    protected string $current_page;
    /** @var array */
    protected array $private_properties = array();

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
        catch (Exception) {
            return;
        }
        $this->current_page = $o_class->getFileName();
        $class_properties = get_class_vars(__CLASS__);
        foreach ($class_properties as $property_name => $property_value) {
            try {
                $o_prop = new ReflectionProperty(__CLASS__, $property_name);
            }
            catch (Exception) {
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
    public function __set(string $var, string $val)
    {
        $a_backtrace = debug_backtrace();
        if ($this->private_properties === null || $this->private_properties === []) {
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
    public function __get(string $var)
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
    public function __isset(string $var)
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
    public function __unset(string $var)
    {
        $a_backtrace = debug_backtrace();
        if (isset($this->$var)) {
            if (!array_key_exists($var, $this->private_properties) || $a_backtrace[0]['file'] !== $this->current_page) {
                unset($this->$var);
            }
        }
    }
}
