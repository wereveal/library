<?php
/**
 * @brief     Manages Dependency injection / Inversion of Control for the site.
 * @details   It is expected that this will be initialized in the setup file at
 *            the very begining. All other services needed to be used
 *            throughout the app then get added to it. It can also be used as an alternative
 *            to CONSTANTS or globals.
 *            This is real basic. You put an instanced service in and pull a service
 *            out. The service must have been instanced already.
 * @ingroup   lib_services
 * @file      Di.php
 * @namespace Ritc\Library\Services
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.2.0
 * @date      2017-02-08 09:04:05
 * @note <b>Change Log</b>
 * - v1.2.0   - added functions to save variable values        - 2017-02-08 wer
 * - v1.1.0   - added function to return all objects           - 03/03/2016 wer
 * - v1.0.1   - removed unused use                             - 02/22/2016 wer
 * - v1.0.0   - it works, not sure why it wasn't out of beta   - 09/03/2015 wer
 *              Removed abstract Base as it wasn't being used
 * - v1.0.0β1 - initial version                                - 11/17/2014 wer
 */
namespace Ritc\Library\Services;

/**
 * Class Di does some basic dependency injection.
 * @class   Di
 * @package Ritc\Library\Services
 */
class Di
{
    /** @var array */
    private $a_objects = [];
    /** @var array */
    private $a_vars    = [];

    /**
     * @param string $object_name
     * @param        $object
     * @return bool
     */
    public function set($object_name = '', $object)
    {
        if (!is_object($object) || $object_name == '') {
            return false;
        }
        $this->a_objects[$object_name] = $object;
        return true;
    }

    /**
     * @param string $object_name
     * @return bool
     */
    public function get($object_name = '')
    {
        if ($object_name != '' && isset($this->a_objects[$object_name]) && is_object($this->a_objects[$object_name])) {
            return $this->a_objects[$object_name];
        }
        return false;
    }

    /**
     * Returns the array of objects.
     * @return array
     */
    public function getObjects()
    {
        return $this->a_objects;
    }

    /**
     * Sets map pair in the a_vars array.
     * @param string $var_name
     * @param string $var_value
     */
    public function setVar($var_name = '', $var_value = '')
    {
        if (!empty($var_name)) {
            if (empty($var_value)) {
                if (isset($this->a_vars[$var_name])) {
                    unset($this->a_vars[$var_name]);
                }
            }
            else {
               $this->a_vars[$var_name] = $var_value;
            }
        }
    }

    /**
     * Returns the value from the a_vars array based on the key name.
     * @param $var_name
     * @return mixed|string
     */
    public function getVar($var_name) {
        if (isset($this->a_vars[$var_name])) {
            return $this->a_vars[$var_name];
        }
        else {
            return '';
        }
    }
}
