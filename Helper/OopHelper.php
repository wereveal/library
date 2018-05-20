<?php
/**
 *      
 * @ingroup   lib_helpers
 * @file      Ritc/Library/Helper/OopHelper.php
 * @namespace Ritc\Library\Helper
 */

namespace Ritc\Library\Helper;

/**
 * Class OopHelper - Utility methods for Object Oriented Programming.
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2018-04-04 10:02:07
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial version                           - 2018-04-04 wer
 */
class OopHelper
{
    /**
     * Determines if a namespace has been declared.
     * @param string $namespace
     * @return bool
     */
    public static function namespaceExists(string $namespace = '') {
        if (empty($namespace)) {
            return false;
        }
        $a_classes = get_declared_classes();
        foreach ($a_classes as $class) {
            if (strpos($class, $namespace) !== false) {
                return true;
            }
        }
        return false;
    }
}
