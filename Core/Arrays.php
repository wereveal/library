<?php
/**
 *  Class that does stuff with arrays.
 *  This is basically a start. I expect others to show up here from
 *  other classes where they don't belong or would server better in this
 *  class where they can be used more globally.
 *  @file Arrays.php
 *  @namespace Ritc\Library\Core
 *  @class Arrays
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.1.0
 *  @date 2013-07-30 10:56:22
 *  @par ChangeLog
 *      v1.1.0 - namespace changes - 07/30/2013 10:56:32
 *      v1.0.3 - moved array methods from class Strings to here - 03/27/2013
 *      v1.0.2 - added new method
 *      v1.0.1 - new namespace, FIG standards (mostly)
 *  @par RITC Library version 4.0.0
 *  @ingroup ritc_library library core
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstracts\Base;

class Arrays extends Base
{
    /**
     *  Modifies array values with htmlentities.
     *  Initially designed to do some basic filtering of $_POST, $_GET, etc
     *  but will work with any array.
     *  @param array $array the array to clean
     *  @param array $a_allowed_keys allows only specified keys to be returned
     *  @return array the cleaned array
    **/
    public function cleanArrayValues($array = '', $a_allowed_keys = array())
    {
        $a_clean = array();
        if (is_array($array) === false || count($array) === 0) {
            $this->o_elog->write("Array was empty", LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $a_clean;
        }
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $a_clean[$key] = $this->cleanArrayValues($value);
            } else {
                $value = trim($value);
                if (count($a_allowed_keys) >= 1) {
                    if (in_array($key, $a_allowed_keys)) {
                        $a_clean[$key] = htmlentities($value, ENT_QUOTES);
                    }
                } else {
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
    public function decodeEntities($array = '')
    {
        $a_clean = array();
        if (is_array($array) === false || count($array) === 0) {
            return $a_clean;
        }
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $a_clean[$key] = $this->decode_htmlentities_in_array($value);
            } else {
                $a_clean[$key] = html_entity_decode($value, ENT_QUOTES);
            }
        }
        return $a_clean;
    }
    /**
     *  Determines that the value passed in is an associative array with all non-numeric keys.
     *  Also determines that the array is not empty.
     *  @param $array (array)
     *  @return bool
    **/
    public function isAssocArray($array = array())
    {
        $this->input_value = $array;
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
     *  @param $array (array)
     *  @return array
    **/
    public function removeSlashes($array = '')
    {
        $a_stripped = array();
        if (is_array($array) === false || count($array) === 0) {
            $this->o_elog->write("Array was empty", LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $a_stripped;
        }
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $a_stripped[$key] = $this->removeSlashes($value);
            } else {
                $value = trim($value);
                $a_stripped[$key] = stripslashes($value);
            }
        }
        return $a_stripped;
    }
    /**
     *  Strip HTML and PHP tags from the values in an array
     *  @param array $array - the array with the values to modify
     *  @param array $a_allowed_keys - an array with a list of keys allowed (optional)
     *  @param string $allowable_tags - a string with allowed tags (see php strip_tags())
     *  @return array $a_clean
    **/
    public function stripTags($array = '', $a_allowed_keys = array(), $allowable_tags = '')
    {
        $a_clean = array();
        if (is_array($a_allowed_keys) === false) {
            $a_allowed_keys = array();
        }
        if (is_array($array) === false || count($array) === 0) {
            return $a_clean;
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $a_clean[$key] = $this->strip_tags_array($value);
            } else {
                $value = trim($value);
                if (count($a_allowed_keys) >= 1) {
                    if (in_array($key, $a_allowed_keys)) {
                        $a_clean[$key] = strip_tags($value, $allowable_tags);
                    }
                } else {
                    $a_clean[$key] = strip_tags($value, $allowable_tags);
                }
            }
        }
        return $a_clean;
    }
    /**
     *  Strips unwanted key=>value pairs.
     *  Only really valuable for assoc arrays.
     *  @param array $a_pairs
     *  @param array $a_good_keys
     *  @return array $a_pairs
    **/
    public function stripUnspecifiedValues($a_pairs = '', $a_allowed_keys = '')
    {
        if ($a_pairs == '' || $a_allowed_keys == '') { return array(); }
        foreach ($a_pairs as $key => $value) {
            if (!in_array($key, $a_allowed_keys)) {
                unset($a_pairs[$key]);
            }
        }
        return $a_pairs;
    }
}
