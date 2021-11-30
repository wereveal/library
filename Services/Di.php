<?php
/**
 * Class Di
 * @package Ritc_Library
 */
namespace Ritc\Library\Services;

/**
 * Manages Dependency injection / Inversion of Control for the site.
 *
 * It is expected that this will be initialized in the setup file at
 * the very beginning. All other services needed to be used
 * throughout the app then get added to it. It can also be used as an alternative
 * to CONSTANTS or globals.
 * This is real basic. You put an instanced service in and pull a service
 * out. The service must have been instanced already. * @package RITC_Libra
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2021-11-30 15:27:28
 * @change_log
 * - v2.0.0   - updated to php 8 standards                      - 2021-11-30 wer
 * - v1.2.0   - added functions to save variable values         - 2017-02-08 wer
 * - v1.1.0   - added function to return all objects            - 03/03/2016 wer
 * - v1.0.0   - it works, not sure why it wasn't out of beta    - 09/03/2015 wer
 *              Removed abstract Base as it wasn't being used
 * - v1.0.0β1 - initial version                                 - 11/17/2014 wer
 */
class Di
{
    /** @var array */
    private array $a_objects = [];
    /** @var array */
    private array $a_vars = [];

    /**
     * @param string $object_name
     * @param        $object
     * @return bool
     */
    public function set(string $object_name = '', $object = null):bool
    {
        if (!is_object($object) || $object_name === '') {
            return false;
        }
        $this->a_objects[$object_name] = $object;
        return true;
    }

    /**
     * @param string $object_name
     * @return bool|Di
     */
    public function get(string $object_name = ''): bool|Di
    {
        if ($object_name !== '' && isset($this->a_objects[$object_name]) && is_object
            ($this->a_objects[$object_name])) {
            return $this->a_objects[$object_name];
        }
        return false;
    }

    /**
     * Returns the array of objects.
     *
     * @return array
     */
    public function getObjects():array
    {
        return $this->a_objects;
    }

    /**
     * Sets map pair in the a_vars array.
     *
     * @param string $var_name
     * @param string $var_value
     */
    public function setVar(string $var_name = '', string $var_value = ''):void
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
     *
     * @param $var_name
     * @return mixed
     */
    public function getVar($var_name): mixed
    {
        return $this->a_vars[$var_name] ?? '';
    }
}
