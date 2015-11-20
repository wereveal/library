<?php
/**
 *  @brief Common functions that inject and use the Elog class service.
 *  @file LogitTraits.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Traits
 *  @class LogitTraits
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.1
 *  @date 2015-11-03 13:04:28
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - checked to see if we want to even bother with calling o_elog.  - 11/03/2015 wer
 *      v1.0.0 - initial version                                                - 08/19/2015 wer
 *  </pre>
 *  @note this is derived from the abstract class Base and may
 *      end up replacing the abstract class or used in classes that
 *      don't use the abstract class.
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Services\Elog;

trait LogitTraits
{
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
        if (is_object($this->o_elog)
            && is_int($log_type)
            && $log_type <= LOG_ALWAYS
            && $message != ''
        ) {
            $this->o_elog->write($message, $log_type, $location);
        }
    }
    /**
     * Logs the $message in the custom log if available.
     * Does some checking if it should be written to log first.
     * @param  string $message
     * @param  string $location
     * @return null
     */
    protected function cLog($message, $location)
    {
        if (is_object($this->o_elog) && $message != '') {
            $go = $this->o_elog->handlerIsSet()
                ? true
                : $this->o_elog->setErrorHandler()
                    ? true
                    : false;
            if ($go) {
                $this->o_elog->setLogMethod(LOG_CUSTOM);
                $this->o_elog->setFromMethod($location);
                trigger_error($message, E_USER_NOTICE);
            }
            else {
                $this->logIt($message, LOG_PHP, $location);
            }
        }
    }
    /**
     * Logs the $message in the custom json formated log if available.
     * Does some checking if it should be written to log first.
     * @param  string $message
     * @param  string $location
     * @return null
     */
    protected function jLog($message, $location)
    {
        if (is_object($this->o_elog) && $message != '') {
            $go = $this->o_elog->handlerIsSet()
                ? true
                : $this->o_elog->setErrorHandler()
                    ? true
                    : false;
            if ($go) {
                $this->o_elog->setLogMethod(LOG_JSON);
                $this->o_elog->setFromMethod($location);
                trigger_error($message, E_USER_NOTICE);
            }
            else {
                $this->logIt($message, LOG_PHP, $location);
            }
        }
    
    }
    /**
     *  Injectes the Elog object into the class.
     *  @param  Elog $o_elog
     *  @return null
     */
    public function setElog(Elog $o_elog)
    {
        $this->o_elog = $o_elog;
    }
}
