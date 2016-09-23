<?php
/**
 * @brief     Class that does stuff with arrays.
 * @ingroup   lib_helper
 * @file      Ritc/Library/Helper/Arrays.php
 * @namespace Ritc\Library\Helper
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   3.1.0
 * @date      2016-09-23 15:30:31
 * @note <b>Change Log</b>
 * - v3.1.0 - Moved a couple methods from DbCommonTraits to Arrays                      - 2016-09-23 wer
 * - v3.0.1 - Bug Fix                                                                   - 2016-09-23 wer
 * - v3.0.0 - Depreciated clearArrayValues in favor of two new methods.                 - 2016-09-19 wer
 *            Arrays::cleanValues() by default removes php and mysql commands from
 *            the values in the array. Optionally it can call Arrays::encodeValues().
 *            Arrays::encodeValues() encodes the values of the array using filter_var
 *            and with the FILTER_SANITIZE_STRING filter and optional flags.
 * - v2.8.0 - Added new method inAssocArrayRecursive()                                  - 2016-04-12 wer
 * - v2.7.0 - Changed entity coding/decoding to be definable via parameter.             - 11/25/2015 wer
 *              Defaults to ENT_QUOTES.
 * - v2.6.1 - bug fix, stripTags -- logic error                                         - 11/12/2015 wer
 * - v2.6.0 - new method, moved from Tester class, can be more generic.                 - 11/02/2015 wer
 * - v2.5.2 - bug fix, hasBlankValues -- needed to check for missing pairs              - 10/22/2015 wer
 * - v2.5.1 - bug fix, inArrayRecursive                                                 - 10/20/2015 wer
 * - v2.5.0 - new method, createRequiredPairs                                           - 10/06/2015 wer
 * - v2.4.0 - new methods, isArrayOfAssocArrays and hasBlankValues                      - 09/12/2015 wer
 * - v2.3.0 - New method, inArrayRecursive                                              - 09/10/2015 wer
 * - v2.2.0 - Removed use of abstract class Base                                        - 09/03/2015 wer
 * - v2.1.0 - After looking at the inconsistency, changed to be more consistent         - 07/31/2015 wer
 *              Also changed variable name to be more descriptive than array.
 * - v2.0.1 - oops, missed one to be static, changed its name                           - 07/31/2015 wer
 * - v2.0.0 - changed methods to be static                                              - 01/27/2015 wer
 * - v1.3.0 - added stripUnsafePhp method and modified cleanArrayValues to use it       - 12/05/2014 wer
 * - v1.2.1 - moved to the Helper namespace                                             - 11/15/2014 wer
 * - v1.2.1 - clean up                                                                  - 09/23/2014 wer
 * - v1.2.0 - new method added                                                          - 12/30/2013 wer
 * - v1.1.1 - match package change                                                      - 12/19/2013 wer
 * - v1.1.0 - namespace changes                                                         - 07/30/2013 wer
 * - v1.0.3 - moved array methods from class Strings to here                            - 03/27/2013 wer
 * - v1.0.2 - added new method
 * - v1.0.1 - new namespace, FIG standards (mostly)
 */
namespace Ritc\Library\Helper;

/**
 * Class Arrays - does stuff with arrays.
 * @class Arrays
 * @package Ritc\Library\Helper
 */
class Arrays
{
    /**
     * Modifies array values with htmlentities and strips unsafe php commands.
     * Initially designed to do some basic filtering of $_POST, $_GET, etc
     * but will work with any array.
     * This is now depreciated in favor of Arrays::cleanValues() and Arrays::encodeValues()
     * @param array $a_pairs             the array to clean
     * @param array $a_allowed_keys      allows only specified keys to be returned
     * @param bool  $unsafe_php_commands defaults to true strips 'unsafe' php commands, rare should it be false
     * @param int   $ent_flag            sets the entity coding defaults to ENT_QUOTES
     * @return array                     the cleaned array
     */
    public static function cleanArrayValues(array $a_pairs = array(), $a_allowed_keys = array(), $unsafe_php_commands = true, $ent_flag = ENT_QUOTES)
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
                        $a_clean[$key] = htmlentities($value, $ent_flag);
                    }
                }
                else {
                    $a_clean[$key] = htmlentities($value, $ent_flag);
                }
            }
        }
        return $a_clean;
    }

    /**
     * Modifies array values by removing php and mysql commands.
     * @param array   $a_pairs            optional, sort of, the array to clean.
     * @param array   $a_allowed_keys     optional, allows only specified keys to be returned.
     * @param array   $a_allowed_commands optional, defaults to []. Used values is ['php' => true, 'mysql' => true].
     * @param integer $sanitize_flags     optional, defaults to 0. If provided, calls Array::encodeValues().
     * @return array
     */
    public static function cleanValues(array $a_pairs = [], array $a_allowed_keys = [], array $a_allowed_commands = [], $sanitize_flags = 0)
    {
        if (empty($a_pairs)) {
            return [];
        }
        if (!empty($a_allowed_keys)) {
            $a_pairs = self::removeUndesiredPairs($a_pairs, $a_allowed_keys);
        }

        if (empty($a_allowed_commands)) {
            $a_pairs = self::stripUnsafePhp($a_pairs);
            $a_pairs = self::stripSQL($a_pairs);
        }
        else {
            if ($a_allowed_commands['php'] !== true) {
                $a_pairs = self::stripUnsafePhp($a_pairs);
            }
            if ($a_allowed_commands['mysql'] !== true) {
                $a_pairs = self::stripSQL($a_pairs);
            }
        }
        if ($sanitize_flags > 0) {
            $a_pairs = self::encodeValues($a_pairs, $sanitize_flags);
        }
        return $a_pairs;
    }

    /**
     * Compares two arrays and sees if the values in the second array match the first.
     * It should be noted that the second array can have additional
     * key=>value pairs but only the key=>value pairs that are in the
     * expected_values array are checked.
     * @param array $a_expected_values can be a simple array or an array of arrays
     * @param array $a_check_values    Needs to be identical structure wise as $a_expected values
     * @return bool                    true or false
     * @note <pre>Example with good results
     *      $a_expected_values = ['fred', 'barney'] equals $a_check_values = ['fred', 'barney']
     *      $a_expected_values = [
     *         ['name' => 'fred',   'wife' => 'wilma'],
     *         ['name' => 'barney', 'wife' => 'betty']
     *      ]
     *      equals
     *      $a_check_values = [
     *         ['name' => 'fred',   'wife' => 'wilma'],
     *         ['name' => 'barney', 'wife' => 'betty']
     *      ]
     * </pre>
     */
    public static function compareArrays(array $a_expected_values = array(), array $a_check_values = array())
    {
        if ($a_check_values != array() && $a_expected_values == array()) {
            return false;
        }
        foreach ($a_expected_values as $key => $value) {
            if (is_array($value)) {
                $results = self::compareArrays($value, $a_check_values[$key]);
                if ($results === false) {
                    return false;
                }
            }
            elseif ($a_expected_values[$key] != $a_check_values[$key]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns an array which has only the required keys and has all of them.
     * @param array $a_pairs
     * @param array $a_required_keys
     * @param mixed $delete_undesired
     * @return array
     */
    public static function createRequiredPairs(array $a_pairs = array(), $a_required_keys = array(), $delete_undesired = false)
    {
        if ($delete_undesired) {
            $a_pairs = self::removeUndesiredPairs($a_pairs, $a_required_keys);
        }
        if (self::hasRequiredKeys($a_pairs, $a_required_keys)) {
            return $a_pairs;
        }
        foreach ($a_required_keys as $key) {
            if (!array_key_exists($key, $a_pairs)) {
                $a_pairs[$key] = '';
            }
        }
        return $a_pairs;
    }

    /**
     * Decodes htmlentities in array values
     * @param array $a_pairs
     * @param int   $ent_flag sets the entity decoding defaults to ENT_QUOTES
     * @return array
     */
    public static function decodeEntities(array $a_pairs = array(), $ent_flag = ENT_QUOTES)
    {
        $a_clean = array();
        if (count($a_pairs) === 0) {
            return $a_clean;
        }
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_clean[$key] = self::decodeEntities($value, $ent_flag);
            }
            else {
                $a_clean[$key] = html_entity_decode($value, $ent_flag);
            }
        }
        return $a_clean;
    }

    /**
     * Runs the value of each array pair through filter_var($var, FILTER_SANITIZE_STRING) using specified flags.
     * Calling this method one would probably use the Flag Constants, e.g.
     * encodeValues($a_pairs, FILTER_FLAG_NO_ENCODE_QUOTES) or encodeValues($a_pairs, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_ENCODE_HIGH).
     * @param array $a_pairs        Required of sorts
     * @param int   $sanitize_flags Optional, defaults to 0 (i.e. default filtering).
     * @return array
     */
    public static function encodeValues(array $a_pairs = array(), $sanitize_flags = 0)
    {
        if (empty($a_pairs)) {
            return $a_pairs;
        }
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_pairs[$key] = self::encodeValues($value, $sanitize_flags);
            }
            else {
                $a_pairs[$key] = filter_var($value, FILTER_SANITIZE_STRING, $sanitize_flags);
            }
        }
        return $a_pairs;
    }

    /**
     * Determines if any required keys are missing
     * @param array $a_required_keys required
     * @param array $a_check_values required
     * @return array $a_missing_keys
     */
    public static function findMissingKeys(array $a_required_keys = array(), array $a_check_values = array())
    {
        if ($a_required_keys == array() || $a_check_values == array()) { return array(); }
        $a_missing_keys = array();
        foreach ($a_required_keys as $key) {
            if (
                array_key_exists($key, $a_check_values)
                ||
                array_key_exists(':' . $key, $a_check_values)
                ||
                array_key_exists(str_replace(':', '', $key), $a_check_values)
            ) {
                // we are happy
            }
            else {
                $a_missing_keys[] = $key;
            }
        }
        return $a_missing_keys;
    }

    /**
     * Finds missing or empty values for given key => value pair
     * @param array $a_required_keys required list of keys that need to have values
     * @param array $a_pairs
     * @return array $a_keys list of the the keys that are missing values
     */
    public static function findMissingValues(array $a_required_keys = array(), array $a_pairs = array())
    {
        if (empty($a_pairs) && !empty($a_required_keys)) {
            return $a_required_keys;
        }
        elseif (empty($a_pairs) && empty($a_required_keys)) {
            return [];
        }
        $a_keys = array();
        foreach ($a_pairs as $key => $value) {
            if (
                array_key_exists($key, $a_required_keys)
                ||
                array_key_exists(':' . $key, $a_required_keys)
                ||
                array_key_exists(str_replace(':', '', $key), $a_required_keys)
            )
            {
                if ($value == '' || is_null($value)) {
                    $a_keys[] = $key;
                }
            }
        }
        return $a_keys;
    }

    /**
     * Verifies an associate array has the necessary keys.
     * @param array $a_required_keys required to have at least one value
     * @param array $a_pairs required, must be associative array.
     * @return bool
     */
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
     * Checks array for blank values and missing key=>value pairs.
     * @param array $a_pairs
     * @param array $a_keys_to_check
     * @return bool
     */
    public static function hasBlankValues(array $a_pairs = array(), array $a_keys_to_check = array())
    {
        if ($a_pairs == array()) {
            return true;
        }
        if (!self::hasRequiredKeys($a_pairs, $a_keys_to_check)) {
            return true;
        }
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $results = self::hasBlankValues($value, $a_keys_to_check);
                if ($results === true) {
                    return true;
                }
            }
            elseif ($value == '') {
                if ($a_keys_to_check != array()) {
                    if (in_array($key, $a_keys_to_check)) {
                        return true;
                    }
                }
                else {
                   return true;
                }
            }
        }
        return false;
    }

    /**
     * Determines if the value exists in an associative array or array or assoc arrays.
     * @param string $key_name   Required.
     * @param string $needle     Required.
     * @param array  $a_haystack Required.
     * @param bool   $strict     Optional.
     * @return bool
     */
    public static function inAssocArrayRecursive($key_name = '', $needle = '', array $a_haystack, $strict = false)
    {
        if ($key_name == '' || $needle == '' || $a_haystack == []) {
            return false;
        }
        if (!self::isArrayOfAssocArrays($a_haystack) && !self::isAssocArray($a_haystack)) {
            return false;
        }
        foreach ($a_haystack as $key => $item) {
            if (is_array($item)) {
                $inner_key = self::inAssocArrayRecursive($key_name, $needle, $item, $strict);
                if ($inner_key !== false) {
                    return true;
                }
            }
            else {
                if (($strict && $key_name == $key && $item === $needle)) {
                    return true;
                }
                elseif ($key_name == $key && $item == $needle) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Searches a multidimensional array for a value.
     * If found, returns the key of the array, if multidimenstional array
     * it returns the keys of each array dot separated, e.g., 1.3.2 or 'fred'.'wife'.'wilma'.
     * @param string $needle     Required.
     * @param array  $a_haystack Required.
     * @param bool   $strict     Optional.
     * @return mixed|string|int|bool key of the found or false
     */
    public static function inArrayRecursive($needle = '', array $a_haystack, $strict = false)
    {
        if ($needle == '' || $a_haystack == array()) {
            return false;
        }
        foreach ($a_haystack as $key => $item) {
            if (is_array($item)) {
                $inner_key = self::inArrayRecursive($needle, $item, $strict);
                if ($inner_key !== false) {
                    return $key . '.' . $inner_key;
                }
            }
            else {
                if (($strict && $item === $needle)
                || ($strict === false && $item == $needle)) {
                    // print $key . "\n";
                    return $key;
                }
            }
        }
        return false;
    }

    /**
     * Determines that the value passed in is an associative array with all non-numeric keys.
     * Also determines that the array is not empty.
     * @param array $a_pairs
     * @return bool
     */
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
     * Sees if the array passed in is an array of assoc arrays.
     * @param array
     * @return bool
     */
    public static function isArrayOfAssocArrays(array $a_arrays = array())
    {
        foreach ($a_arrays as $a_array) {
            if (!self::isAssocArray($a_array)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Removes the slashes from values in an array.
     * Used primarily for returned values from a database search.
     * @param array $a_pairs
     * @return array
     */
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
     * Strips unwanted key=>value pairs.
     * Only really valuable for assoc arrays.
     * @param array $a_pairs
     * @param array $a_allowed_keys
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
     * Removes SQL commands from array pairs.
     * @param array $a_pairs
     * @return array
     */
    public static function stripSQL(array $a_pairs = [])
    {
        if (empty($a_pairs)) {
            return [];
        }
        $a_commands = [
            '/SELECT(.*) FROM/',
            '/INSERT(.*) INTO/',
            '/DELETE(.*) FROM/',
            '/UPDATE(.*) SET/',
            '/REPLACE(.*) INTO/',
            '/ALTER AGGREGATE/i',
            '/ALTER COLLATION/i',
            '/ALTER CONVERSION/i',
            '/ALTER DATABASE/i',
            '/ALTER DEFAULT PRIVILEGES/i',
            '/ALTER DOMAIN/i',
            '/ALTER EVENT TRIGGER/i',
            '/ALTER EVENT/i',
            '/ALTER EXTENSION/i',
            '/ALTER FOREIGN DATA WRAPPER/i',
            '/ALTER FOREIGN TABLE/i',
            '/ALTER FUNCTION/i',
            '/ALTER GROUP/i',
            '/ALTER INDEX/i',
            '/ALTER INSTANCE/i',
            '/ALTER LANGUAGE/i',
            '/ALTER LARGE OBJECT/i',
            '/ALTER LOGFILE GROUP/i',
            '/ALTER MAGERIALIZED VIEW/i',
            '/ALTER OPERATOR CLASS/i',
            '/ALTER OPERATOR FAMILY/i',
            '/ALTER OPERATOR/i',
            '/ALTER PROCEDURE/i',
            '/ALTER ROLE/i',
            '/ALTER SCHEMA/i',
            '/ALTER SEQUENCE/i',
            '/ALTER SERVER/i',
            '/ALTER SYSTEM/i',
            '/ALTER TABLE/i',
            '/ALTER TABLESPACE/i',
            '/ALTER TRIGGER/i',
            '/ALTER TYPE/i',
            '/ALTER USER MAPPING/i',
            '/ALTER USER/i',
            '/ALTER VIEW/i',
            '/CREATE DATABASE/i',
            '/CREATE EVENT/i',
            '/CREATE FUNCTION/i',
            '/CREATE INDEX/i',
            '/CREATE LOGFILE GROUP/i',
            '/CREATE PROCEDURE/i',
            '/CREATE FUNCTION/i',
            '/CREATE ROLE/i',
            '/CREATE SERVER/i',
            '/CREATE TABLE/i',
            '/CREATE TABLESPACE/i',
            '/CREATE TRIGGER/i',
            '/CREATE USER/i',
            '/CREATE VIEW/i',
            '/DROP DATABASE/i',
            '/DROP DOMAIN/i',
            '/DROP EVENT/i',
            '/DROP FOREIGN TABLE/i',
            '/DROP FUNCTION/i',
            '/DROP INDEX/i',
            '/DROP LOGFILE GROUP/i',
            '/DROP PROCEDURE/i',
            '/DROP ROLE/i',
            '/DROP FUNCTION/i',
            '/DROP SERVER/i',
            '/DROP TABLE/i',
            '/DROP TABLESPACE/i',
            '/DROP TRIGGER/i',
            '/DROP USER/i',
            '/DROP VIEW/i',
            '/RENAME TABLE/i',
            '/SET ROLE/i',
            '/SET SESSION AUTHORIZATION/i',
            '/TRUNCATE TABLE/i'
        ];

        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_pairs[$key] = self::stripSQL($value);
            }
            else {
                $a_pairs[$key] = preg_replace($a_commands, '', $value);
            }
        }
        return $a_pairs;
    }

    /**
     * Strip HTML and PHP tags from the values in an array
     * @param array  $a_pairs        the array with the values to modify
     * @param array  $a_allowed_keys an array with a list of keys allowed to have tags (optional)
     * @param string $allowable_tags a string with allowed tags (see php strip_tags())
     * @return array $a_clean
     */
    public static function stripTags(array $a_pairs = array(), array $a_allowed_keys = array(), $allowable_tags = '')
    {
        $a_clean = array();
        if (count($a_pairs) === 0) {
            return $a_clean;
        }
        foreach ($a_pairs as $key => $value) {
            if (is_array($value)) {
                $a_clean[$key] = self::stripTags($value, $a_allowed_keys, $allowable_tags);
            }
            else {
                $value = trim($value);
                if (count($a_allowed_keys) >= 1) {
                    if (!in_array($key, $a_allowed_keys)) {
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
     * Removes unsafe php function names from array values.
     * @param array $a_pairs
     * @return array
     */
    public static function stripUnsafePhp(array $a_pairs)
    {
        $a_functions = [
            '/shell_exec\((.*)\)/i',
            '/exec\((.*)\)/i',
            '/passthru\((.*)\)/i',
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
            else {
                $a_return_this[$key] = preg_replace($a_functions, '', $value);
            }
        }
        return $a_return_this;
    }
}
