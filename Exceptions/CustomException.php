<?php
/**
 * Class CustomException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;
use Throwable;

/**
 * Class CustomErrorException - generic custom errors.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-11-26 16:37:06
 * @change_log
 * - v1.0.0-alpha.1 - updated to php8                           - 2021-11-26 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-07-15 wer
 */
class CustomException extends CustomExceptionAbstract
{
    /**
     * CustomException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
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
            default => parent::getCodeText($code),
        };
    }

}
