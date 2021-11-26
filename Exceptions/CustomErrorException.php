<?php
/**
 * Class CustomErrorException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Exception;
use Ritc\Library\Abstracts\CustomErrorAbstract;

/**
 * Class CustomErrorException - generic custom errors.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2021-11-26 16:35:09
 * @change_log
 * - v1.0.0         - production and updated to php8            - 2021-11-26 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-07-15 wer
 */
class CustomErrorException extends CustomErrorAbstract
{
    /**
     * CustomErrorException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param int            $severity
     * @param string         $filename
     * @param int            $lineno
     * @param Exception|null $previous
     */
    public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, Exception $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }

    /**
     * @param int $code
     * @return string
     */
    public function getCodeText(int $code = -1):string
    {
        return match ($code) {
            900     => 'General Error, see error message',
            999     => 'Unspecified Error.',
            -1      => 999,
            default => parent::getCodeText($code),
        };
    }

}
