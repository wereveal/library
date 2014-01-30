<?php
/**
 *  @brief Class that does stuff with arrays.
 *  @details This is basically a start. I expect others to show up here from
 *  other classes where they don't belong or would server better in this
 *  class where they can be used more globally.
 *  @file Arrays.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Arrays
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.2.0
 *  @date 2013-12-30 13:16:46
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.2.0 - new method added - 12/30/2013 wer
 *      v1.1.1 - match package change - 12/19/2013 wer
 *      v1.1.0 - namespace changes - 07/30/2013 wer
 *      v1.0.3 - moved array methods from class Strings to here - 03/27/2013
 *      v1.0.2 - added new method
 *      v1.0.1 - new namespace, FIG standards (mostly)
 *  </pre>
**/
namespace Ritc\Library\Core;

class Arrays extends namespace\Base
{
    /**
     *  Modifies array values with htmlentities.
     *  Initially designed to do some basic filtering of $_POST, $_GET, etc
     *  but will work with any array.
     *  @param array $array the array to clean
     *  @param array $a_allowed_keys allows only specified keys to be returned
     *  @return array the cleaned array
    **/
    public function cleanArrayValues(array $array = array(), $a_allowed_keys = array())
    {
        $a_clean = array();
        if (count($array) === 0) {
            return $a_clean;
        }
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $a_clean[$key] = $this->cleanArrayValues($value);
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
     *  @param array $array
     *  @return array
    **/
    public function decodeEntities(array $array = array())
    {
        $a_clean = array();
        if (count($array) === 0) {
            return $a_clean;
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $a_clean[$key] = $this->decodeEntities($value);
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
     *  @param array $a_values required, must be associative array.
     *  @return bool
    **/
    public function hasRequiredKeys(array $a_required_keys = array(), array $a_values = array())
    {
        if (count($a_required_keys) === 0 || count($a_values) === 0) {
            return false;
        }
        if (!$this->isAssocArray($a_values)) {
            return false;
        }
        foreach ($a_required_keys as $key) {
            if (!array_key_exists($key, $a_values)) {
                return false;
            }
        }
        return true;
    }
    /**
     *  Determines that the value passed in is an associative array with all non-numeric keys.
     *  Also determines that the array is not empty.
     *  @param $array (array)
     *  @return bool
    **/
    public function isAssocArray($array = array())
    {
        return (
            is_array($array)
            &&
            count($array) !== 0
            &&
            count(array_diff_key($array, array_keys(array_keys($array)))) == count($array)
        );
    }
    /**
     *  Removes the slashes from values in an array.
     *  Used primarily for returned values from a database search.
     *  @param array $array
     *  @return array
    **/
    public function removeSlashes(array $array = array())
    {
        $a_stripped = array();
        if (count($array) === 0) {
            return $a_stripped;
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $a_stripped[$key] = $this->removeSlashes($value);
            }
            else {
                $value = trim($value);
                $a_stripped[$key] = stripslashes($value);
            }
        }
        return $a_stripped;
    }
    /**
     *  Strip HTML and PHP tags from the values in an array
     *  @param array $array the array with the values to modify
     *  @param array $a_allowed_keys an array with a list of keys allowed (optional)
     *  @param string $allowable_tags a string with allowed tags (see php strip_tags())
     *  @return array $a_clean
    **/
    public function stripTags(array $array = array(), array $a_allowed_keys = array(), $allowable_tags = '')
    {
        $a_clean = array();
        if (count($array) === 0) {
            return $a_clean;
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $a_clean[$key] = $this->stripTags($value);
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
     *  Strips unwanted key=>value pairs.
     *  Only really valuable for assoc arrays.
     *
     * @param array $a_pairs
     * @param array $a_allowed_keys
     *
     * @return array $a_pairs
     */
    public function stripUnspecifiedValues(array $a_pairs = array(), array $a_allowed_keys = array())
    {
        if ($a_pairs == array() || $a_allowed_keys == array()) { return array(); }
        foreach ($a_pairs as $key => $value) {
            if (!in_array($key, $a_allowed_keys)) {
                unset($a_pairs[$key]);
            }
        }
        return $a_pairs;
    }
}
