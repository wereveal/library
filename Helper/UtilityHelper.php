<?php
/**
 * @brief     Utility methods that don’t belong elsewhere.
 * @details   A tool box of disparate methods.
 * @ingroup   lib_helpers
 * @file      Ritc/Library/Helper/UtilityHelper.php
 * @namespace Ritc\Library\Helper
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2017-05-15 11:46:59
 * @note Change Log
 * - v1.0.0 - Initial version        - 2017-05-15 wer
 */
namespace Ritc\Library\Helper;

/**
 * Class UtilityHelper.
 * @class   UtilityHelper
 * @package Ritc\Library\Helper
 */
class UtilityHelper
{
    /**
     * Determines if a namespace has been declared.
     * @param string $namespace
     * @return bool
     */
    public static function namespaceExists($namespace = '') {
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