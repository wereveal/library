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
                    return $array;
                }
                $a_new[] = $results;
            }
            return $a_new;
        }
        else {
            return $array;
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
            return $array;
        }
    }

}