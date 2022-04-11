<?php
/**
 * Class OopHelper.
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Utility methods for Object Oriented Programming.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-04-04 10:02:07
 * @change_log
 * - v1.0.0-alpha.0 - Initial version                           - 2018-04-04 wer
 */
class OopHelper
{
    /**
     * Determines if a namespace has been declared.
     * @param string $namespace
     * @return bool
     */
    public static function namespaceExists(string $namespace = ''):bool
    {
        if (empty($namespace)) {
            return false;
        }
        $a_classes = get_declared_classes();
        foreach ($a_classes as $class) {
            if (str_contains($class, $namespace)) {
                return true;
            }
        }
        return false;
    }
}
