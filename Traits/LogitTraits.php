<?php
/**
 *  @brief Common functions that inject and use the Elog class service.
 *  @file LogitTraits.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Traits
 *  @class LogitTraits
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-08-19 09:53:24
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - initial version - 08/19/2015 wer
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
     * Writes the message into the log
     * @param  string $message
     * @param  int    $log_type see Elog Class for allowed values
     * @param  string $location
     * @return null
     */
    public function logIt($message = '', $log_type = LOG_OFF, $location = '')
    {
        if (is_object($this->o_elog) && is_int($log_type)) {
            if ($log_type > LOG_ALWAYS) {
                $log_type = LOG_OFF;
            }
            $this->o_elog->write($message, $log_type, $location);
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
