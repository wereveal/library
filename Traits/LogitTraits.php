<?php
/**
 * Trait LogitTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;

/**
 * Common functions that inject and use the Elog class service.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.3.1
 * @date    2018-03-29 15:44:43
 * @note    this is derived from the abstract class Base which it replaced.
 * @change_log
 * - v1.3.1 - bug fix                                                        - 2018-03-29 wer
 * - v1.3.0 - added method and property to set elog for multiple object      - 2017-12-05 wer
 *            based on the names of the objects saved in the property
 * - v1.2.0 - setElog now allows anything to pass into it and then checks    - 2017-02-07 wer
 *            to see if it is an instanceof Elog. That way, it doesn't
 *            generate errors.
 * - v1.1.1 - bug fixes                                                      - 02/26/2016 wer
 * - v1.1.0 - added code to utilize the custom logging capabilities          - 11/20/2015 wer
 * - v1.0.1 - checked to see if we want to even bother with calling o_elog.  - 11/03/2015 wer
 * - v1.0.0 - initial version                                                - 08/19/2015 wer
 */
trait LogitTraits
{
    /** @var array */
    protected $a_object_names = [];
    /** @var Elog */
    protected $o_elog = '';

    /**
     * @return Elog|string
     */
    public function getElog()
    {
        if ($this->o_elog instanceof Elog) {
            return $this->o_elog;
        }
        return '';
    }

    /**
     * Logs the $message as defined by $log_type.
     * Does some checking if it should be written to log first.
     * @param  string $message
     * @param  int    $log_type see Elog Class for allowed values
     * @param  string $location
     */
    protected function logIt($message = '', $log_type = LOG_OFF, $location = '')
    {
        if ($this->o_elog instanceof Elog
            && is_int($log_type)
            && $log_type <= LOG_ALWAYS
            && $log_type > LOG_OFF
            && $message != ''
        ) {
            switch ($log_type) {
                case LOG_ON:
                case LOG_CUSTOM:
                    $this->o_elog->setLogMethod(LOG_CUSTOM);
                    $this->o_elog->setFromLocation($location);
                    trigger_error($message, E_USER_NOTICE);
                    break;
                case LOG_JSON:
                    $this->o_elog->setLogMethod(LOG_JSON);
                    $this->o_elog->setFromLocation($location);
                    trigger_error($message, E_USER_NOTICE);
                    break;
                default:
                    $this->o_elog->write($message, $log_type, $location);
            }
        }
    }

    /**
     * Injects the Elog object into the class that uses LogitTraits.
     * @param Elog|null $o_elog
     */
    public function setElog($o_elog = null)
    {
        if ($o_elog instanceof Elog) {
            $this->o_elog = $o_elog;
            $this->setElogForObjects();
        }
    }

    /**
     * Sets the elog for objects in the object list.
     * Normally a part of a complex model class.
     */
    public function setElogForObjects()
    {
        if ($this->o_elog instanceof Elog) {
            if (!empty($this->a_object_names)) {
                foreach ($this->a_object_names as $object) {
                    if (is_object($this->$object)) {
                        $this->$object->setElog($this->o_elog);
                    }
                }
            }
        }
    }

    /**
     * Quick Stub for a commonly called thing.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function setupElog(Di $o_di)
    {
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $o_elog = $o_di->get('elog');
            $this->setElog($o_elog);
        }
    }

    /**
     * Standard SETter for protected class property.
     * @param array $a_object_names
     */
    protected function setObjectNames(array $a_object_names = [])
    {
        $this->a_object_names = $a_object_names;
    }
}
