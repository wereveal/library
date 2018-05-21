<?php
/**
 * Class CustomErrorException
 * @package RITC_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomError;

/**
 * Class CustomErrorException - generic custom errors.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-07-15 12:41:34
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-07-15 wer
 */
class CustomErrorException extends CustomError
{
    /**
     * CustomErrorException constructor.
     * @param string     $message
     * @param int        $code
     * @param int        $severity
     * @param string     $filename
     * @param int        $lineno
     * @param \Exception $previous
     */
    public function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, \Exception $previous)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }

    /**
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        switch ($code) {
            default:
                return parent::getCodeText($code);

        }
    }

}
