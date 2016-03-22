<?php
/**
 * @brief     Common methods for the db traits.
 * @ingroup   ritc_traits
 * @file      DbCommonTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-03-19 08:58:00
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2016-03-19 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\Arrays;

/**
 * Class DbCommonTraits.
 * @class   DbCommonTraits
 * @package Ritc\Library\Traits
 */
trait DbCommonTraits
{
    /**
     * Determines if any required keys are missing
     * @param array $a_required_keys required
     * @param array $a_check_values required
     * @return array $a_missing_keys
     */
    public function findMissingKeys(array $a_required_keys = array(), array $a_check_values = array())
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
    public function findMissingValues(array $a_required_keys = array(), array $a_pairs = array())
    {
        if ($a_pairs == array() || $a_required_keys == array()) { return false; }
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
     * Changes array keys to be compatible with prepared statements.
     * @param array $array required associative array, named keys
     * @return array fixed key names
     */
    public function prepareKeys(array $array = array())
    {
        $a_new = array();
        if (Arrays::isAssocArray($array)) {
            foreach ($array as $key=>$value) {
                $new_key = strpos($key, ':') === 0 ? $key : ':' . $key;
                $a_new[$new_key] = $value;
            }
            return $a_new;
        }
        elseif (Arrays::isAssocArray($array[0])) {
            foreach ($array as $a_keys) {
                $results = $this->prepareKeys($a_keys);
                if ($results === false) {
                    return false;
                }
                $a_new[] = $results;
            }
            return $a_new;
        }
        else {
            return false;
        }
    }

    /**
     * Changes array values to help build a prepared statement primarily the WHERE.
     * @param array $array key/value pairs to fix
     * @return array fixed where needed
     */
    public function prepareValues(array $array)
    {
        $a_new = array();
        if (Arrays::isAssocArray($array)) {
            foreach ($array as $key => $value) {
                $new_key = strpos($key, ':') === 0 ? $key : ':' . $key;
                $a_new[$key] = $new_key;
            }
            return $a_new;
        }
        elseif (Arrays::isAssocArray($array[0])) {
            return $this->prepareValues($array[0]);
        }
        else {
            return false;
        }
    }

}