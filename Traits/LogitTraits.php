<?php
/**
 * @brief     Common functions that inject and use the Elog class service.
 * @ingroup   lib_traits
 * @file      LogitTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.1.2
 * @date      2018-03-29 16:15:00
 * @note      this is derived from the abstract class Base and may end up replacing
 *            the abstract class or used in classes that don't use the abstract class.
 * @note <b>Change Log</b>
 * - v1.1.2 - bug fixes                                                      - 2018-03-29 wer
 * - v1.1.1 - bug fixes                                                      - 02/26/2016 wer
 * - v1.1.0 - added code to utilize the custom logging capabilities          - 11/20/2015 wer
 * - v1.0.1 - checked to see if we want to even bother with calling o_elog.  - 11/03/2015 wer
 * - v1.0.0 - initial version                                                - 08/19/2015 wer
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Services\Elog;

/**
 * Class LogitTraits
 * @class   LogitTraits
 * @package Ritc\Library\Traits
 */
trait LogitTraits
{
    /** @var Elog */
    protected $o_elog;

    /**
     * @return object|null
     */
    public function getElog()
    {
        if (is_object($this->o_elog)) {
            return $this->o_elog;
        }
        return null;
    }

    /**
     * Logs the $message as defined by $log_type.
     * Does some checking if it should be written to log first.
     * @param  string $message
     * @param  int    $log_type see Elog Class for allowed values
     * @param  string $location
     * @return null
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
     * Injectes the Elog object into the class.
     * @param  string $o_elog
     * @return null
     */
    public function setElog($o_elog = '')
    {
        if ($o_elog instanceof Elog) {
            $this->o_elog = $o_elog;
        }
    }
}
