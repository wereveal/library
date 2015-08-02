<?php
/**
 *  @brief Class that does stuff with arrays.
 *  @file Arrays.php
 *  @ingroup ritc_library helper
 *  @namespace Ritc/Library/Helper
 *  @class Arrays
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 2.1.0
 *  @date 2015-07-31 16:14:43
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v2.1.0 - After looking at the inconsistency, changed to be more consistent   - 07/31/2015 wer
 *               Also changed variable name to be more descriptive than array.
 *      v2.0.1 - oops, missed one to be static, changed its name                     - 07/31/2015 wer
 *      v2.0.0 - changed methods to be static                                        - 01/27/2015 wer
 *      v1.3.0 - added stripUnsafePhp method and modified cleanArrayValues to use it - 12/05/2014 wer
 *      v1.2.1 - moved to the Helper namespace                                       - 11/15/2014 wer
 *      v1.2.1 - clean up                                                            - 09/23/2014 wer
 *      v1.2.0 - new method added                                                    - 12/30/2013 wer
 *      v1.1.1 - match package change                                                - 12/19/2013 wer
 *      v1.1.0 - namespace changes                                                   - 07/30/2013 wer
 *      v1.0.3 - moved array methods from class Strings to here                      - 03/27/2013 wer
 *      v1.0.2 - added new method
 *      v1.0.1 - new namespace, FIG standards (mostly)
 *  </pre>
**/
namespace Ritc\Library\Helper;

use Ritc\Library\Abstracts\Base;

class Arrays extends Base
{
    /**
     *  Modifies array values with htmlentities and strips unsafe php commands.
     *  Initially designed to do some basic filtering of $_POST, $_GET, etc
     *  but will work with any array.
     *  @param array $a_pairs               the array to clean
     *  @param array $a_allowed_keys      allows only specified keys to be returned
     *  @param bool  $unsafe_php_commands defaults to true strips 'unsafe' php commands, rare should it be false
     *  @return array                     the cleaned array
     */
    public static function cleanArrayValues(array $a_pairs = array(), $a_allowed_keys = array(), $unsafe_php_commands = true)
    {
        $a_clean = array();
        if (count($a_pairs) === 0) {
            return $a_clean;
        }
        if ($unsafe_php_commands === true) {
            $a_pairs = self::stripUnsafePhp($a_pairs);
        }
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_clean[$key] = self::cleanArrayValues($value);
            }
            else {
                $value = trim($value);
                if (count($a_allowed_keys) >= 1) {
                    if (in_array($key, $a_allowed_keys)) {
                        $a_clean[$key] = htmlentities($value, ENT_QUOTES);
                    }
                }
                else {
                    $a_clean[$key] = htmlentities($value, ENT_QUOTES);
                }
            }
        }
        return $a_clean;
    }
    /**
     *  Decodes htmlentities in array values
     *  @param array $a_pairs
     *  @return array
    **/
    public static function decodeEntities(array $a_pairs = array())
    {
        $a_clean = array();
        if (count($a_pairs) === 0) {
            return $a_clean;
        }
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_clean[$key] = self::decodeEntities($value);
            }
            else {
                $a_clean[$key] = html_entity_decode($value, ENT_QUOTES);
            }
        }
        return $a_clean;
    }
    /**
     *  Verifies an associate array has the necessary keys.
     *  @param array $a_required_keys required to have at least one value
     *  @param array $a_pairs required, must be associative array.
     *  @return bool
    **/
    public static function hasRequiredKeys(array $a_pairs = array(), array $a_required_keys = array())
    {
        if (count($a_required_keys) === 0 || count($a_pairs) === 0) {
            return false;
        }
        if (!self::isAssocArray($a_pairs)) {
            return false;
        }
        foreach ($a_required_keys as $key) {
            if (!array_key_exists($key, $a_pairs)) {
                return false;
            }
        }
        return true;
    }
    /**
     *  Determines that the value passed in is an associative array with all non-numeric keys.
     *  Also determines that the array is not empty.
     *  @param $a_pairs (array)
     *  @return bool
    **/
    public static function isAssocArray($a_pairs = array())
    {
        return (
            is_array($a_pairs)
            &&
            count($a_pairs) !== 0
            &&
            count(array_diff_key($a_pairs, array_keys(array_keys($a_pairs)))) == count($a_pairs)
        );
    }
    /**
     *  Removes the slashes from values in an array.
     *  Used primarily for returned values from a database search.
     *  @param array $a_pairs
     *  @return array
    **/
    public static function removeSlashes(array $a_pairs = array())
    {
        $a_stripped = array();
        if (count($a_pairs) === 0) {
            return $a_stripped;
        }
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_stripped[$key] = self::removeSlashes($value);
            }
            else {
                $value = trim($value);
                $a_stripped[$key] = stripslashes($value);
            }
        }
        return $a_stripped;
    }
    /**
     *  Strips unwanted key=>value pairs.
     *  Only really valuable for assoc arrays.
     *
     * @param array $a_pairs
     * @param array $a_allowed_keys
     *
     * @return array $a_pairs
     */
    public static function removeUndesiredPairs(array $a_pairs = array(), array $a_allowed_keys = array())
    {
        if ($a_pairs == array() || $a_allowed_keys == array()) { return array(); }
        foreach ($a_pairs as $key => $value) {
            if (!in_array($key, $a_allowed_keys)) {
                unset($a_pairs[$key]);
            }
        }
        return $a_pairs;
    }
    /**
     *  Strip HTML and PHP tags from the values in an array
     *  @param array $a_pairs the array with the values to modify
     *  @param array $a_allowed_keys an array with a list of keys allowed (optional)
     *  @param string $allowable_tags a string with allowed tags (see php strip_tags())
     *  @return array $a_clean
    **/
    public static function stripTags(array $a_pairs = array(), array $a_allowed_keys = array(), $allowable_tags = '')
    {
        $a_clean = array();
        if (count($a_pairs) === 0) {
            return $a_clean;
        }
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_clean[$key] = self::stripTags($value);
            }
            else {
                $value = trim($value);
                if (count($a_allowed_keys) >= 1) {
                    if (in_array($key, $a_allowed_keys)) {
                        $a_clean[$key] = strip_tags($value, $allowable_tags);
                    }
                }
                else {
                    $a_clean[$key] = strip_tags($value, $allowable_tags);
                }
            }
        }
        return $a_clean;
    }
    /**
     *  Removes unsafe php function names from array values.
     *  @param array $a_pairs
     *  @return array
     */
    public static function stripUnsafePhp(array $a_pairs)
    {
        $a_functions = [
            '/exec\((.*)\)/i',
            '/passthru\((.*)\)/i',
            '/shell_exec\((.*)\)/i',
            '/system\((.*)\)/i',
            '/proc_open\((.*)\)/i',
            '/popen\((.*)\)/i',
            '/curl_exec\((.*)\)/i',
            '/curl_multi_exec\((.*)\)/i',
            '/parse_ini_file\((.*)\)/i',
            '/show_source\((.*)\)/i'
        ];
        $a_return_this = array();
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_return_this[$key] = self::stripUnsafePhp($value);
            }
            $a_return_this[$key] = preg_replace($a_functions, '', $value);
        }
        return $a_return_this;
    }
}
